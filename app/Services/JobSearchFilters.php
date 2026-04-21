<?php

namespace App\Services;

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use Illuminate\Support\Str;

class JobSearchFilters
{
    public const string DEFAULT_SORT = 'best_match';

    public const int DEFAULT_PAGE = 1;

    public const int DEFAULT_PER_PAGE = 20;

    public const int MAX_PER_PAGE = 50;

    public const array SORT_OPTIONS = [
        self::DEFAULT_SORT,
        'newest',
        'salary_desc',
        'salary_asc',
    ];

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     q: string,
     *     location: array<int, string>,
     *     category: array<int, string>,
     *     skills: array<int, string>,
     *     job_type: array<int, string>,
     *     work_model: array<int, string>,
     *     experience_level: array<int, string>,
     *     salary_min: ?int,
     *     salary_max: ?int,
     *     sort: string,
     *     page: int,
     *     per_page: int
     * }
     */
    public static function normalize(array $input): array
    {
        return [
            'q' => self::string($input, 'q', 255),
            'location' => self::slugList($input['location'] ?? []),
            'category' => self::slugList($input['category'] ?? []),
            'skills' => self::skills($input['skills'] ?? []),
            'job_type' => self::enumList($input['job_type'] ?? [], JobType::class),
            'work_model' => self::enumList($input['work_model'] ?? [], WorkModel::class),
            'experience_level' => self::enumList($input['experience_level'] ?? [], ExperienceLevel::class),
            'salary_min' => self::integer($input['salary_min'] ?? null),
            'salary_max' => self::integer($input['salary_max'] ?? null),
            'sort' => self::sort($input['sort'] ?? null),
            'page' => self::page($input['page'] ?? null),
            'per_page' => self::perPage($input['per_page'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function compact(array $input): array
    {
        $normalized = self::normalize($input);
        $compact = [];

        if ($normalized['q'] !== '') {
            $compact['q'] = $normalized['q'];
        }

        foreach (['location', 'category', 'skills', 'job_type', 'work_model', 'experience_level'] as $key) {
            if ($normalized[$key] !== []) {
                $compact[$key] = $normalized[$key];
            }
        }

        foreach (['salary_min', 'salary_max'] as $key) {
            if ($normalized[$key] !== null) {
                $compact[$key] = $normalized[$key];
            }
        }

        if ($normalized['sort'] !== self::DEFAULT_SORT) {
            $compact['sort'] = $normalized['sort'];
        }

        if ($normalized['page'] !== self::DEFAULT_PAGE) {
            $compact['page'] = $normalized['page'];
        }

        if ($normalized['per_page'] !== self::DEFAULT_PER_PAGE) {
            $compact['per_page'] = $normalized['per_page'];
        }

        return $compact;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected static function string(array $input, string $key, int $maxLength): string
    {
        $value = trim((string) ($input[$key] ?? ''));

        return mb_strlen($value) <= $maxLength ? $value : '';
    }

    /**
     * @return array<int, string>
     */
    protected static function slugList(mixed $value): array
    {
        return collect(self::arrayValue($value))
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter(fn (string $item): bool => $item !== '' && mb_strlen($item) <= 120)
            ->map(fn (string $item): string => Str::slug($item))
            ->filter(fn (string $item): bool => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected static function skills(mixed $value): array
    {
        return collect(self::arrayValue($value))
            ->map(fn (mixed $skill): string => trim((string) $skill))
            ->filter(fn (string $skill): bool => $skill !== '' && mb_strlen($skill) <= 120)
            ->map(fn (string $skill): string => Str::slug($skill))
            ->filter(fn (string $skill): bool => $skill !== '')
            ->values()
            ->all();
    }

    /**
     * @param  class-string<\BackedEnum>  $enumClass
     */
    protected static function enum(mixed $value, string $enumClass): string
    {
        $candidate = trim((string) $value);

        if ($candidate === '') {
            return '';
        }

        return $enumClass::tryFrom($candidate)?->value ?? '';
    }

    /**
     * @param  class-string<\BackedEnum>  $enumClass
     * @return array<int, string>
     */
    protected static function enumList(mixed $value, string $enumClass): array
    {
        return collect(self::arrayValue($value))
            ->map(fn (mixed $item): string => self::enum($item, $enumClass))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, mixed>
     */
    protected static function arrayValue(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    protected static function integer(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $integer = (int) $value;

        return $integer >= 0 ? $integer : null;
    }

    protected static function sort(mixed $value): string
    {
        $candidate = trim((string) $value);

        return in_array($candidate, self::SORT_OPTIONS, true)
            ? $candidate
            : self::DEFAULT_SORT;
    }

    protected static function page(mixed $value): int
    {
        if (! is_numeric($value)) {
            return self::DEFAULT_PAGE;
        }

        return max((int) $value, self::DEFAULT_PAGE);
    }

    protected static function perPage(mixed $value): int
    {
        if (! is_numeric($value)) {
            return self::DEFAULT_PER_PAGE;
        }

        return min(max((int) $value, self::DEFAULT_PAGE), self::MAX_PER_PAGE);
    }
}
