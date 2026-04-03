import { SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';
import { sectionLabelClassName } from '@/components/search/shared';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const sortOptions = ['Precision Match', 'Salary: High to Low', 'Most Recent'];

export function ResultsToolbar() {
    const [sortValue, setSortValue] = useState(sortOptions[0]);

    return (
        <div className="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-800 dark:bg-zinc-900/50">
            <p className="font-mono text-[10px] tracking-[0.28em] text-zinc-400 uppercase dark:text-zinc-500">
                Displaying{' '}
                <span className="font-semibold text-zinc-700 dark:text-zinc-200">
                    142
                </span>{' '}
                matches <span className="mx-1">/</span> indexed 0.04s
            </p>

            <div className="flex items-center gap-3 self-start sm:self-auto">
                <SlidersHorizontal className="size-4 text-zinc-400 dark:text-zinc-500" />
                <span className={sectionLabelClassName}>Sort By</span>
                <Select value={sortValue} onValueChange={setSortValue}>
                    <SelectTrigger className="h-6 w-auto min-w-0 rounded-none border-0 bg-transparent px-0 py-0 text-sm font-medium text-zinc-700 shadow-none ring-0 focus-visible:ring-0 dark:bg-transparent dark:text-zinc-200">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent
                        align="end"
                        className="rounded-none border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950"
                    >
                        {sortOptions.map((option) => (
                            <SelectItem key={option} value={option}>
                                {option}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    );
}
