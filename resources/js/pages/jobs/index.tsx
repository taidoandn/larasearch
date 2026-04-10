import { Head } from '@inertiajs/react';
import { JobSummarySheet } from '@/features/jobs/components/job-summary-sheet';
import { JobsFilters } from '@/features/jobs/components/jobs-filters';
import { JobsResultsList } from '@/features/jobs/components/jobs-results-list';
import { JobsResultsToolbar } from '@/features/jobs/components/jobs-results-toolbar';
import { useJobSearch } from '@/features/jobs/hooks/use-job-search';
import type { JobFilters, JobResultsPayload } from '@/features/jobs/types';
import { compactJobSearchQuery } from '@/features/jobs/utils';
import SearchLayout from '@/layouts/search-layout';
import { index as jobsIndex } from '@/routes/jobs';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Larasearch',
        href: jobsIndex(),
    },
    {
        title: 'Jobs',
        href: jobsIndex(),
    },
];

export default function JobsIndexPage({
    results,
    filters,
}: {
    results: JobResultsPayload;
    filters: JobFilters;
}) {
    const searchQuery = compactJobSearchQuery(filters);
    const {
        isRefreshing,
        selectedJob,
        applyFilters,
        resetFilters,
        selectJob,
        setSummarySheetOpen,
        changePage,
        changeSort,
    } = useJobSearch({ results, filters });

    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title="Larasearch Jobs" />
            <div className="flex flex-1 flex-col bg-zinc-50/50 dark:bg-zinc-950">
                <div className="border-b border-zinc-200 bg-white/95 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                    <JobsFilters
                        filters={filters}
                        facets={results.facets}
                        isRefreshing={isRefreshing}
                        onApply={applyFilters}
                        onReset={resetFilters}
                    />
                </div>

                <JobsResultsToolbar
                    total={results.pagination.total}
                    sort={results.sort}
                    isRefreshing={isRefreshing}
                    onSortChange={changeSort}
                />

                <JobsResultsList
                    items={results.items}
                    pagination={results.pagination}
                    isRefreshing={isRefreshing}
                    selectedJobId={selectedJob?.id ?? null}
                    onSelectJob={selectJob}
                    onPageChange={changePage}
                    onReset={resetFilters}
                />

                <JobSummarySheet
                    job={selectedJob}
                    open={selectedJob !== null}
                    onOpenChange={setSummarySheetOpen}
                    searchQuery={searchQuery}
                />
            </div>
        </SearchLayout>
    );
}
