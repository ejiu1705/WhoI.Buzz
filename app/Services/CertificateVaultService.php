<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificateVaultService
{
    private const ALLOWED_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    private const MAX_BYTES = 2 * 1024 * 1024; // 2 MB strict Zero-Trust limit

    /**
     * Validate magic bytes, check SHA-256 anti-duplication, sanitize/re-encode image, store in private vault.
     */
    public function inspectAndStore(UploadedFile $file, User $student, Activity $activity): ActivityFile
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            throw ValidationException::withMessages([
                'certificate_file' => 'Berkas unggahan tidak valid atau rusak.',
            ]);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'certificate_file' => 'Ukuran file melebihi batas keamanan 2 MB.',
            ]);
        }

        // Magic Bytes MIME verification (never trust client extension or Content-Type header)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($realPath) ?: 'application/octet-stream';

        if (! array_key_exists($detectedMime, self::ALLOWED_MIMES)) {
            throw ValidationException::withMessages([
                'certificate_file' => "Tipe konten biner file ({$detectedMime}) ditolak oleh Zero-Trust Vault. Hanya PDF, JPG, atau PNG asli yang diizinkan.",
            ]);
        }

        // Compute SHA-256 on raw uploaded file to prevent duplicate claims
        $sha256 = hash_file('sha256', $realPath);
        $duplicate = ActivityFile::where('student_id', $student->id)
            ->where('sha256_hash', $sha256)
            ->first();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'certificate_file' => 'Duplikasi terdeteksi (SHA-256): Sertifikat ini sudah pernah diklaim sebelumnya pada aktivitas Anda.',
            ]);
        }

        $uuid = (string) Str::uuid7();
        $ext = self::ALLOWED_MIMES[$detectedMime];
        $storagePath = "students/{$student->id}/{$uuid}.{$ext}";

        // Sanitize & re-encode images to strip EXIF metadata and polyglot payloads
        $contents = $this->sanitizeContents($realPath, $detectedMime);

        Storage::disk('private_vault')->put($storagePath, $contents, ['visibility' => 'private']);

        $activityFile = new ActivityFile([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'disk' => 'private_vault',
            'storage_path' => $storagePath,
            'original_name' => Str::limit(preg_replace('/[^A-Za-z0-9._ -]/', '_', $file->getClientOriginalName()), 120, ''),
            'mime_type' => $detectedMime,
            'file_size' => strlen($contents),
            'sha256_hash' => $sha256,
        ]);
        $activityFile->id = $uuid;
        $activityFile->save();

        return $activityFile;
    }

    /**
     * Re-encode JPEG/PNG through GD to destroy EXIF & embedded polyglot scripts.
     */
    private function sanitizeContents(string $realPath, string $mime): string
    {
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $img = @imagecreatefromjpeg($realPath);
            if ($img) {
                ob_start();
                imagejpeg($img, null, 85);
                $clean = ob_get_clean();
                imagedestroy($img);
                if ($clean !== false && strlen($clean) > 0) {
                    return $clean;
                }
            }
        }

        if ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
            $img = @imagecreatefrompng($realPath);
            if ($img) {
                imagesavealpha($img, true);
                ob_start();
                imagepng($img, null, 7);
                $clean = ob_get_clean();
                imagedestroy($img);
                if ($clean !== false && strlen($clean) > 0) {
                    return $clean;
                }
            }
        }

        return (string) file_get_contents($realPath);
    }

    /**
     * Generate a clean sample certificate PNG in the private vault for seeded demo records.
     */
    public function storeDemoCertificateImage(
        User $student,
        Activity $activity,
        string $titleId,
        string $organizer,
        string $dateStr,
        string $tierLabel
    ): ActivityFile {
        $w = 960;
        $h = 640;
        $img = imagecreatetruecolor($w, $h);

        $cream = imagecolorallocate($img, 249, 231, 169);      // #F9E7A9
        $ivory = imagecolorallocate($img, 253, 246, 220);      // #FDF6DC
        $gold = imagecolorallocate($img, 242, 170, 0);         // #F2AA00
        $amber = imagecolorallocate($img, 205, 112, 0);        // #CD7000
        $espresso = imagecolorallocate($img, 76, 37, 0);       // #4C2500

        imagefill($img, 0, 0, $ivory);
        imagefilledrectangle($img, 0, 0, $w, 92, $espresso);
        imagefilledrectangle($img, 0, 92, $w, 102, $gold);
        imagerectangle($img, 24, 122, $w - 24, $h - 24, $amber);
        imagerectangle($img, 30, 128, $w - 30, $h - 30, $gold);
        imagefilledrectangle($img, 54, 150, 360, 186, $cream);

        imagestring($img, 5, 42, 36, 'WHOI.BUZZ  |  CAMPUS VERIFIED CREDENTIAL VAULT', $cream);
        imagestring($img, 4, 66, 160, strtoupper($tierLabel), $espresso);
        imagestring($img, 5, 56, 224, 'CERTIFICATE OF ACHIEVEMENT / SERTIFIKAT CAPAIAN', $amber);
        imagestring($img, 5, 56, 276, 'Recipient : '.$student->name, $espresso);
        imagestring($img, 5, 56, 316, 'Activity  : '.Str::limit($titleId, 68), $espresso);
        imagestring($img, 4, 56, 360, 'Organizer : '.$organizer, $espresso);
        imagestring($img, 4, 56, 396, 'Date      : '.$dateStr, $espresso);
        imagestring($img, 3, 56, 470, 'Stored in Zero-Trust Private Vault (Signed URL Preview Only)', $amber);
        imagestring($img, 3, 56, 496, 'Activity ULID: '.$activity->id, $espresso);

        ob_start();
        imagepng($img, null, 7);
        $bytes = (string) ob_get_clean();
        imagedestroy($img);

        $uuid = (string) Str::uuid7();
        $path = "students/{$student->id}/{$uuid}.png";
        Storage::disk('private_vault')->put($path, $bytes, ['visibility' => 'private']);

        $activityFile = new ActivityFile([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'disk' => 'private_vault',
            'storage_path' => $path,
            'original_name' => Str::slug(Str::limit($titleId, 40, '')).'.png',
            'mime_type' => 'image/png',
            'file_size' => strlen($bytes),
            'sha256_hash' => hash('sha256', $bytes),
        ]);
        $activityFile->id = $uuid;
        $activityFile->save();

        return $activityFile;
    }
}
