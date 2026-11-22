<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'nim',
        'prodi_name',
        'faculty_name',
        'semester',
        'advisor_id',
        'public_slug',
        'is_public_enabled',
        'phone',
        'headline',
        'bio',
        'skills_json',
        'embedding',
        'yudisium_status',
    ];

    protected function casts(): array
    {
        return [
            'is_public_enabled' => 'boolean',
            'phone' => 'encrypted', // UU PDP Encrypted Casting
            'skills_json' => 'array',
            'embedding' => 'array',
            'semester' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * Compute 5-axis competency radar scores (0..100) from approved activities.
     */
    public function skillRadar(): array
    {
        $domains = [
            'Leadership' => 0,
            'Technical' => 0,
            'Communication' => 0,
            'Social' => 0,
            'Academic' => 0,
        ];

        $approved = Activity::where('student_id', $this->user_id)
            ->where('status', 'approved')
            ->with('category')
            ->get();

        foreach ($approved as $activity) {
            $domain = $activity->category?->competency_domain;
            if ($domain && array_key_exists($domain, $domains)) {
                $domains[$domain] = min(100, $domains[$domain] + (int) round($activity->awarded_points * 2.2));
            }
        }

        return $domains;
    }

    public function approvedPoints(): int
    {
        return (int) Activity::where('student_id', $this->user_id)
            ->where('status', 'approved')
            ->sum('awarded_points');
    }
}
