<?php

namespace App\Search\Utils;

use Illuminate\Support\Str;

class SearchNormalizer
{
    public const int DEFAULT_PAGE = 1;

    public const int DEFAULT_PER_PAGE = 20;

    public const int MAX_PER_PAGE = 50;

    public static function keyword(mixed $value, int $maxLength = 255): string
    {
        $keyword = trim((string) preg_replace('/\s+/u', ' ', (string) $value));

        return mb_strlen($keyword) <= $maxLength ? $keyword : '';
    }

    /**
     * @return array<int, string>
     */
    public static function stringList(mixed $value, int $maxLength = 120): array
    {
        return collect(self::arrayValue($value))
            ->map(fn (mixed $item): string => trim((string) preg_replace('/\s+/u', ' ', (string) $item)))
            ->filter(fn (string $item): bool => $item !== '' && mb_strlen($item) <= $maxLength)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function slugList(mixed $value, int $maxLength = 120): array
    {
        return collect(self::stringList($value, $maxLength))
            ->map(fn (string $item): string => Str::slug($item))
            ->filter(fn (string $item): bool => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  class-string<\BackedEnum>  $enumClass
     * @return array<int, string>
     */
    public static function enumList(mixed $value, string $enumClass): array
    {
        return collect(self::arrayValue($value))
            ->map(fn (mixed $item): string => $enumClass::tryFrom(trim((string) $item))?->value ?? '')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $allowed
     */
    public static function allowedString(mixed $value, array $allowed, string $default): string
    {
        $candidate = trim((string) $value);

        return in_array($candidate, $allowed, true) ? $candidate : $default;
    }

    public static function nonNegativeInteger(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $integer = (int) $value;

        return $integer >= 0 ? $integer : null;
    }

    public static function page(mixed $value): int
    {
        return is_numeric($value)
            ? max((int) $value, self::DEFAULT_PAGE)
            : self::DEFAULT_PAGE;
    }

    public static function perPage(mixed $value): int
    {
        return is_numeric($value)
            ? min(max((int) $value, self::DEFAULT_PAGE), self::MAX_PER_PAGE)
            : self::DEFAULT_PER_PAGE;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public static function compactDefaults(array $values, array $defaults): array
    {
        return collect($values)
            ->reject(fn (mixed $value, string $key): bool => array_key_exists($key, $defaults) && $defaults[$key] === $value)
            ->reject(fn (mixed $value): bool => $value === [] || $value === '' || $value === null)
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
}
