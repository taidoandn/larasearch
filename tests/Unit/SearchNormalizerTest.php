<?php

use App\Enums\JobType;
use App\Search\Utils\SearchNormalizer;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes keywords and string lists for safe search input', function () {
    expect(SearchNormalizer::keyword("  Laravel   Search\nEngineer  "))->toBe('Laravel Search Engineer')
        ->and(SearchNormalizer::keyword(str_repeat('a', 256)))->toBe('')
        ->and(SearchNormalizer::stringList([' Laravel ', "React\nNative", '', 'Laravel']))->toBe(['Laravel', 'React Native']);
});

it('normalizes slug and enum lists', function () {
    expect(SearchNormalizer::slugList(["St. John's", 'Da Nang', 'Da Nang', '']))->toBe(['st-johns', 'da-nang'])
        ->and(SearchNormalizer::enumList(['full-time', 'temporary', 'contract', 'contract'], JobType::class))->toBe(['full-time', 'contract']);
});

it('normalizes numeric pagination and allow-listed string values', function () {
    expect(SearchNormalizer::nonNegativeInteger('-10'))->toBeNull()
        ->and(SearchNormalizer::nonNegativeInteger('2500'))->toBe(2500)
        ->and(SearchNormalizer::page(0))->toBe(1)
        ->and(SearchNormalizer::perPage(999))->toBe(50)
        ->and(SearchNormalizer::allowedString('newest', ['best_match', 'newest'], 'best_match'))->toBe('newest')
        ->and(SearchNormalizer::allowedString('unknown', ['best_match', 'newest'], 'best_match'))->toBe('best_match');
});

it('compacts values that match defaults or empty values', function () {
    expect(SearchNormalizer::compactDefaults([
        'q' => 'laravel',
        'location' => [],
        'sort' => 'best_match',
        'page' => 2,
        'salary_min' => null,
    ], [
        'q' => '',
        'sort' => 'best_match',
        'page' => 1,
    ]))->toBe([
        'q' => 'laravel',
        'page' => 2,
    ]);
});
