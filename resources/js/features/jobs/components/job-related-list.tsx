import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import type { JobResultItem, JobSearchContext } from '@/features/jobs/types';
import {
    formatSalaryRange,
    formatWorkModelLabel,
    sectionLabelClassName,
} from '@/features/jobs/utils';
import { show as jobsShow } from '@/routes/jobs';

export function JobRelatedList({
    jobId,
    relatedJobs,
    searchQuery,
}: {
    jobId: number;
    relatedJobs: JobResultItem[];
    searchQuery: JobSearchContext['index_query'];
}) {
    return (
        <div className="grid gap-6 md:grid-cols-2">
            {relatedJobs
                .filter((candidate) => candidate.id !== jobId)
                .map((candidate) => (
                    <Button
                        key={candidate.id}
                        asChild
                        variant="ghost"
                        className="h-auto rounded-none border border-zinc-200 bg-white p-6 text-left shadow-none hover:border-primary hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:bg-zinc-900"
                    >
                        <Link
                            href={jobsShow(candidate.slug, {
                                query: searchQuery,
                            })}
                        >
                            <div className="space-y-5">
                                <p className={sectionLabelClassName}>
                                    {candidate.company.name}
                                </p>
                                <h3 className="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                                    {candidate.title}
                                </h3>
                                <div className="flex flex-wrap items-center gap-3">
                                    <span className="border border-zinc-200 bg-zinc-50 px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-zinc-700 uppercase dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                                        {formatSalaryRange(candidate.salary)}
                                    </span>
                                    <span className="text-[11px] font-semibold tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                                        {formatWorkModelLabel(candidate.work_model)}
                                        {' / '}
                                        {candidate.primary_location ?? 'Remote'}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </Button>
                ))}
        </div>
    );
}
