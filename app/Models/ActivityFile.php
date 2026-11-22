<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class ActivityFile extends Model
{
    use HasUuids;

    protected $fillable = [
        'activity_id',
        'student_id',
        'disk',
        'storage_path',
        'original_name',
        'mime_type',
        'file_size',
        'sha256_hash',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function temporarySignedPreviewUrl(int $minutes = 5): string
    {
        return URL::temporarySignedRoute(
            'vault.preview',
            now()->addMinutes($minutes),
            ['activityFile' => $this->id]
        );
    }
}
