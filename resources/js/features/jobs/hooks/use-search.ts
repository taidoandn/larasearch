import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { createDefaultJobFilters, JOB_SEARCH_DEFAULT_PAGE } from '@/features/jobs/constants';
import type {
    JobFilters,
    JobResultItem,
    JobResultsPayload,
} from '@/features/jobs/types';
import { buildJobSearchUrl } from '@/features/jobs/utils';

export function useSearch({
    results,
    filters,
}: {
    results: JobResultsPayload;
    filters: JobFilters;
}) {
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [selectedJobId, setSelectedJobId] = useState<number | null>(null);

    const selectedJob = useMemo(
        () => results.items.find((job) => job.id === selectedJobId) ?? null,
        [results.items, selectedJobId],
    );

    const visitResults = (nextFilters: JobFilters): void => {
        setSelectedJobId(null);
        setIsRefreshing(true);

        router.get(buildJobSearchUrl(nextFilters), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onError: () => {
                setIsRefreshing(false);
            },
            onFinish: () => {
                setIsRefreshing(false);
            },
        });
    };

    const resetFilters = (): void => {
        visitResults(createDefaultJobFilters(filters.per_page));
    };

    return {
        isRefreshing,
        selectedJob,
        applyFilters: visitResults,
        resetFilters,
        selectJob: (job: JobResultItem) => {
            setSelectedJobId(job.id);
        },
        setSummarySheetOpen: (open: boolean) => {
            if (! open) {
                setSelectedJobId(null);
            }
        },
        changePage: (page: number) => {
            visitResults({
                ...filters,
                page,
            });
        },
        changeSort: (sort: string) => {
            visitResults({
                ...filters,
                sort,
                page: JOB_SEARCH_DEFAULT_PAGE,
            });
        },
    };
}
