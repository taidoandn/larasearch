import { Link } from '@inertiajs/react';
import { ArrowRight, MapPin } from 'lucide-react';
import type { SearchResult } from '@/components/search/mock-search-data';
import { sectionLabelClassName } from '@/components/search/shared';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent } from '@/components/ui/sheet';
import { useInitials } from '@/hooks/use-initials';

export function SearchSummarySheet({
    job,
    open,
    onOpenChange,
}: {
    job: SearchResult | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="right"
                className="w-full overflow-y-auto border-zinc-200 bg-zinc-50 p-0 sm:max-w-[46rem] dark:border-zinc-800 dark:bg-zinc-950"
            >
                {job && <SummaryPanel job={job} />}
            </SheetContent>
        </Sheet>
    );
}

function SummaryPanel({ job }: { job: SearchResult }) {
    const getInitials = useInitials();

    return (
        <div className="bg-zinc-50 dark:bg-zinc-950">
            <div className="mx-auto max-w-3xl space-y-8 p-6 lg:p-10">
                <div className="space-y-3">
                    <span className="text-[11px] font-semibold tracking-[0.3em] text-primary uppercase dark:text-accent-foreground">
                        Job Overview
                    </span>
                    <div className="space-y-3">
                        <h1 className="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                            {job.title}
                        </h1>
                        <div className="flex items-center gap-3">
                            <div className="flex size-11 items-center justify-center bg-zinc-900 text-sm font-semibold text-white dark:bg-zinc-100 dark:text-zinc-950">
                                {getInitials(job.company)}
                            </div>
                            <div>
                                <p className="font-medium text-zinc-800 dark:text-zinc-100">
                                    {job.company}
                                </p>
                                <p className="text-sm tracking-wide text-zinc-400 uppercase dark:text-zinc-500">
                                    {job.companyMeta}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-px bg-zinc-200 sm:grid-cols-2 dark:bg-zinc-800">
                    {job.summaryMetrics.map((metric) => (
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
                        The Role
                    </h2>
                    <p className="max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                        {job.overview}
                    </p>
                </section>

                <section className="space-y-4">
                    <h2 className="text-xs font-semibold tracking-[0.24em] text-zinc-900 uppercase dark:text-zinc-100">
                        Core Proficiencies
                    </h2>
                    <div className="flex flex-wrap gap-2">
                        {job.skills.map((skill) => (
                            <span
                                key={skill}
                                className="bg-white px-3 py-2 text-xs text-zinc-600 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-800"
                            >
                                {skill}
                            </span>
                        ))}
                    </div>
                </section>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <Button className="h-auto rounded-none bg-primary px-8 py-4 text-[11px] font-semibold tracking-[0.28em] text-primary-foreground uppercase shadow-none hover:bg-primary/90">
                        Apply For Position
                    </Button>
                    <Button
                        asChild
                        variant="ghost"
                        className="h-auto rounded-none px-0 py-0 text-[11px] font-semibold tracking-[0.28em] text-zinc-400 uppercase shadow-none hover:bg-transparent hover:text-primary dark:text-zinc-500 dark:hover:text-accent-foreground"
                    >
                        <Link href={`/search/jobs/${job.id}`}>
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
                                {job.mapLabel}
                            </p>
                        </div>
                        <MapPin className="size-8 text-primary dark:text-accent-foreground" />
                    </div>
                </div>
            </div>
        </div>
    );
}
