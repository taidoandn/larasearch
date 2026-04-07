<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Company $company): void {
            $company->loadMissing('jobListings');
        });
    }

    protected $fillable = [
        'slug',
        'name',
        'legal_name',
        'description',
        'website_url',
        'logo_url',
        'industry',
        'company_size',
        'founded_year',
        'country_code',
        'is_verified',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'status' => CompanyStatus::class,
        ];
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }
}
