import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import type {
    JobFilters,
    JobResultItem,
    JobResultsPayload,
} from '@/features/jobs/types';
import { compactJobSearchQuery } from '@/features/jobs/utils';
import { index as jobsIndex } from '@/routes/jobs';

export function useJobSearch({
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

        router.get(jobsIndex.url(), compactJobSearchQuery(nextFilters), {
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
        visitResults({
            q: '',
            location: '',
            category: '',
            skills: [],
            job_type: '',
            work_model: '',
            experience_level: '',
            salary_min: null,
            salary_max: null,
            sort: 'best_match',
            page: 1,
            per_page: filters.per_page,
        });
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
                page: 1,
            });
        },
    };
}
