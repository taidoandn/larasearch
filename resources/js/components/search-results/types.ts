export type SearchFilters = {
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

export type SearchResultItem = {
    id: string;
    slug: string;
    title: string;
    company: {
        name: string | null;
        slug: string | null;
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

export type SearchResultsPayload = {
    items: SearchResultItem[];
    pagination: {
        page: number;
        per_page: number;
        total: number;
        total_pages: number;
        has_more: boolean;
    };
    facets: {
        locations: Array<{ value: string; count: number }>;
        categories: Array<{ value: string; count: number }>;
        skills: Array<{ value: string; count: number }>;
        job_types: Array<{ value: string; count: number }>;
        work_models: Array<{ value: string; count: number }>;
        experience_levels: Array<{ value: string; count: number }>;
    };
    sort: string;
};
