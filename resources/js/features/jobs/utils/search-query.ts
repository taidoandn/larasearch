import {
    JOB_SEARCH_DEFAULT_PAGE,
    JOB_SEARCH_DEFAULT_PER_PAGE,
    JOB_SEARCH_DEFAULT_SORT,
} from '@/features/jobs/constants';
import type { JobFilters, JobSearchQuery } from '@/features/jobs/types';
import { index as jobsIndex } from '@/routes/jobs';

export function compactJobSearchQuery(filters: JobFilters): JobSearchQuery {
    const query: JobSearchQuery = {};

    if (filters.q.trim() !== '') {
        query.q = filters.q;
    }

    if (filters.location.length > 0) {
        query.location = filters.location;
    }

    if (filters.category.length > 0) {
        query.category = filters.category;
    }

    if (filters.skills.length > 0) {
        query.skills = filters.skills;
    }

    if (filters.job_type.length > 0) {
        query.job_type = filters.job_type;
    }

    if (filters.work_model.length > 0) {
        query.work_model = filters.work_model;
    }

    if (filters.experience_level.length > 0) {
        query.experience_level = filters.experience_level;
    }

    if (filters.salary_min !== null) {
        query.salary_min = filters.salary_min;
    }

    if (filters.salary_max !== null) {
        query.salary_max = filters.salary_max;
    }

    if (filters.sort !== JOB_SEARCH_DEFAULT_SORT) {
        query.sort = filters.sort;
    }

    if (filters.page !== JOB_SEARCH_DEFAULT_PAGE) {
        query.page = filters.page;
    }

    if (filters.per_page !== JOB_SEARCH_DEFAULT_PER_PAGE) {
        query.per_page = filters.per_page;
    }

    return query;
}

export function buildJobSearchUrl(filters: JobFilters): string {
    return jobsIndex.url({
        query: compactJobSearchQuery(filters),
    });
}
