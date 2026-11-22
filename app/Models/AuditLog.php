<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class AuditLog extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'changes_json',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changes_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Append-Only Audit Trail: never allow mutation or deletion
        static::updating(fn () => throw new RuntimeException('AuditLog bersifat append-only dan tidak dapat diubah.'));
        static::deleting(fn () => throw new RuntimeException('AuditLog bersifat append-only dan tidak dapat dihapus.'));
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
