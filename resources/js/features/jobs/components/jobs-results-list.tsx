import { ChevronRight } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { HighlightedText } from '@/features/jobs/components/highlighted-text';
import { JobLedgerMetric } from '@/features/jobs/components/job-ledger-metric';
import type {
    JobResultItem,
    JobResultsPayload,
} from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from '@/features/jobs/utils';
import { cn } from '@/lib/utils';

export function JobsResultsList({
    items,
    pagination,
    isRefreshing,
    selectedJobId,
    onSelectJob,
    onPageChange,
    onReset,
}: {
    items: JobResultItem[];
    pagination: JobResultsPayload['pagination'];
    isRefreshing: boolean;
    selectedJobId: number | null;
    onSelectJob: (job: JobResultItem) => void;
    onPageChange: (page: number) => void;
    onReset: () => void;
}) {
    if (isRefreshing && items.length === 0) {
        return <JobsResultsSkeleton />;
    }

    return (
        <div className="bg-white dark:bg-zinc-950">
            {items.length === 0 ? (
                <JobsEmptyState onReset={onReset} />
            ) : (
                items.map((job) => (
                    <JobResultRow
                        key={job.id}
                        job={job}
                        selected={selectedJobId === job.id}
                        onSelect={() => onSelectJob(job)}
                    />
                ))
            )}

            <div className="flex flex-col items-center gap-3 px-4 py-8 sm:px-6 sm:py-10">
                {pagination.total_pages > 1 ? (
                    <Pagination
                        page={pagination.page}
                        totalPages={pagination.total_pages}
                        disabled={isRefreshing}
                        onPageChange={onPageChange}
                    />
                ) : null}
                <p className="font-mono text-[10px] tracking-[0.28em] text-zinc-400 uppercase dark:text-zinc-500">
                    {pagination.total === 0
                        ? 'Showing 0 results'
                        : `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results`}
                </p>
            </div>
        </div>
    );
}

function JobResultRow({
    job,
    selected,
    onSelect,
}: {
    job: JobResultItem;
    selected: boolean;
    onSelect: () => void;
}) {
    const salary = formatSalaryRange(job.salary);

    return (
        <button
            type="button"
            aria-pressed={selected}
            onClick={onSelect}
            className={cn(
                'relative grid h-auto w-full border-b border-l-4 px-4 py-4 text-left shadow-none transition-colors sm:px-6 md:grid-cols-12 md:items-center',
                'justify-start whitespace-normal',
                selected
                    ? 'border-l-primary bg-accent/80 dark:bg-zinc-900/80'
                    : 'border-l-transparent bg-white hover:bg-zinc-50 dark:bg-zinc-950 dark:hover:bg-zinc-900/70',
            )}
        >
            <div className="md:col-span-6">
                <div className="flex items-start gap-2">
                    <div className="space-y-1">
                        <h2 className="text-sm font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                            <HighlightedText text={job.title} highlight={job.highlight.title} />
                        </h2>
                        <p className="text-xs text-zinc-500 dark:text-zinc-400">
                            {job.company.name ?? 'Unknown company'}{' '}
                            <span className="mx-1 text-zinc-300 dark:text-zinc-700">
                                •
                            </span>{' '}
                            {formatWorkModelLabel(job.work_model, 'Unknown model')}
                            {job.primary_location ? `, ${job.primary_location}` : ''}
                        </p>
                    </div>
                </div>

                {job.highlight.description ? (
                    <p className="mt-2 line-clamp-2 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                        <HighlightedText
                            text={job.highlight.description}
                            highlight={job.highlight.description}
                        />
                    </p>
                ) : null}
            </div>

            <div className="mt-4 flex gap-8 md:col-span-4 md:mt-0">
                <JobLedgerMetric label="Comp" value={salary} />
                <JobLedgerMetric
                    label="Exp"
                    value={formatExperienceLevelLabel(job.experience_level)}
                />
            </div>

            <div className="mt-4 flex items-center justify-between gap-4 md:col-span-2 md:mt-0 md:justify-end">
                <JobLedgerMetric
                    align="right"
                    label="Added"
                    value={formatDisplayDate(job.published_at)}
                />
                <ChevronRight
                    className={cn(
                        'size-4 transition-colors',
                        selected
                            ? 'text-primary'
                            : 'text-zinc-300 dark:text-zinc-600',
                    )}
                />
            </div>
        </button>
    );
}

function JobsEmptyState({
    onReset,
}: {
    onReset: () => void;
}) {
    return (
        <div className="px-4 py-12 sm:px-6">
            <div className="max-w-xl space-y-4">
                <p className="text-sm font-semibold text-zinc-900 dark:text-zinc-50">
                    No jobs matched your current search.
                </p>
                <p className="text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    Broaden the keyword, clear a filter, or reset the search to
                    view the full job set.
                </p>
                <Button
                    variant="outline"
                    onClick={onReset}
                    className="rounded-none border-zinc-200 bg-white px-5 py-3 text-[11px] font-semibold tracking-[0.24em] text-zinc-900 uppercase shadow-none hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                >
                    Reset Search
                </Button>
            </div>
        </div>
    );
}

function JobsResultsSkeleton() {
    return (
        <div className="bg-white dark:bg-zinc-950">
            {Array.from({ length: 5 }).map((_, index) => (
                <div
                    key={index}
                    className="grid gap-4 border-b border-l-4 border-l-transparent border-zinc-100 px-4 py-4 sm:px-6 md:grid-cols-12 md:items-center dark:border-zinc-900"
                >
                    <div className="space-y-3 md:col-span-6">
                        <Skeleton className="h-4 w-2/3 rounded-none" />
                        <Skeleton className="h-3 w-1/2 rounded-none" />
                        <Skeleton className="h-3 w-3/4 rounded-none" />
                    </div>
                    <div className="flex gap-8 md:col-span-4">
                        <div className="space-y-2">
                            <Skeleton className="h-3 w-10 rounded-none" />
                            <Skeleton className="h-3 w-24 rounded-none" />
                        </div>
                        <div className="space-y-2">
                            <Skeleton className="h-3 w-10 rounded-none" />
                            <Skeleton className="h-3 w-20 rounded-none" />
                        </div>
                    </div>
                    <div className="flex items-center justify-between gap-4 md:col-span-2 md:justify-end">
                        <div className="space-y-2 text-right">
                            <Skeleton className="h-3 w-12 rounded-none" />
                            <Skeleton className="h-3 w-16 rounded-none" />
                        </div>
                        <Skeleton className="size-4 rounded-none" />
                    </div>
                </div>
            ))}
        </div>
    );
}
