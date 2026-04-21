import { ExternalLink, MapPin } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { CompanyAvatar } from '@/features/jobs/components/shared';
import type { JobDetailItem } from '@/features/jobs/types';

export function Sidebar({ job }: { job: JobDetailItem }) {
    const primaryLocation = job.primary_location ?? job.locations[0] ?? 'Remote';
    const applyUrl = job.application_url ?? job.company.website;

    return (
        <aside className="flex flex-col gap-6 lg:sticky lg:top-28">
            <section className="overflow-hidden rounded-[1.75rem] bg-white px-7 py-7 shadow-[0_16px_36px_-30px_rgba(0,74,198,0.16)]">
                <h3 className="text-sm font-bold tracking-[0.22em] text-slate-500 uppercase">
                    About the Company
                </h3>

                <div className="mt-8 flex flex-col gap-8">
                    <div className="flex items-center gap-4">
                        <CompanyAvatar name={job.company.name} logoUrl={job.company.logo_url} />
                        <div className="min-w-0">
                            <p className="truncate text-lg font-bold text-slate-950">
                                {job.company.name}
                            </p>
                            <p className="text-sm font-bold text-primary">
                                {job.company.industry ?? 'Technology'}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-col gap-4 border-t border-slate-200/70 pt-5">
                        <CompanyStat
                            label="Industry"
                            value={job.company.industry ?? 'Technology'}
                        />
                        <CompanyStat
                            label="Headcount"
                            value={job.company.company_size ?? 'Growing team'}
                        />
                        <CompanyStat
                            label="Status"
                            value={job.company.is_verified ? 'Hiring Active' : 'Actively Hiring'}
                            accent
                        />
                    </div>

                    <div className="rounded-[1.25rem] bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-600">
                        {job.company.summary ||
                            'The team is scaling carefully and hiring engineers who want to own platform quality.'}
                    </div>

                    <div className="relative h-40 overflow-hidden rounded-[1.25rem] border border-slate-200 bg-[linear-gradient(180deg,#dbeafe_0%,#eff6ff_32%,#e2e8f0_100%)]">
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(37,99,235,0.18),transparent_46%),radial-gradient(circle_at_72%_66%,rgba(148,163,184,0.18),transparent_34%)]" />
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="relative">
                                <div className="absolute -inset-3 rounded-full bg-primary/18 blur-md" />
                                <div className="relative flex size-5 items-center justify-center rounded-full border-2 border-white bg-primary shadow-lg" />
                            </div>
                        </div>
                        <div className="absolute right-4 bottom-4 left-4 flex items-end justify-between">
                            <p className="max-w-48 text-sm leading-6 font-medium text-slate-700">
                                {primaryLocation}
                            </p>
                            <div className="flex size-10 items-center justify-center rounded-full bg-white text-primary shadow-lg">
                                <MapPin className="size-4" />
                            </div>
                        </div>
                    </div>

                    <Button
                        asChild={Boolean(applyUrl)}
                        disabled={!applyUrl}
                        className="h-14 rounded-2xl bg-[linear-gradient(135deg,#2563eb_0%,#1d4ed8_100%)] text-base font-extrabold text-white shadow-[0_24px_42px_-28px_rgba(37,99,235,0.72)] hover:opacity-95"
                    >
                        {applyUrl ? (
                            <a href={applyUrl} target="_blank" rel="noreferrer">
                                Apply for this Job
                            </a>
                        ) : (
                            <span>Apply for this Job</span>
                        )}
                    </Button>

                    {job.company.website ? (
                        <Button
                            asChild
                            variant="ghost"
                            className="h-auto justify-center rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-primary"
                        >
                            <a href={job.company.website} target="_blank" rel="noreferrer">
                                Visit company website
                                <ExternalLink data-icon="inline-end" />
                            </a>
                        </Button>
                    ) : null}
                </div>
            </section>

            <section className="rounded-[1.75rem] bg-slate-50 px-7 py-7 shadow-[inset_0_0_0_1px_rgba(148,163,184,0.14)]">
                <h3 className="text-base font-bold text-slate-950">Role Impact</h3>
                <p className="mt-4 text-sm leading-7 text-slate-600">
                    You&apos;ll help keep critical services stable while improving the systems,
                    tooling, and operating habits that support the broader engineering team.
                </p>
            </section>
        </aside>
    );
}

function CompanyStat({
    label,
    value,
    accent = false,
}: {
    label: string;
    value: string;
    accent?: boolean;
}) {
    return (
        <div className="flex items-center justify-between gap-4 text-sm">
            <span className="font-medium text-slate-500">{label}</span>
            {accent ? (
                <span className="rounded-md bg-emerald-100 px-2 py-1 text-[10px] font-bold tracking-[0.18em] text-emerald-700 uppercase">
                    {value}
                </span>
            ) : (
                <span className="text-right font-bold text-slate-950">{value}</span>
            )}
        </div>
    );
}
