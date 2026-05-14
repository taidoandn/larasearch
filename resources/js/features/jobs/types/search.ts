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
