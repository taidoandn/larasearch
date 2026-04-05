<?php

test('elasticsearch configuration exposes the expected phase 0 defaults', function () {
    expect(config('elasticsearch.host'))->toBeString()
        ->and(config('elasticsearch.indexes.job_listings'))->toBe('job_listings_v1')
        ->and(config('elasticsearch.aliases.job_listings'))->toBe('job_listings_current')
        ->and(config('elasticsearch.mapping.mappings.properties.title.fields.autocomplete.analyzer'))
        ->toBe('autocomplete_analyzer');
});
