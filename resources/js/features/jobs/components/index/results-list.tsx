import { Link, router } from '@inertiajs/react';
import { Bookmark, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { LedgerMetric } from '@/features/jobs/components/shared';
import type {
    JobFilters,
    JobResultItem,
    JobResultsPayload,
} from '@/features/jobs/types';
import { cn } from '@/lib/utils';
import { index as jobsIndex, show as jobsShow } from '@/routes/jobs';

export function JobsResultsList({
    items,
    pagination,
    filters,
}: {
    items: JobResultItem[];
    pagination: JobResultsPayload['pagination'];
    filters: JobFilters;
}) {
    return (
        <div className="bg-white dark:bg-zinc-950">
            {items.length === 0 ? (
                <div className="px-4 py-10 sm:px-6">
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        No jobs matched your current search.
                    </p>
                </div>
            ) : (
                items.map((job) => (
                    <JobResultRow key={job.id} job={job} />
                ))
            )}

            <div className="flex flex-col items-center gap-3 px-4 py-8 sm:px-6 sm:py-10">
                {pagination.has_more ? (
                    <Button
                        variant="outline"
                        onClick={() => {
                            router.get(jobsIndex.url(), {
                                ...filters,
                                page: pagination.page + 1,
                            }, {
                                preserveState: true,
                                preserveScroll: true,
                                replace: true,
                            });
                        }}
                        className="rounded-none border-zinc-200 bg-white px-8 py-3 text-[11px] font-semibold tracking-[0.28em] text-zinc-900 uppercase shadow-none hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                    >
                        Load Next Page
                    </Button>
                ) : null}
                <p className="font-mono text-[10px] tracking-[0.28em] text-zinc-400 uppercase dark:text-zinc-500">
                    Showing {Math.min(pagination.page * pagination.per_page, pagination.total)} of {pagination.total} records
                </p>
            </div>
        </div>
    );
}

function JobResultRow({
    job,
}: {
    job: JobResultItem;
}) {
    const salary = ! job.salary.is_visible || (job.salary.min === null && job.salary.max === null)
        ? 'Comp undisclosed'
        : `${job.salary.currency ?? ''} ${job.salary.min ?? '0'} - ${job.salary.max ?? '0'}`;

    return (
        <Link
            href={jobsShow(job.slug)}
            className={cn(
                'relative grid h-auto w-full rounded-none border-b border-l-4 border-zinc-100 px-4 py-4 text-left shadow-none sm:px-6 md:grid-cols-12 md:items-center',
                'justify-start whitespace-normal hover:bg-zinc-50 dark:border-zinc-900 dark:hover:bg-zinc-900/70',
                'border-l-transparent bg-white dark:bg-zinc-950',
            )}
        >
            <div className="md:col-span-5">
                <div className="flex items-start gap-2">
                    <h2 className="text-sm font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                        {job.title}
                    </h2>
                </div>

                <p className="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    {job.company.name ?? 'Unknown company'}{' '}
                    <span className="mx-1 text-zinc-300 dark:text-zinc-700">
                        •
                    </span>{' '}
                    {job.work_model ?? 'Unknown model'}, {job.primary_location ?? 'Unknown location'}
                </p>
            </div>

            <div className="mt-4 flex gap-8 md:col-span-4 md:mt-0">
                <LedgerMetric label="Comp" value={salary.trim()} />
                <LedgerMetric label="Exp" value={job.experience_level ?? 'Unknown'} />
            </div>

            <div className="mt-4 flex items-center justify-between gap-4 md:col-span-3 md:mt-0 md:justify-end">
                <LedgerMetric
                    align="right"
                    label="Added"
                    value={job.published_at ?? 'Unknown'}
                />
                <Bookmark
                    className={cn(
                        'size-4',
                        'text-zinc-300 dark:text-zinc-600',
                    )}
                />
                <ChevronRight className="size-4 text-zinc-300 dark:text-zinc-600" />
            </div>
        </Link>
    );
}
