import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { CompanyAvatar } from '@/features/jobs/components/shared';
import type { JobResultItem, JobSearchContext } from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from '@/features/jobs/utils';
import { show as jobsShow } from '@/routes/jobs';

export function RelatedList({
    jobId,
    relatedJobs,
    searchQuery,
}: {
    jobId: number;
    relatedJobs: JobResultItem[];
    searchQuery: JobSearchContext['index_query'];
}) {
    const items = relatedJobs.filter((candidate) => candidate.id !== jobId);

    if (items.length === 0) {
        return (
            <div className="rounded-[1.75rem] bg-white px-8 py-10 text-sm leading-7 text-slate-600 shadow-[0_16px_36px_-30px_rgba(0,74,198,0.12)]">
                No closely related roles are available yet.
            </div>
        );
    }

    return (
        <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            {items.map((candidate, index) => (
                <Link
                    key={candidate.id}
                    href={jobsShow(candidate.slug, {
                        query: searchQuery,
                    })}
                    className={
                        'group flex min-h-80 flex-col justify-between rounded-[1.75rem] bg-white px-8 py-8 shadow-[0_16px_36px_-30px_rgba(0,74,198,0.14)] transition-all hover:-translate-y-0.5 hover:shadow-[0_22px_42px_-28px_rgba(0,74,198,0.18)]'
                    }
                >
                    <div className="flex flex-col gap-8">
                        <div className="flex items-start justify-between gap-4">
                            <CompanyAvatar
                                name={candidate.company.name ?? 'Unknown company'}
                                logoUrl={candidate.company.logo_url}
                            />
                            <span className="rounded-lg bg-white/80 px-3 py-1 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">
                                {index === items.length - 1 ? 'More from Co.' : 'Related Role'}
                            </span>
                        </div>

                        <div className="flex flex-col gap-2">
                            <h3 className="text-2xl font-bold tracking-tight text-slate-950 transition-colors group-hover:text-primary">
                                {candidate.title}
                            </h3>
                            <p className="text-sm font-medium text-slate-500">
                                {candidate.company.name ?? 'Unknown company'}
                                {' • '}
                                {candidate.primary_location ?? 'Remote'}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-col gap-6 border-t border-slate-100 pt-6">
                        <div className="flex flex-wrap gap-2">
                            <span className="rounded-lg bg-slate-100 px-3 py-1 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">
                                {candidate.work_model_label ??
                                    formatWorkModelLabel(candidate.work_model)}
                            </span>
                            <span className="rounded-lg bg-slate-100 px-3 py-1 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">
                                {candidate.experience_level_label ??
                                    formatExperienceLevelLabel(candidate.experience_level)}
                            </span>
                        </div>

                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <p className="text-lg font-extrabold text-slate-950">
                                    {formatSalaryRange(candidate.salary)}
                                </p>
                                <p className="text-[10px] font-bold tracking-[0.18em] text-slate-400 uppercase">
                                    {formatDisplayDate(candidate.published_at)}
                                </p>
                            </div>
                            <ArrowRight className="size-5 text-primary transition-transform group-hover:translate-x-1" />
                        </div>
                    </div>
                </Link>
            ))}
        </div>
    );
}
