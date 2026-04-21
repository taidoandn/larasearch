import type { JobFilters } from '@/features/jobs/types';

export const JOB_SEARCH_DEFAULT_SORT = 'best_match';
export const JOB_SEARCH_DEFAULT_PAGE = 1;
export const JOB_SEARCH_DEFAULT_PER_PAGE = 20;

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
