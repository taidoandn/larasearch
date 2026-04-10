<?php

it('keeps landing preview sample data outside the jobs feature', function () {
    $searchPreview = file_get_contents(resource_path('js/features/landing/components/search-preview.tsx'));

    expect($searchPreview)
        ->not->toContain('@/features/jobs');
});

it('removes benchmark references from the targeted search docs', function () {
    $files = [
        base_path('docs/reference.md'),
        base_path('docs/prd.md'),
        base_path('docs/task.md'),
        base_path('docs/project-document.md'),
        base_path('docs/ui/screens/jobs-show/DESIGN.md'),
        base_path('docs/ui/screens/job-summary-sheet/DESIGN.md'),
        base_path('docs/superpowers/plans/2026-04-05-phase-0-foundation-search-domain.md'),
    ];

    foreach ($files as $file) {
        $contents = file_get_contents($file);

        expect($contents)
            ->not->toContain('benchmark')
            ->not->toContain('DatabaseSearchService')
            ->not->toContain('benchmark:search');
    }
});
