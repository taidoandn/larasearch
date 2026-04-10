<?php

namespace App\Services;

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use Illuminate\Support\Str;

class JobSearchFilters
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     q: string,
     *     location: string,
     *     category: string,
     *     skills: array<int, string>,
     *     job_type: string,
     *     work_model: string,
     *     experience_level: string,
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
            'location' => self::string($input, 'location', 120),
            'category' => self::slug($input, 'category', 120),
            'skills' => self::skills($input['skills'] ?? []),
            'job_type' => self::enum($input['job_type'] ?? null, JobType::class),
            'work_model' => self::enum($input['work_model'] ?? null, WorkModel::class),
            'experience_level' => self::enum($input['experience_level'] ?? null, ExperienceLevel::class),
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

        foreach (['q', 'location', 'category', 'job_type', 'work_model', 'experience_level'] as $key) {
            if ($normalized[$key] !== '') {
                $compact[$key] = $normalized[$key];
            }
        }

        if ($normalized['skills'] !== []) {
            $compact['skills'] = $normalized['skills'];
        }

        foreach (['salary_min', 'salary_max'] as $key) {
            if ($normalized[$key] !== null) {
                $compact[$key] = $normalized[$key];
            }
        }

        if ($normalized['sort'] !== 'best_match') {
            $compact['sort'] = $normalized['sort'];
        }

        if ($normalized['page'] !== 1) {
            $compact['page'] = $normalized['page'];
        }

        if ($normalized['per_page'] !== 20) {
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
     * @param  array<string, mixed>  $input
     */
    protected static function slug(array $input, string $key, int $maxLength): string
    {
        $value = self::string($input, $key, $maxLength);

        return $value === '' ? '' : Str::slug($value);
    }

    /**
     * @return array<int, string>
     */
    protected static function skills(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
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

        return in_array($candidate, ['best_match', 'newest', 'salary_desc', 'salary_asc'], true)
            ? $candidate
            : 'best_match';
    }

    protected static function page(mixed $value): int
    {
        if (! is_numeric($value)) {
            return 1;
        }

        return max((int) $value, 1);
    }

    protected static function perPage(mixed $value): int
    {
        if (! is_numeric($value)) {
            return 20;
        }

        return min(max((int) $value, 1), 50);
    }
}
