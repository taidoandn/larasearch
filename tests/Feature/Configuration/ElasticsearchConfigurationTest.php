<?php

test('elasticsearch configuration exposes the configured search index names', function () {
    expect(config('elasticsearch.host'))->toBeString()
        ->and(config('elasticsearch.indexes.job_listings'))->toBeString()->not->toBe('')
        ->and(config('elasticsearch.aliases.job_listings'))->toBeString()->not->toBe('')
        ->and(config('elasticsearch.mapping_path'))->toBeString()
        ->and(file_exists(config('elasticsearch.mapping_path')))->toBeTrue()
        ->and(config('elasticsearch.mapping.mappings.properties.title.fields.autocomplete.analyzer'))
        ->toBe('autocomplete_analyzer');
});
