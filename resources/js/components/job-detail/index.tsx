import { Link } from '@inertiajs/react';
import {
    BriefcaseBusiness,
    Building2,
    Clock3,
    ExternalLink,
    MapPin,
} from 'lucide-react';
import { searchResults } from '@/components/search/mock-search-data';
import type { SearchResult } from '@/components/search/mock-search-data';
import {
    DetailSection,
    getCompanyMark,
    MetadataRow,
    sectionLabelClassName,
} from '@/components/search/shared';
import { Button } from '@/components/ui/button';

export function JobDetailContent({ job }: { job: SearchResult }) {
    return (
        <div className="grid bg-white xl:grid-cols-[minmax(0,1fr)_24rem] dark:bg-zinc-950">
            <article className="px-4 py-6 sm:px-6 lg:px-12 lg:py-10">
                <div className="mx-auto max-w-4xl space-y-12">
                    <Button
                        asChild
                        variant="ghost"
                        className="h-auto rounded-none px-0 py-0 text-[11px] font-semibold tracking-[0.26em] text-zinc-400 uppercase shadow-none hover:bg-transparent hover:text-indigo-700 dark:text-zinc-500 dark:hover:text-indigo-300"
                    >
                        <Link href="/search">Back To Search Results</Link>
                    </Button>

                    <div className="space-y-4">
                        <h1 className="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                            {job.title}
                        </h1>
                        <div className="flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                            <span className="font-medium text-zinc-900 dark:text-zinc-100">
                                {job.company}
                            </span>
                            <span className="text-zinc-300 dark:text-zinc-700">
                                •
                            </span>
                            <span>{job.location}</span>
                        </div>
                    </div>

                    <section className="flex flex-col gap-4 bg-indigo-50 px-5 py-5 ring-1 ring-indigo-100 lg:flex-row lg:items-center lg:justify-between dark:bg-indigo-500/10 dark:ring-indigo-500/20">
                        <div className="space-y-2">
                            <p className="text-[10px] font-semibold tracking-[0.28em] text-indigo-700 uppercase dark:text-indigo-300">
                                Market Benchmark
                            </p>
                            <p className="max-w-2xl text-sm leading-7 text-indigo-950 dark:text-indigo-100">
                                {job.benchmark}
                            </p>
                        </div>
                        <Button className="h-auto rounded-none bg-indigo-700 px-5 py-3 text-[11px] font-semibold tracking-[0.24em] text-white uppercase shadow-none hover:bg-indigo-800">
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
                                    <span className="text-lg leading-none font-semibold text-indigo-700 dark:text-indigo-300">
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
                                    className="space-y-2 bg-white p-6 dark:bg-zinc-900"
                                >
                                    <p className="text-[10px] font-semibold tracking-[0.28em] text-indigo-700 uppercase dark:text-indigo-300">
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
                                .filter((candidate) => candidate.id !== job.id)
                                .slice(0, 2)
                                .map((candidate) => (
                                    <Button
                                        key={candidate.id}
                                        asChild
                                        variant="ghost"
                                        className="h-auto rounded-none bg-zinc-50 p-6 text-left shadow-none hover:bg-zinc-100 dark:bg-zinc-900 dark:hover:bg-zinc-800"
                                    >
                                        <Link
                                            href={`/search/jobs/${candidate.id}`}
                                        >
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
                                                    <span className="bg-white px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-zinc-700 uppercase dark:bg-zinc-950 dark:text-zinc-300">
                                                        {candidate.salary}
                                                    </span>
                                                    <span className="text-[11px] font-semibold tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                                                        {candidate.workModel} /{' '}
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

            <aside className="border-t border-zinc-200 bg-zinc-50 px-4 py-6 sm:px-6 xl:border-t-0 xl:border-l xl:border-zinc-200 xl:px-8 xl:py-10 dark:border-zinc-800 dark:bg-zinc-900/60">
                <div className="space-y-10 xl:sticky xl:top-6">
                    <section className="space-y-5">
                        <div className="flex size-16 items-center justify-center bg-white text-xl font-semibold tracking-tight text-indigo-700 ring-1 ring-zinc-200 dark:bg-zinc-950 dark:text-indigo-300 dark:ring-zinc-800">
                            {getCompanyMark(job.company)}.
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
                                icon={<BriefcaseBusiness className="size-4" />}
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
                                <div key={metric.label} className="space-y-1">
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
                        <div className="flex h-48 items-end justify-start bg-[linear-gradient(180deg,rgba(82,82,91,0.2),rgba(24,24,27,0.05))] px-4 py-4 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.02))]">
                            <p className="max-w-[14rem] text-xs leading-6 text-zinc-700 dark:text-zinc-300">
                                Midtown West, 42nd St campus. Direct access to
                                Grand Central and Hudson Yards corridors.
                            </p>
                        </div>
                        <Button className="h-auto w-full rounded-none bg-indigo-700 px-6 py-4 text-[11px] font-semibold tracking-[0.28em] text-white uppercase shadow-none hover:bg-indigo-800">
                            Apply For This Job
                        </Button>
                    </div>
                </div>
            </aside>
        </div>
    );
}
