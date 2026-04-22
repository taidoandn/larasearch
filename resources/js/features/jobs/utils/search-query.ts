import {
    JOB_SEARCH_ARRAY_FILTER_KEYS,
    JOB_SEARCH_DEFAULT_PAGE,
    JOB_SEARCH_DEFAULT_PER_PAGE,
    JOB_SEARCH_DEFAULT_SORT,
    JOB_SEARCH_NULLABLE_NUMBER_KEYS,
} from '@/features/jobs/constants';
import type { JobFilters, JobSearchQuery } from '@/features/jobs/types';
import { index as jobsIndex } from '@/routes/jobs';

export function compactJobSearchQuery(filters: JobFilters): JobSearchQuery {
    const query: JobSearchQuery = {};

    if (filters.q.trim() !== '') {
        query.q = filters.q;
    }

    for (const key of JOB_SEARCH_ARRAY_FILTER_KEYS) {
        if (filters[key].length > 0) {
            query[key] = filters[key];
        }
    }

    for (const key of JOB_SEARCH_NULLABLE_NUMBER_KEYS) {
        if (filters[key] !== null) {
            query[key] = filters[key];
        }
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
