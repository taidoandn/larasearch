import {
    Bookmark,
    BriefcaseBusiness,
    CircleDot,
    Clock3,
    TrendingUp,
    WalletCards,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { CompanyIdentity } from '@/features/jobs/components/shared';
import type { JobDetailItem } from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from '@/features/jobs/utils';

export function Header({ job }: { job: JobDetailItem }) {
    const primaryLocation = job.primary_location ?? job.locations[0] ?? 'Remote';
    const applyUrl = job.application_url ?? job.company.website;
    const chips = [
        {
            icon: WalletCards,
            label: formatSalaryRange(job.salary),
            className: 'bg-blue-50 text-primary',
        },
        {
            icon: BriefcaseBusiness,
            label: job.work_model_label ?? formatWorkModelLabel(job.work_model, 'Flexible'),
            className: 'bg-slate-100 text-slate-600',
        },
        {
            icon: TrendingUp,
            label:
                job.experience_level_label ??
                formatExperienceLevelLabel(
                    job.summary_metrics.find((metric) => metric.label === 'Experience Required')
                        ?.value ?? null,
                    'Not specified',
                ),
            className: 'bg-slate-100 text-slate-600',
        },
        {
            icon: CircleDot,
            label: job.job_type_label ?? formatJobTypeLabel(job.job_type, 'Not specified'),
            className: 'bg-slate-100 text-slate-600',
        },
        {
            icon: Clock3,
            label: formatDisplayDate(job.published_at),
            className: 'bg-slate-100 text-slate-600',
        },
    ];

    return (
        <section className="relative overflow-hidden rounded-4xl bg-white px-6 py-8 shadow-[0_16px_42px_-28px_rgba(0,74,198,0.16)] sm:px-8 sm:py-10 lg:px-10 lg:py-12">
            <div className="absolute right-0 bottom-0 h-56 w-56 rounded-full bg-primary/6 blur-3xl" />
            <div className="absolute top-6 right-10 h-20 w-20 rounded-full bg-primary/8 blur-2xl" />

            <div className="relative flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                <div className="flex min-w-0 flex-1 flex-col gap-8">
                    <CompanyIdentity
                        name={job.company.name}
                        logoUrl={job.company.logo_url}
                        verified={job.company.is_verified}
                        meta={[primaryLocation, job.company.industry].filter(Boolean).join(' • ')}
                    />

                    <div className="flex flex-col gap-5">
                        <h1 className="max-w-4xl font-display text-4xl leading-[0.96] font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-[4rem]">
                            {job.title}
                        </h1>

                        <div className="flex flex-wrap gap-3">
                            {chips.map((chip) => (
                                <span
                                    key={chip.label}
                                    className={`inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold ${chip.className}`}
                                >
                                    <chip.icon className="size-4" />
                                    {chip.label}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="flex w-full flex-col gap-3 lg:w-72 lg:shrink-0">
                    <Button
                        asChild={Boolean(applyUrl)}
                        disabled={!applyUrl}
                        className="h-14 rounded-2xl bg-[linear-gradient(135deg,#2563eb_0%,#1d4ed8_100%)] px-8 text-base font-bold text-white shadow-[0_24px_42px_-28px_rgba(37,99,235,0.72)] hover:opacity-95"
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
                        type="button"
                        variant="outline"
                        className="h-13 rounded-2xl border-slate-200 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50"
                    >
                        <Bookmark data-icon="inline-start" />
                        Save for later
                    </Button>
                </div>
            </div>
        </section>
    );
}
