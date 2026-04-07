import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BriefcaseBusiness,
    Building2,
    Clock3,
    ExternalLink,
    MapPin,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DetailSection,
    MetadataRow,
    sectionLabelClassName,
} from '@/features/jobs/components/shared';
import type { SearchResult } from '@/features/jobs/data/mock-search-data';
import { searchResults } from '@/features/jobs/data/mock-search-data';
import { useInitials } from '@/hooks/use-initials';
import { index as jobsIndex, show as jobsShow } from '@/routes/jobs';

export function JobShowContent({ job }: { job: SearchResult }) {
    const getInitials = useInitials();

    return (
        <div className="bg-white dark:bg-zinc-950">
            <header className="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
                <div className="mx-auto flex w-full max-w-420 flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 xl:flex-row xl:items-center xl:justify-between xl:py-8">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-black tracking-tight text-zinc-950 dark:text-zinc-50">
                            {job.title}
                        </h1>
                        <div className="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <span className="font-semibold text-zinc-950 dark:text-zinc-100">
                                {job.company}
                            </span>
                            <span className="text-zinc-300 dark:text-zinc-700">
                                •
                            </span>
                            <span>{job.location}</span>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Button
                            variant="outline"
                            className="h-11 rounded-none border-zinc-200 px-5 text-[10px] font-bold tracking-[0.24em] uppercase shadow-none hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-900"
                        >
                            Save Job
                        </Button>
                        <Button className="h-11 rounded-none bg-primary px-6 text-[10px] font-bold tracking-[0.24em] text-primary-foreground uppercase shadow-none hover:bg-primary/90">
                            Apply Now
                        </Button>
                    </div>
                </div>
            </header>

            <div className="mx-auto flex w-full max-w-420 flex-col md:flex-row xl:gap-0">
                <article className="flex-1 px-4 py-8 sm:px-6 lg:px-10 lg:py-12 xl:px-12 xl:py-14">
                    <div className="w-full space-y-14">
                        <Button
                            asChild
                            variant="ghost"
                            className="h-auto rounded-none px-0 py-0 text-[11px] font-bold tracking-[0.26em] text-zinc-400 uppercase shadow-none hover:bg-transparent hover:text-primary dark:text-zinc-500 dark:hover:text-accent-foreground"
                        >
                            <Link
                                href={jobsIndex()}
                                className="inline-flex items-center gap-2"
                            >
                                <ArrowLeft className="size-3.5" />
                                Back To Search Results
                            </Link>
                        </Button>

                        <section className="flex flex-col gap-4 border-l-4 border-primary bg-secondary px-5 py-5 lg:flex-row lg:items-center lg:justify-between dark:bg-accent/30">
                            <div className="space-y-2">
                                <p className="text-[10px] font-black tracking-[0.26em] text-primary uppercase dark:text-accent-foreground">
                                    Market Benchmark
                                </p>
                                <p className="max-w-2xl text-sm leading-7 text-secondary-foreground dark:text-foreground">
                                    {job.benchmark}
                                </p>
                            </div>
                            <Button className="h-10 rounded-none bg-primary px-5 text-[10px] font-bold tracking-[0.24em] text-primary-foreground uppercase shadow-none hover:bg-primary/90">
                                View Report
                            </Button>
                        </section>

                        <DetailSection title="Job Overview">
                            <p className="max-w-3xl text-base leading-8 text-zinc-600 dark:text-zinc-300">
                                {job.overview}
                            </p>
                        </DetailSection>

                        <DetailSection title="Key Responsibilities">
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
                        </DetailSection>

                        <DetailSection title="Technical Requirements">
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
                        </DetailSection>

                        <DetailSection title="Related Opportunities">
                            <div className="grid gap-6 md:grid-cols-2">
                                {searchResults
                                    .filter(
                                        (candidate) => candidate.id !== job.id,
                                    )
                                    .slice(0, 2)
                                    .map((candidate) => (
                                        <Button
                                            key={candidate.id}
                                            asChild
                                            variant="ghost"
                                            className="h-auto rounded-none border border-zinc-200 bg-white p-6 text-left shadow-none hover:border-primary hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:bg-zinc-900"
                                        >
                                            <Link href={jobsShow(candidate.id)}>
                                                <div className="space-y-5">
                                                    <p
                                                        className={
                                                            sectionLabelClassName
                                                        }
                                                    >
                                                        {candidate.company}
                                                    </p>
                                                    <h3 className="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                                                        {candidate.title}
                                                    </h3>
                                                    <div className="flex flex-wrap items-center gap-3">
                                                        <span className="border border-zinc-200 bg-zinc-50 px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-zinc-700 uppercase dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                                                            {candidate.salary}
                                                        </span>
                                                        <span className="text-[11px] font-semibold tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                                                            {
                                                                candidate.workModel
                                                            }{' '}
                                                            /{' '}
                                                            {candidate.location}
                                                        </span>
                                                    </div>
                                                </div>
                                            </Link>
                                        </Button>
                                    ))}
                            </div>
                        </DetailSection>
                    </div>
                </article>

                <aside className="w-full max-w-88 border-t border-zinc-200 bg-white px-4 py-8 sm:px-6 xl:border-t-0 xl:border-l xl:px-6 xl:py-8 dark:border-zinc-800 dark:bg-zinc-950">
                    <div className="space-y-10 xl:sticky xl:top-24">
                        <section className="space-y-6">
                            <div className="flex size-16 items-center justify-center border border-zinc-200 bg-white text-xl font-semibold tracking-tight text-primary dark:border-zinc-800 dark:bg-zinc-950 dark:text-accent-foreground">
                                {getInitials(job.company)}.
                            </div>
                            <div className="space-y-3">
                                <h2 className="text-xl font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                                    {job.company}
                                </h2>
                                <p className="text-sm leading-7 text-zinc-500 dark:text-zinc-400">
                                    {job.companySummary}
                                </p>
                            </div>
                            <div className="space-y-4 text-[11px] font-semibold tracking-[0.22em] text-zinc-500 uppercase dark:text-zinc-400">
                                <MetadataRow
                                    icon={<Building2 className="size-4" />}
                                    text={job.companyMeta}
                                />
                                <MetadataRow
                                    icon={<MapPin className="size-4" />}
                                    text={`${job.officeLocation} • ${job.location}`}
                                />
                                <MetadataRow
                                    icon={
                                        <BriefcaseBusiness className="size-4" />
                                    }
                                    text={job.workType}
                                />
                                <MetadataRow
                                    icon={<Clock3 className="size-4" />}
                                    text={`Posted ${job.postedAt}`}
                                />
                                <MetadataRow
                                    icon={<ExternalLink className="size-4" />}
                                    text="metropolis-design.com"
                                />
                            </div>
                        </section>

                        <section className="space-y-5">
                            <h3 className={sectionLabelClassName}>
                                Job Specification
                            </h3>
                            <div className="space-y-5">
                                {job.summaryMetrics.map((metric) => (
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
                                    Midtown West, 42nd St campus. Direct access
                                    to Grand Central and Hudson Yards corridors.
                                </p>
                            </div>
                            <Button className="h-12 w-full rounded-none bg-primary px-6 text-[11px] font-bold tracking-[0.28em] text-primary-foreground uppercase shadow-none hover:bg-primary/90">
                                Apply For This Job
                            </Button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    );
}
