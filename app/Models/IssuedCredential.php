<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedCredential extends Model
{
    use HasUlids;

    protected $fillable = [
        'student_id',
        'activity_id',
        'type',
        'title',
        'registration_number',
        'payload_json',
        'signature_hash',
        'signature_algo',
        'issued_at',
        'revoked_at',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function verifyUrl(): string
    {
        return route('verify.show', $this->registration_number);
    }
}
