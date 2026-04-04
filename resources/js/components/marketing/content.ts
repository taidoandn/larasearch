export const marketingNavItems = [
    { label: 'Features', href: '#features' },
    { label: 'Insights', href: '#preview' },
    { label: 'Search', href: '#queries' },
] as const;

export const marketingFeatures = [
    {
        title: 'Fast Search',
        description:
            'Start from role intent, stack filters quickly, and move into a ranked list that behaves like a working search interface rather than a form-heavy directory.',
    },
    {
        title: 'Smart Relevance',
        description:
            'Precision Match ranking combines query language, structured metadata, and freshness so the strongest roles rise without introducing noisy edge results.',
    },
    {
        title: 'Powerful Filters',
        description:
            'Compensation, work model, seniority, and location stay visible as first-class controls built for repeated narrowing during real search sessions.',
    },
] as const;

export const exampleQueries = [
    'laravel backend remote',
    'react da nang',
    'salary > 4500 usd',
    'senior + elasticsearch',
] as const;

export const technicalHighlights = [
    {
        title: 'ElasticSearch-backed indexing',
        description:
            'Dense search sessions stay responsive with an ingestion and retrieval layer tuned for large role catalogs and repeated query refinement.',
    },
    {
        title: 'Structured filtering',
        description:
            'Typed filters preserve legibility while still allowing natural-language discovery, ranking interpretation, and quick narrowing inside the same interface.',
    },
] as const;

export const technicalPreviewLines = [
    'query larasearch.jobs.search {',
    '  intent: "senior laravel remote"',
    '  filters: {',
    '    salary_min: 180000',
    '    work_model: ["remote", "hybrid"]',
    '    location: "United States"',
    '  }',
    '  ranking: "semantic + keyword + recency"',
    '}',
] as const;
