<?php

namespace App\Models;

use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'primary_location_id',
        'slug',
        'title',
        'normalized_title',
        'short_description',
        'description',
        'requirements',
        'benefits',
        'job_type',
        'work_model',
        'experience_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_is_visible',
        'application_url',
        'is_featured',
        'is_active',
        'published_at',
        'expires_at',
        'source_type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'salary_is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'published_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot(['is_primary', 'weight'])
            ->withTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchDocument(): array
    {
        $this->loadMissing(['company', 'primaryLocation', 'categories', 'skills']);

        return [
            'id' => (string) $this->getKey(),
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'company_name' => $this->company?->name,
            'locations' => $this->primaryLocation === null ? [] : [$this->primaryLocation->display_name],
            'category_names' => $this->categories->pluck('name')->values()->all(),
            'skills' => $this->skills->pluck('slug')->values()->all(),
            'skills_text' => $this->skills->pluck('name')->implode(' '),
            'job_type' => $this->job_type,
            'work_model' => $this->work_model,
            'experience_level' => $this->experience_level,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'salary_currency' => $this->salary_currency,
            'salary_is_visible' => $this->salary_is_visible,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at?->toAtomString(),
            'expires_at' => $this->expires_at?->toAtomString(),
        ];
    }
}
