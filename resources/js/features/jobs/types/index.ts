export type JobSearchQuery = Partial<{
    q: string;
    location: string;
    category: string;
    skills: string[];
    job_type: string;
    work_model: string;
    experience_level: string;
    salary_min: number;
    salary_max: number;
    sort: string;
    page: number;
    per_page: number;
}>;

export type JobFilters = {
    q: string;
    location: string;
    category: string;
    skills: string[];
    job_type: string;
    work_model: string;
    experience_level: string;
    salary_min: number | null;
    salary_max: number | null;
    sort: string;
    page: number;
    per_page: number;
};

export type JobFacetItem = {
    value: string;
    label?: string | null;
    count: number;
};

export type JobSuggestionItem = {
    label: string;
    type: string;
};

export type JobResultItem = {
    id: number;
    slug: string;
    title: string;
    description: string;
    application_url: string | null;
    company: {
        name: string | null;
        slug: string | null;
        website: string | null;
    };
    primary_location: string | null;
    locations: string[];
    skills: string[];
    salary: {
        min: number | null;
        max: number | null;
        currency: string | null;
        is_visible: boolean;
    };
    job_type: string | null;
    work_model: string | null;
    experience_level: string | null;
    published_at: string | null;
    highlight: {
        title: string | null;
        description: string | null;
    };
};

export type JobDetailMetric = {
    label: string;
    value: string;
};

export type JobDetailItem = {
    id: number;
    slug: string;
    title: string;
    application_url: string | null;
    company: {
        name: string;
        slug: string;
        summary: string;
        meta: string;
        website: string | null;
    };
    locations: string[];
    primary_location: string | null;
    work_model: string | null;
    job_type: string | null;
    salary: {
        min: number | null;
        max: number | null;
        currency: string | null;
        is_visible: boolean;
    };
    overview: string;
    responsibilities: string[];
    requirements: JobDetailMetric[];
    summary_metrics: JobDetailMetric[];
    skills: string[];
    map_label: string | null;
    published_at: string | null;
    highlight: {
        title: string | null;
        description: string | null;
    };
};

export type JobSearchContext = {
    index_query: JobSearchQuery;
};

export type JobResultsPayload = {
    items: JobResultItem[];
    pagination: {
        page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        total_pages: number;
        has_more: boolean;
    };
    facets: {
        locations: JobFacetItem[];
        categories: JobFacetItem[];
        skills: JobFacetItem[];
        job_types: JobFacetItem[];
        work_models: JobFacetItem[];
        experience_levels: JobFacetItem[];
    };
    sort: string;
};
