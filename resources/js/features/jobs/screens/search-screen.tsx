import { Head } from '@inertiajs/react';
import {
    Filters,
    ResultsList,
    ResultsToolbar,
    SummarySheet,
} from '@/features/jobs/components/search';
import { useSearch } from '@/features/jobs/hooks';
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

export function SearchScreen({
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
    } = useSearch({ results, filters });

    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title="Larasearch Jobs" />
            <div className="mx-auto flex w-full max-w-360 flex-1 flex-col gap-5 px-4 py-6 sm:px-6 lg:flex-row lg:gap-8 lg:px-8">
                <aside className="w-full shrink-0 lg:w-72">
                    <div className="rounded-4xl bg-secondary p-4 shadow-[0_20px_40px_-34px_rgba(0,74,198,0.08)] sm:p-6 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7.75rem)] lg:overflow-y-auto lg:pr-4 lg:shadow-[0_18px_40px_-34px_rgba(0,74,198,0.08)] lg:[scrollbar-color:oklch(0.389_0.026_274/0.28)_transparent] lg:[scrollbar-gutter:stable] lg:[scrollbar-width:thin] lg:[&::-webkit-scrollbar]:w-1.5 lg:[&::-webkit-scrollbar-thumb]:rounded-full lg:[&::-webkit-scrollbar-thumb]:bg-muted-foreground/25 lg:[&::-webkit-scrollbar-track]:bg-transparent">
                        <div className="mb-5 space-y-1">
                            <h1 className="font-display text-xl font-semibold tracking-tight text-foreground">
                                Filters
                            </h1>
                            <p className="text-sm text-muted-foreground">Refine your match</p>
                        </div>
                        <Filters
                            filters={filters}
                            facets={results.facets}
                            isRefreshing={isRefreshing}
                            onApply={applyFilters}
                        />
                    </div>
                </aside>

                <div className="flex min-w-0 flex-1 flex-col gap-5">
                    <section className="space-y-5">
                        <ResultsToolbar
                            total={results.total}
                            filters={filters}
                            sort={results.sort}
                            isRefreshing={isRefreshing}
                            onSortChange={changeSort}
                            onApplyFilters={applyFilters}
                            onResetFilters={resetFilters}
                        />

                        <div className="overflow-hidden rounded-4xl bg-card shadow-[0_18px_40px_-34px_rgba(0,74,198,0.08)]">
                            <ResultsList
                                items={results.data}
                                activeSkills={filters.skills}
                                pagination={results}
                                isRefreshing={isRefreshing}
                                selectedJobId={selectedJob?.id ?? null}
                                onSelectJob={selectJob}
                                onPageChange={changePage}
                                onReset={resetFilters}
                            />
                        </div>
                    </section>
                </div>

                <SummarySheet
                    job={selectedJob}
                    open={selectedJob !== null}
                    onOpenChange={setSummarySheetOpen}
                    searchQuery={searchQuery}
                />
            </div>
        </SearchLayout>
    );
}
