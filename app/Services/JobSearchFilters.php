<?php

namespace App\Services;

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Search\Utils\SearchNormalizer;

class JobSearchFilters
{
    public const string DEFAULT_SORT = 'best_match';

    public const int DEFAULT_PAGE = SearchNormalizer::DEFAULT_PAGE;

    public const int DEFAULT_PER_PAGE = SearchNormalizer::DEFAULT_PER_PAGE;

    public const int MAX_PER_PAGE = SearchNormalizer::MAX_PER_PAGE;

    public const array SORT_OPTIONS = [
        self::DEFAULT_SORT,
        'newest',
        'salary_desc',
        'salary_asc',
    ];

    public const array DEFAULTS = [
        'q' => '',
        'location' => [],
        'category' => [],
        'skills' => [],
        'job_type' => [],
        'work_model' => [],
        'experience_level' => [],
        'salary_min' => null,
        'salary_max' => null,
        'sort' => self::DEFAULT_SORT,
        'page' => self::DEFAULT_PAGE,
        'per_page' => self::DEFAULT_PER_PAGE,
    ];

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        return [
            'q' => SearchNormalizer::keyword($input['q'] ?? ''),
            'location' => SearchNormalizer::slugList($input['location'] ?? []),
            'category' => SearchNormalizer::slugList($input['category'] ?? []),
            'skills' => SearchNormalizer::slugList($input['skills'] ?? []),
            'job_type' => SearchNormalizer::enumList($input['job_type'] ?? [], JobType::class),
            'work_model' => SearchNormalizer::enumList($input['work_model'] ?? [], WorkModel::class),
            'experience_level' => SearchNormalizer::enumList($input['experience_level'] ?? [], ExperienceLevel::class),
            'salary_min' => SearchNormalizer::nonNegativeInteger($input['salary_min'] ?? null),
            'salary_max' => SearchNormalizer::nonNegativeInteger($input['salary_max'] ?? null),
            'sort' => SearchNormalizer::allowedString($input['sort'] ?? null, self::SORT_OPTIONS, self::DEFAULT_SORT),
            'page' => SearchNormalizer::page($input['page'] ?? null),
            'per_page' => SearchNormalizer::perPage($input['per_page'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function compact(array $input): array
    {
        return SearchNormalizer::compactDefaults(self::normalize($input), self::DEFAULTS);
    }
}
