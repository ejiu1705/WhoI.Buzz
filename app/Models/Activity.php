<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use RuntimeException;

class Activity extends Model
{
    use HasUlids;

    public const TIER_CAMPUS_ISSUED = 1;
    public const TIER_VERIFIED_EXTERNAL = 2;
    public const TIER_ADMIN_REVIEWED = 3;

    protected $fillable = [
        'student_id',
        'category_id',
        'title_id',
        'title_en',
        'organizer',
        'role_in_activity',
        'activity_date',
        'status',
        'trust_tier',
        'awarded_points',
        'external_verify_url',
        'is_public',
        'ai_confidence_score',
        'ai_extracted_payload',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'is_public' => 'boolean',
            'trust_tier' => 'integer',
            'awarded_points' => 'integer',
            'ai_confidence_score' => 'integer',
            'ai_extracted_payload' => 'array',
            'locked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Enforce Immutable Approved Records at Model Observer level
        static::updating(function (Activity $activity) {
            $wasLocked = $activity->getOriginal('locked_at') !== null;
            if (! $wasLocked) {
                return;
            }

            // Only allow toggling visibility (is_public) or official revocation (status -> revoked)
            $dirty = array_keys($activity->getDirty());
            $allowedWhenLocked = ['is_public', 'status', 'updated_at'];
            $forbidden = array_diff($dirty, $allowedWhenLocked);

            if (! empty($forbidden)) {
                throw new RuntimeException(
                    'Immutable Approved Record: data kegiatan yang sudah disetujui dan terkunci tidak dapat diubah ('.implode(', ', $forbidden).').'
                );
            }

            if (in_array('status', $dirty, true) && $activity->status !== 'revoked') {
                throw new RuntimeException('Status kegiatan terkunci hanya dapat dicabut secara resmi (revoked).');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SkpiCategory::class, 'category_id');
    }

    public function file(): HasOne
    {
        return $this->hasOne(ActivityFile::class, 'activity_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ActivityValidation::class, 'activity_id')->latest('reviewed_at');
    }

    public function credential(): HasOne
    {
        return $this->hasOne(IssuedCredential::class, 'activity_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function trustTierMeta(): array
    {
        return match ($this->trust_tier) {
            self::TIER_CAMPUS_ISSUED => [
                'tier' => 1,
                'label' => 'Tier 1 — Campus Issued',
                'short' => 'Gold Badge',
                'description' => 'Diterbitkan langsung oleh sistem kampus via Auto-Claim dengan jaminan kriptografi 100%.',
                'badge_class' => 'bg-[#F2AA00] text-[#4C2500] border-[#CD7000]',
            ],
            self::TIER_VERIFIED_EXTERNAL => [
                'tier' => 2,
                'label' => 'Tier 2 — Digitally Verified External',
                'short' => 'Verified External',
                'description' => 'Sertifikat lembaga eksternal bereputasi dengan tautan verifikasi daring resmi yang terkonfirmasi.',
                'badge_class' => 'bg-[#CD7000] text-[#FDF6DC] border-[#4C2500]/30',
            ],
            default => [
                'tier' => 3,
                'label' => 'Tier 3 — Administratively Reviewed',
                'short' => 'Admin Reviewed',
                'description' => 'Diverifikasi secara administratif berdasarkan bukti dokumen oleh Dosen PA.',
                'badge_class' => 'bg-[#4C2500] text-[#F9E7A9] border-[#F2AA00]/40',
            ],
        };
    }

    /**
     * Generate 1-Click "Add to LinkedIn Profile" Certification URL.
     */
    public function linkedInAddUrl(): string
    {
        $regNumber = $this->credential?->registration_number ?? ('WB-ACT-'.strtoupper(substr($this->id, -8)));
        $verifyUrl = $this->credential
            ? route('verify.show', $this->credential->registration_number)
            : ($this->external_verify_url ?: route('verify.index'));

        $params = [
            'startTask' => 'CERTIFICATION_NAME',
            'name' => $this->title_en ?: $this->title_id,
            'organizationName' => $this->trust_tier === self::TIER_CAMPUS_ISSUED
                ? 'WhoI.Buzz Campus Verified Credential Hub'
                : $this->organizer,
            'issueYear' => $this->activity_date?->format('Y') ?? now()->format('Y'),
            'issueMonth' => $this->activity_date?->format('n') ?? now()->format('n'),
            'certId' => $regNumber,
            'certUrl' => $verifyUrl,
        ];

        return 'https://www.linkedin.com/profile/add?'.http_build_query($params);
    }
}
