import { Link } from '@inertiajs/react';
import { ArrowRight, MapPin } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent } from '@/components/ui/sheet';
import { HighlightedText } from '@/features/jobs/components/highlighted-text';
import type { JobResultItem, JobSearchContext } from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatSalaryRange,
    sectionLabelClassName,
    formatWorkModelLabel,
} from '@/features/jobs/utils';
import { useInitials } from '@/hooks/use-initials';
import { show as jobsShow } from '@/routes/jobs';

export function JobSummarySheet({
    job,
    open,
    onOpenChange,
    searchQuery,
}: {
    job: JobResultItem | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    searchQuery: JobSearchContext['index_query'];
}) {
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="right"
                className="w-full overflow-y-auto border-zinc-200 bg-zinc-50 p-0 sm:max-w-184 dark:border-zinc-800 dark:bg-zinc-950"
            >
                {job && <SummaryPanel job={job} searchQuery={searchQuery} />}
            </SheetContent>
        </Sheet>
    );
}

function SummaryPanel({
    job,
    searchQuery,
}: {
    job: JobResultItem;
    searchQuery: JobSearchContext['index_query'];
}) {
    const getInitials = useInitials();
    const salary = formatSalaryRange(job.salary);
    const applyUrl = job.application_url ?? job.company.website;
    const isApplyDisabled = applyUrl === null;

    return (
        <div className="bg-zinc-50 dark:bg-zinc-950">
            <div className="mx-auto max-w-3xl space-y-8 p-6 lg:p-10">
                <div className="space-y-3">
                    <span className="text-[11px] font-semibold tracking-[0.3em] text-primary uppercase dark:text-accent-foreground">
                        Job Overview
                    </span>
                    <div className="space-y-3">
                        <h1 className="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                            <HighlightedText text={job.title} highlight={job.highlight.title} />
                        </h1>
                        <div className="flex items-center gap-3">
                            <div className="flex size-11 items-center justify-center bg-zinc-900 text-sm font-semibold text-white dark:bg-zinc-100 dark:text-zinc-950">
                                {getInitials(job.company.name ?? 'Job')}
                            </div>
                            <div>
                                <p className="font-medium text-zinc-800 dark:text-zinc-100">
                                    {job.company.name ?? 'Unknown company'}
                                </p>
                                <p className="text-sm tracking-wide text-zinc-400 uppercase dark:text-zinc-500">
                                    {job.primary_location ?? 'Location flexible'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-px bg-zinc-200 sm:grid-cols-2 dark:bg-zinc-800">
                    {[
                        {
                            label: 'Salary',
                            value: salary,
                        },
                        {
                            label: 'Work Model',
                            value: formatWorkModelLabel(job.work_model),
                        },
                        {
                            label: 'Experience',
                            value: formatExperienceLevelLabel(job.experience_level),
                        },
                        {
                            label: 'Published',
                            value: formatDisplayDate(job.published_at),
                        },
                    ].map((metric) => (
                        <div
                            key={metric.label}
                            className="space-y-2 bg-white p-6 dark:bg-zinc-900"
                        >
                            <span className={sectionLabelClassName}>
                                {metric.label}
                            </span>
                            <p className="text-xl font-medium tracking-tight text-zinc-900 dark:text-zinc-50">
                                {metric.value}
                            </p>
                        </div>
                    ))}
                </div>

                <section className="space-y-4">
                    <h2 className="text-xs font-semibold tracking-[0.24em] text-zinc-900 uppercase dark:text-zinc-100">
                        Matched Highlight
                    </h2>
                    <p className="max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                        {job.highlight.description
                            ? (
                                <HighlightedText
                                    text={job.highlight.description}
                                    highlight={job.highlight.description}
                                />
                            )
                            : <span className="line-clamp-4 block">{job.description}</span>}
                    </p>
                </section>

                <section className="space-y-4">
                    <h2 className="text-xs font-semibold tracking-[0.24em] text-zinc-900 uppercase dark:text-zinc-100">
                        Core Proficiencies
                    </h2>
                    <div className="flex flex-wrap gap-2">
                        {job.skills.map((skill) => (
                            <Badge
                                key={skill}
                                variant="outline"
                                className="rounded-none border-zinc-200 bg-white px-3 py-2 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300"
                            >
                                {skill}
                            </Badge>
                        ))}
                    </div>
                </section>

                <section className="space-y-4">
                    <h2 className="text-xs font-semibold tracking-[0.24em] text-zinc-900 uppercase dark:text-zinc-100">
                        Search Context
                    </h2>
                    <div className="grid gap-px bg-zinc-200 sm:grid-cols-2 dark:bg-zinc-800">
                        <div className="space-y-2 bg-white p-5 dark:bg-zinc-900">
                            <span className={sectionLabelClassName}>Company</span>
                            <p className="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {job.company.name ?? 'Unknown company'}
                            </p>
                        </div>
                        <div className="space-y-2 bg-white p-5 dark:bg-zinc-900">
                            <span className={sectionLabelClassName}>Primary Location</span>
                            <p className="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {job.primary_location ?? 'Unknown'}
                            </p>
                        </div>
                    </div>
                </section>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <Button
                        asChild={! isApplyDisabled}
                        disabled={isApplyDisabled}
                        className="h-auto rounded-none bg-primary px-8 py-4 text-[11px] font-semibold tracking-[0.28em] text-primary-foreground uppercase shadow-none hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        {applyUrl ? (
                            <a href={applyUrl} target="_blank" rel="noreferrer">
                                Apply For Position
                            </a>
                        ) : (
                            <span>Apply For Position</span>
                        )}
                    </Button>
                    <Button
                        asChild
                        variant="ghost"
                        className="h-auto rounded-none px-0 py-0 text-[11px] font-semibold tracking-[0.28em] text-zinc-400 uppercase shadow-none hover:bg-transparent hover:text-primary dark:text-zinc-500 dark:hover:text-accent-foreground"
                    >
                        <Link href={jobsShow(job.slug, { query: searchQuery })}>
                            View Full Details
                            <ArrowRight className="size-4" />
                        </Link>
                    </Button>
                </div>

                <div className="space-y-4 bg-white p-3 dark:bg-zinc-900">
                    <div className="flex h-48 items-end justify-between bg-linear-to-br from-accent to-background px-5 py-5 dark:from-accent/30 dark:to-card">
                        <div className="space-y-2">
                            <p className={sectionLabelClassName}>
                                Office Location
                            </p>
                            <p className="max-w-xs text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                                {job.locations[0] ?? job.primary_location ?? 'Remote'}
                            </p>
                        </div>
                        <MapPin className="size-8 text-primary dark:text-accent-foreground" />
                    </div>
                </div>
            </div>
        </div>
    );
}
