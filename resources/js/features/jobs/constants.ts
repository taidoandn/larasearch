import type { JobFilters } from '@/features/jobs/types';

export const JOB_SEARCH_DEFAULT_SORT = 'best_match';
export const JOB_SEARCH_DEFAULT_PAGE = 1;
export const JOB_SEARCH_DEFAULT_PER_PAGE = 20;
export const JOB_SEARCH_ARRAY_FILTER_KEYS = [
    'location',
    'category',
    'skills',
    'job_type',
    'work_model',
    'experience_level',
] as const satisfies ReadonlyArray<keyof JobFilters>;
export const JOB_SEARCH_NULLABLE_NUMBER_KEYS = [
    'salary_min',
    'salary_max',
] as const satisfies ReadonlyArray<keyof JobFilters>;

export function createDefaultJobFilters(perPage = JOB_SEARCH_DEFAULT_PER_PAGE): JobFilters {
    return {
        q: '',
        location: [],
        category: [],
        skills: [],
        job_type: [],
        work_model: [],
        experience_level: [],
        salary_min: null,
        salary_max: null,
        sort: JOB_SEARCH_DEFAULT_SORT,
        page: JOB_SEARCH_DEFAULT_PAGE,
        per_page: perPage,
    };
}
