import { router } from '@inertiajs/react';
import { SlidersHorizontal } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { sectionLabelClassName } from '@/features/jobs/components/shared';
import type { JobFilters } from '@/features/jobs/types';
import { index as jobsIndex } from '@/routes/jobs';

const sortOptions = [
    { label: 'Best Match', value: 'best_match' },
    { label: 'Salary: High to Low', value: 'salary_desc' },
    { label: 'Salary: Low to High', value: 'salary_asc' },
    { label: 'Most Recent', value: 'newest' },
];

export function JobsResultsToolbar({
    total,
    sort,
    filters,
}: {
    total: number;
    sort: string;
    filters: JobFilters;
}) {
    const updateSort = (sortValue: string) => {
        router.get(jobsIndex.url(), {
            ...filters,
            sort: sortValue,
            page: 1,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <div className="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-800 dark:bg-zinc-900/50">
            <p className="font-mono text-[10px] tracking-[0.28em] text-zinc-400 uppercase dark:text-zinc-500">
                Displaying{' '}
                <span className="font-semibold text-zinc-700 dark:text-zinc-200">
                    {total}
                </span>{' '}
                matches
            </p>

            <div className="flex items-center gap-3 self-start sm:self-auto">
                <SlidersHorizontal className="size-4 text-zinc-400 dark:text-zinc-500" />
                <span className={sectionLabelClassName}>Sort By</span>
                <Select value={sort} onValueChange={updateSort}>
                    <SelectTrigger className="h-6 w-auto min-w-0 rounded-none border-0 bg-transparent px-0 py-0 text-sm font-medium text-zinc-700 shadow-none ring-0 focus-visible:ring-0 dark:bg-transparent dark:text-zinc-200">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent
                        align="end"
                        className="rounded-none border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950"
                    >
                        {sortOptions.map((option) => (
                            <SelectItem key={option.value} value={option.value}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    );
}
