import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BriefcaseBusiness,
    Building2,
    Clock3,
    ExternalLink,
    MapPin,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { HighlightedText } from '@/features/jobs/components/highlighted-text';
import { JobDetailSection } from '@/features/jobs/components/job-detail-section';
import { JobRelatedList } from '@/features/jobs/components/job-related-list';
import type {
    JobDetailItem,
    JobResultItem,
    JobSearchContext,
} from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatJobTypeLabel,
    formatWorkModelLabel,
    sectionLabelClassName,
} from '@/features/jobs/utils';
import { useInitials } from '@/hooks/use-initials';
import { index as jobsIndex } from '@/routes/jobs';

export function JobDetailInfo({
    job,
    relatedJobs,
    searchContext,
}: {
    job: JobDetailItem;
    relatedJobs: JobResultItem[];
    searchContext: JobSearchContext;
}) {
    const getInitials = useInitials();
    const primaryLocation = job.primary_location ?? job.locations[0] ?? 'Remote';
    const applyUrl = job.application_url ?? job.company.website;
    const isApplyDisabled = applyUrl === null;

    return (
        <div className="mx-auto flex w-full max-w-420 flex-col md:flex-row xl:gap-0">
            <article className="flex-1 px-4 py-8 sm:px-6 lg:px-10 lg:py-12 xl:px-12 xl:py-14">
                <div className="w-full space-y-14">
                    <Button
                        asChild
                        variant="ghost"
                        className="h-auto rounded-none px-0 py-0 text-[11px] font-bold tracking-[0.26em] text-zinc-400 uppercase shadow-none hover:bg-transparent hover:text-primary dark:text-zinc-500 dark:hover:text-accent-foreground"
                    >
                        <Link
                            href={jobsIndex({
                                query: searchContext.index_query,
                            })}
                            className="inline-flex items-center gap-2"
                        >
                            <ArrowLeft className="size-3.5" />
                            Back To Search Results
                        </Link>
                    </Button>

                    <JobDetailSection title="Job Overview">
                        <p className="max-w-3xl text-base leading-8 text-zinc-600 dark:text-zinc-300">
                            <HighlightedText
                                text={job.overview}
                                highlight={job.highlight.description}
                            />
                        </p>
                    </JobDetailSection>

                    <JobDetailSection title="Key Responsibilities">
                        <ul className="space-y-6">
                            {job.responsibilities.map((item) => (
                                <li key={item} className="flex gap-5">
                                    <span className="text-lg leading-none font-semibold text-primary dark:text-accent-foreground">
                                        /
                                    </span>
                                    <span className="text-base leading-8 text-zinc-600 dark:text-zinc-300">
                                        {item}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </JobDetailSection>

                    <JobDetailSection title="Technical Requirements">
                        <div className="grid gap-px bg-zinc-200 sm:grid-cols-2 dark:bg-zinc-800">
                            {job.requirements.map((requirement) => (
                                <div
                                    key={requirement.label}
                                    className="space-y-2 border-t border-zinc-200 bg-white p-6 first:border-t-0 dark:border-zinc-800 dark:bg-zinc-950 sm:[&:nth-child(-n+2)]:border-t-0"
                                >
                                    <p className="text-[10px] font-semibold tracking-[0.28em] text-primary uppercase dark:text-accent-foreground">
                                        {requirement.label}
                                    </p>
                                    <p className="text-sm leading-6 font-medium text-zinc-900 dark:text-zinc-100">
                                        {requirement.value}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </JobDetailSection>

                    <JobDetailSection title="Related Opportunities">
                        <JobRelatedList
                            jobId={job.id}
                            relatedJobs={relatedJobs}
                            searchQuery={searchContext.index_query}
                        />
                    </JobDetailSection>
                </div>
            </article>

            <aside className="w-full max-w-88 border-t border-zinc-200 bg-white px-4 py-8 sm:px-6 xl:border-t-0 xl:border-l xl:px-6 xl:py-8 dark:border-zinc-800 dark:bg-zinc-950">
                <div className="space-y-10 xl:sticky xl:top-24">
                    <section className="space-y-6">
                        <div className="flex size-16 items-center justify-center border border-zinc-200 bg-white text-xl font-semibold tracking-tight text-primary dark:border-zinc-800 dark:bg-zinc-950 dark:text-accent-foreground">
                            {getInitials(job.company.name)}.
                        </div>
                        <div className="space-y-3">
                            <h2 className="text-xl font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                                {job.company.name}
                            </h2>
                            <p className="text-sm leading-7 text-zinc-500 dark:text-zinc-400">
                                {job.company.summary}
                            </p>
                        </div>
                        <div className="space-y-4 text-[11px] font-semibold tracking-[0.22em] text-zinc-500 uppercase dark:text-zinc-400">
                            <MetadataRow
                                icon={<Building2 className="size-4" />}
                                text={job.company.meta}
                            />
                            <MetadataRow
                                icon={<MapPin className="size-4" />}
                                text={`${formatJobTypeLabel(job.job_type, 'Full Time')} • ${primaryLocation}`}
                            />
                            <MetadataRow
                                icon={<BriefcaseBusiness className="size-4" />}
                                text={formatWorkModelLabel(job.work_model, 'Unknown model')}
                            />
                            <MetadataRow
                                icon={<Clock3 className="size-4" />}
                                text={`Posted ${formatDisplayDate(job.published_at, 'recently')}`}
                            />
                            <MetadataRow
                                icon={<ExternalLink className="size-4" />}
                                text={job.company.website ?? 'Company website unavailable'}
                                href={job.company.website}
                            />
                        </div>
                    </section>

                    <section className="space-y-5">
                        <h3 className={sectionLabelClassName}>
                            Job Specification
                        </h3>
                        <div className="space-y-5">
                            {job.summary_metrics.map((metric) => (
                                <div
                                    key={metric.label}
                                    className="space-y-1"
                                >
                                    <p className={sectionLabelClassName}>
                                        {metric.label}
                                    </p>
                                    <p className="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {metric.value}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </section>

                    <div className="space-y-4">
                        <div className="flex h-48 items-end justify-start border border-zinc-200 bg-[linear-gradient(180deg,rgba(39,39,42,0.12),rgba(39,39,42,0.03))] px-4 py-4 dark:border-zinc-800 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.02))]">
                            <p className="max-w-56 text-xs leading-6 text-zinc-700 dark:text-zinc-300">
                                {job.map_label ?? primaryLocation}
                            </p>
                        </div>
                        <Button
                            asChild={! isApplyDisabled}
                            disabled={isApplyDisabled}
                            className="h-12 w-full rounded-none bg-primary px-6 text-[11px] font-bold tracking-[0.28em] text-primary-foreground uppercase shadow-none hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {applyUrl ? (
                                <a
                                    href={applyUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    Apply For This Job
                                </a>
                            ) : (
                                <span>Apply For This Job</span>
                            )}
                        </Button>
                    </div>
                </div>
            </aside>
        </div>
    );
}

function MetadataRow({
    icon,
    text,
    href,
}: {
    icon: ReactNode;
    text: string;
    href?: string | null;
}) {
    return (
        <div className="flex items-center gap-3">
            <span className="text-zinc-400 dark:text-zinc-500">{icon}</span>
            {href ? (
                <a
                    href={href}
                    target="_blank"
                    rel="noreferrer"
                    className="underline-offset-2 hover:underline"
                >
                    {text}
                </a>
            ) : (
                <span>{text}</span>
            )}
        </div>
    );
}
