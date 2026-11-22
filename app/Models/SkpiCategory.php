<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkpiCategory extends Model
{
    use HasFactory, HasUlids;

    // Notice created_by is intentionally NOT in $fillable (Modul 6 Mass Assignment protection)
    protected $fillable = [
        'curriculum_version',
        'code',
        'name_id',
        'name_en',
        'competency_domain',
        'is_mandatory',
        'default_points',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'default_points' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'category_id');
    }

    // Local Query Scopes (Modul 2)
    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeByDomain(Builder $query, string $domain): Builder
    {
        return $query->where('competency_domain', $domain);
    }
}
