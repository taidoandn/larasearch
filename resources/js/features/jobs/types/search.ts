import type { JobFacetItem, JobResultItem } from './job';

export type JobSearchQuery = Partial<{
    q: string;
    location: string[];
    category: string[];
    skills: string[];
    job_type: string[];
    work_model: string[];
    experience_level: string[];
    salary_min: number;
    salary_max: number;
    sort: string;
    page: number;
    per_page: number;
}>;

export type JobFilters = {
    q: string;
    location: string[];
    category: string[];
    skills: string[];
    job_type: string[];
    work_model: string[];
    experience_level: string[];
    salary_min: number | null;
    salary_max: number | null;
    sort: string;
    page: number;
    per_page: number;
};

export type JobSearchContext = {
    index_query: JobSearchQuery;
};

export type JobResultsPayload = {
    data: JobResultItem[];
    current_page: number;
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
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
