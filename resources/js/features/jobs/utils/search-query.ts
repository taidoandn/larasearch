import type { JobFilters, JobSearchQuery } from '@/features/jobs/types';

const DEFAULT_SORT = 'best_match';
const DEFAULT_PAGE = 1;
const DEFAULT_PER_PAGE = 20;

export function compactJobSearchQuery(filters: JobFilters): JobSearchQuery {
    const query: JobSearchQuery = {};

    if (filters.q.trim() !== '') {
        query.q = filters.q;
    }

    if (filters.location.trim() !== '') {
        query.location = filters.location;
    }

    if (filters.category.trim() !== '') {
        query.category = filters.category;
    }

    if (filters.skills.length > 0) {
        query.skills = filters.skills;
    }

    if (filters.job_type.trim() !== '') {
        query.job_type = filters.job_type;
    }

    if (filters.work_model.trim() !== '') {
        query.work_model = filters.work_model;
    }

    if (filters.experience_level.trim() !== '') {
        query.experience_level = filters.experience_level;
    }

    if (filters.salary_min !== null) {
        query.salary_min = filters.salary_min;
    }

    if (filters.salary_max !== null) {
        query.salary_max = filters.salary_max;
    }

    if (filters.sort !== DEFAULT_SORT) {
        query.sort = filters.sort;
    }

    if (filters.page !== DEFAULT_PAGE) {
        query.page = filters.page;
    }

    if (filters.per_page !== DEFAULT_PER_PAGE) {
        query.per_page = filters.per_page;
    }

    return query;
}
