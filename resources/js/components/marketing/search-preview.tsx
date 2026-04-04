import { Bookmark, ChevronRight, SlidersHorizontal } from 'lucide-react';
import {
    MarketingSection,
    SectionEyebrow,
} from '@/components/marketing/section-shell';
import {
    activeFilterChips,
    searchResults,
} from '@/components/search/mock-search-data';
import { LedgerMetric } from '@/components/search/shared';
import { cn } from '@/lib/utils';

const previewResults = searchResults.slice(0, 3);
const previewFilters = [
    { label: 'Keyword', value: 'Senior Laravel' },
    { label: 'Location', value: 'Da Nang' },
    { label: 'Experience', value: '5+ years' },
    { label: 'Type', value: 'Last 30 days' },
] as const;

export function SearchPreviewSection() {
    return (
        <MarketingSection
            id="preview"
            className="px-6 py-8 sm:px-8 lg:px-10 lg:py-10"
        >
            <div className="space-y-4">
                <SectionEyebrow>Search Preview</SectionEyebrow>

                <div className="grid gap-4 bg-zinc-100/70 p-3 lg:grid-cols-[168px_1fr] dark:bg-zinc-900/60">
                    <aside className="bg-zinc-50 px-4 py-4 dark:bg-zinc-950">
                        <div className="space-y-6">
                            <div className="space-y-3">
                                <SectionEyebrow>Filters</SectionEyebrow>
                                <div className="space-y-3">
                                    {previewFilters.map((filter) => (
                                        <div
                                            key={filter.label}
                                            className="space-y-1"
                                        >
                                            <p className="text-xs font-semibold tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                                                {filter.label}
                                            </p>
                                            <p className="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                {filter.value}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="space-y-3">
                                <SectionEyebrow>Signals</SectionEyebrow>
                                <div className="flex flex-wrap gap-2">
                                    {activeFilterChips.map((chip) => (
                                        <span
                                            key={chip.id}
                                            className="bg-white px-2 py-1 text-xs font-medium tracking-[0.16em] text-zinc-600 uppercase dark:bg-zinc-900 dark:text-zinc-300"
                                        >
                                            {chip.value}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </aside>

                    <div className="bg-white dark:bg-zinc-950">
                        <div className="flex items-center justify-between px-4 py-3">
                            <h2 className="text-base font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                                12 jobs in Da Nang
                            </h2>
                            <p className="font-mono text-xs tracking-[0.22em] text-zinc-400 uppercase dark:text-zinc-500">
                                sort by relevance
                            </p>
                        </div>

                        <div className="flex flex-col gap-2 bg-zinc-50 px-4 py-2 sm:flex-row sm:items-center sm:justify-between dark:bg-zinc-900/50">
                            <p className="font-mono text-xs tracking-[0.22em] text-zinc-400 uppercase dark:text-zinc-500">
                                Displaying 142 matches / indexed 0.04s
                            </p>

                            <div className="flex items-center gap-2 self-start sm:self-auto">
                                <SlidersHorizontal className="size-3 text-zinc-400 dark:text-zinc-500" />
                                <span className="text-xs font-semibold tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                                    Precision Match
                                </span>
                            </div>
                        </div>

                        <div className="space-y-px bg-zinc-100 dark:bg-zinc-900">
                            {previewResults.map((job) => (
                                <article
                                    key={job.id}
                                    className={cn(
                                        'grid gap-3 bg-white px-4 py-3 md:grid-cols-12 md:items-center dark:bg-zinc-950',
                                    )}
                                >
                                    <div className="md:col-span-6">
                                        <div className="flex items-start gap-2">
                                            <h3 className="text-sm font-semibold tracking-tight text-zinc-950 dark:text-zinc-50">
                                                {job.title}
                                            </h3>
                                            {job.isNew ? (
                                                <span className="bg-emerald-100 px-1.5 py-0.5 text-[7px] font-bold tracking-[0.18em] text-emerald-700 uppercase dark:bg-emerald-500/15 dark:text-emerald-300">
                                                    New
                                                </span>
                                            ) : null}
                                        </div>
                                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                            {job.company}{' '}
                                            <span className="mx-1 text-zinc-300 dark:text-zinc-700">
                                                •
                                            </span>{' '}
                                            {job.workModel}, {job.location}
                                        </p>
                                    </div>

                                    <div className="flex gap-6 md:col-span-4">
                                        <LedgerMetric
                                            label="Comp"
                                            value={job.salary}
                                        />
                                        <LedgerMetric
                                            label="Type"
                                            value={job.workModel}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between gap-3 md:col-span-2 md:justify-end">
                                        <LedgerMetric
                                            align="right"
                                            label="Added"
                                            value={job.postedAt}
                                        />
                                        <Bookmark
                                            className={cn(
                                                'size-4',
                                                job.isSaved
                                                    ? 'fill-zinc-500 text-zinc-500 dark:fill-zinc-300 dark:text-zinc-300'
                                                    : 'text-zinc-300 dark:text-zinc-600',
                                            )}
                                        />
                                        <ChevronRight className="size-4 text-zinc-300 dark:text-zinc-600" />
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </MarketingSection>
    );
}
