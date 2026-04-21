import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetFooter } from '@/components/ui/sheet';
import { HighlightedText, JobSummaryPanel } from '@/features/jobs/components/shared';
import type { JobResultItem, JobSearchContext } from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from '@/features/jobs/utils';
import { show as jobsShow } from '@/routes/jobs';

export function SummarySheet({
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
                className="w-full border-zinc-200 bg-white p-0 sm:max-w-120 lg:max-w-lg"
            >
                {job ? <SummaryPanel job={job} searchQuery={searchQuery} /> : null}
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
    const salary = formatSalaryRange(job.salary);
    const location = job.primary_location ?? job.locations[0] ?? 'Location flexible';
    const applyUrl = job.application_url ?? job.company.website;
    const detailUrl = jobsShow(job.slug, { query: searchQuery });

    return (
        <>
            <div className="flex-1 overflow-y-auto bg-white px-4 py-4 sm:px-5 sm:py-5">
                <JobSummaryPanel
                    companyName={job.company.name ?? 'Unknown company'}
                    companyLogoUrl={job.company.logo_url}
                    location={location}
                    title={job.title}
                    titleHighlight={job.highlight.title}
                    chips={[
                        {
                            label: salary,
                            type: 'salary',
                            emphasis: 'primary',
                        },
                        {
                            label: job.work_model_label ?? formatWorkModelLabel(job.work_model),
                            type: 'work-model',
                        },
                        {
                            label:
                                job.experience_level_label ??
                                formatExperienceLevelLabel(job.experience_level),
                            type: 'experience',
                        },
                        {
                            label: job.job_type_label ?? formatJobTypeLabel(job.job_type),
                            type: 'job-type',
                        },
                        {
                            label: formatDisplayDate(job.published_at),
                            type: 'published-at',
                        },
                    ]}
                    skills={job.skills}
                    highlightedSummary={
                        job.highlight.description ? (
                            <HighlightedText
                                text={job.highlight.description}
                                highlight={job.highlight.description}
                            />
                        ) : (
                            <span className="line-clamp-4 block">{job.description}</span>
                        )
                    }
                    mapLabel={job.locations[0] ?? location}
                    contextLabel="Search Context"
                    contextValue={[job.company.name ?? 'Unknown company', location].join(' • ')}
                    secondaryAction={{
                        label: 'Save for later',
                    }}
                />
            </div>

            <SheetFooter className="border-t border-zinc-200 bg-white px-4 py-4 sm:px-5">
                <Button
                    asChild={Boolean(applyUrl)}
                    disabled={!applyUrl}
                    className="h-12 w-full rounded-full bg-[linear-gradient(135deg,#2563eb_0%,#1d4ed8_100%)] text-sm font-semibold text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.7)] hover:opacity-95"
                >
                    {applyUrl ? (
                        <a href={applyUrl} target="_blank" rel="noreferrer">
                            Apply Now
                        </a>
                    ) : (
                        <span>Apply Now</span>
                    )}
                </Button>

                <Button
                    asChild
                    variant="outline"
                    className="h-12 w-full rounded-full border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    <Link href={detailUrl}>
                        Full position details
                        <ArrowRight className="size-4" />
                    </Link>
                </Button>
            </SheetFooter>
        </>
    );
}
