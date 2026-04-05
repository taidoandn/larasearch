<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'country_code',
        'state_name',
        'city_name',
        'district_name',
        'display_name',
        'latitude',
        'longitude',
        'is_active',
    ];

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class, 'primary_location_id');
    }
}
