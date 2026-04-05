<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'aliases_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aliases_json' => 'array',
        ];
    }

    public function jobListings(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class)
            ->withPivot(['is_primary', 'weight'])
            ->withTimestamps();
    }
}
