<?php

namespace App\Models;

use App\Enums\ExperienceLevel;
use App\Enums\JobListingSourceType;
use App\Enums\JobType;
use App\Enums\WorkModel;
use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

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
            'job_type' => JobType::class,
            'work_model' => WorkModel::class,
            'experience_level' => ExperienceLevel::class,
            'source_type' => JobListingSourceType::class,
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

    public function scopeVisibleInSearch(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('published_at', '<=', now())
            ->where(function (Builder $builder): void {
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isVisibleInSearch(): bool
    {
        $now = now();

        return $this->is_active
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo($now)
            && ($this->expires_at === null || $this->expires_at->greaterThan($now));
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchDocument(): array
    {
        $this->loadMissing(['company', 'primaryLocation', 'categories', 'skills']);
        $locationLabel = $this->primaryLocation?->display_name;
        $locationKey = $this->primaryLocation?->city_name === null
            ? null
            : Str::slug($this->primaryLocation->city_name);

        return [
            'id' => $this->getKey(),
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'application_url' => $this->application_url,
            'company_name' => $this->company?->name,
            'company_slug' => $this->company?->slug,
            'company_website' => $this->company?->website_url,
            'location_slugs' => $locationKey === null ? [] : [$locationKey],
            'location_labels' => $locationLabel === null ? [] : [$locationLabel],
            'category_slugs' => $this->categories->pluck('slug')->values()->all(),
            'category_names' => $this->categories->pluck('name')->values()->all(),
            'skill_slugs' => $this->skills->pluck('slug')->values()->all(),
            'skills' => $this->skills->pluck('name')->values()->all(),
            'skills_text' => $this->skills->pluck('name')->implode(' '),
            'job_type' => $this->job_type?->value,
            'work_model' => $this->work_model?->value,
            'experience_level' => $this->experience_level?->value,
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
