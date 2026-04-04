import { Bookmark, ChevronRight } from 'lucide-react';
import { searchResults } from '@/components/search/mock-search-data';
import type { SearchResult } from '@/components/search/mock-search-data';
import { LedgerMetric } from '@/components/search/shared';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export function ResultsList({
    selectedJobId,
    onSelectJob,
}: {
    selectedJobId: string | null;
    onSelectJob: (jobId: string) => void;
}) {
    return (
        <div className="bg-white dark:bg-zinc-950">
            {searchResults.map((job) => (
                <ResultRow
                    key={job.id}
                    job={job}
                    isSelected={job.id === selectedJobId}
                    onSelect={() => onSelectJob(job.id)}
                />
            ))}

            <div className="flex flex-col items-center gap-3 px-4 py-8 sm:px-6 sm:py-10">
                <Button
                    variant="outline"
                    className="rounded-none border-zinc-200 bg-white px-8 py-3 text-[11px] font-semibold tracking-[0.28em] text-zinc-900 uppercase shadow-none hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                >
                    Load Next Page
                </Button>
                <p className="font-mono text-[10px] tracking-[0.28em] text-zinc-400 uppercase dark:text-zinc-500">
                    Showing 15 of 142 records
                </p>
            </div>
        </div>
    );
}

function ResultRow({
    job,
    isSelected,
    onSelect,
}: {
    job: SearchResult;
    isSelected: boolean;
    onSelect: () => void;
}) {
    return (
        <Button
            variant="ghost"
            onClick={onSelect}
            className={cn(
                'relative grid h-auto w-full rounded-none border-b border-l-4 border-zinc-100 px-4 py-4 text-left shadow-none sm:px-6 md:grid-cols-12 md:items-center',
                'justify-start whitespace-normal hover:bg-zinc-50 dark:border-zinc-900 dark:hover:bg-zinc-900/70',
                isSelected
                    ? 'border-l-primary bg-accent dark:border-l-primary dark:bg-accent/30'
                    : 'border-l-transparent bg-white dark:bg-zinc-950',
            )}
        >
            <div className="md:col-span-5">
                <div className="flex items-start gap-2">
                    <h2 className="text-sm font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                        {job.title}
                    </h2>
                    {job.isNew && (
                        <span className="bg-accent px-1.5 py-0.5 text-[9px] font-bold tracking-[0.18em] text-primary uppercase dark:bg-accent/30 dark:text-accent-foreground">
                            New
                        </span>
                    )}
                </div>

                <p className="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    {job.company}{' '}
                    <span className="mx-1 text-zinc-300 dark:text-zinc-700">
                        •
                    </span>{' '}
                    {job.workModel}, {job.location}
                </p>
            </div>

            <div className="mt-4 flex gap-8 md:col-span-4 md:mt-0">
                <LedgerMetric label="Comp" value={job.salary} />
                <LedgerMetric label="Exp" value={job.experience} />
            </div>

            <div className="mt-4 flex items-center justify-between gap-4 md:col-span-3 md:mt-0 md:justify-end">
                <LedgerMetric
                    align="right"
                    label="Added"
                    value={job.postedAt}
                />
                <Bookmark
                    className={cn(
                        'size-4',
                        job.isSaved
                            ? 'fill-primary text-primary dark:fill-primary dark:text-primary'
                            : 'text-zinc-300 dark:text-zinc-600',
                    )}
                />
                <ChevronRight className="size-4 text-zinc-300 dark:text-zinc-600" />
            </div>
        </Button>
    );
}
