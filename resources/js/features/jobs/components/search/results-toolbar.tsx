import { SlidersHorizontal, X } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import type { JobFilters } from '@/features/jobs/types';
import {
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSlugLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from '@/features/jobs/utils';

const sortOptions = [
    { label: 'Most Relevant', value: 'best_match' },
    { label: 'Most Recent', value: 'newest' },
    { label: 'Salary: High to Low', value: 'salary_desc' },
    { label: 'Salary: Low to High', value: 'salary_asc' },
];

type ToolbarChip = {
    key: string;
    label: string;
    value: string;
    onRemove: () => void;
};

export function ResultsToolbar({
    total,
    filters,
    sort,
    isRefreshing,
    onSortChange,
    onApplyFilters,
    onResetFilters,
}: {
    total: number;
    filters: JobFilters;
    sort: string;
    isRefreshing: boolean;
    onSortChange: (sortValue: string) => void;
    onApplyFilters: (filters: JobFilters) => void;
    onResetFilters: () => void;
}) {
    const activeChips = buildToolbarChips(filters, onApplyFilters);
    const summary = buildSummary(filters);

    return (
        <div className="rounded-4xl bg-secondary px-5 py-5 sm:px-6 sm:py-6">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="space-y-3">
                    <p className="text-sm font-medium text-muted-foreground">
                        Showing{' '}
                        <span className="font-semibold text-foreground">
                            {total.toLocaleString()} jobs
                        </span>{' '}
                        for <span className="font-semibold text-primary">{summary}</span>
                    </p>

                    <div className="flex flex-wrap items-center gap-2">
                        {activeChips.length > 0 ? (
                            activeChips.map((chip) => (
                                <button
                                    key={chip.key}
                                    type="button"
                                    onClick={chip.onRemove}
                                    className="inline-flex items-center gap-3 rounded-xl bg-card px-4 py-2 text-xs font-medium text-muted-foreground shadow-[0_14px_26px_-22px_rgba(0,74,198,0.12)] transition-colors hover:bg-white"
                                >
                                    <span>{chip.label}:</span>
                                    <span className="font-semibold text-foreground">
                                        {chip.value}
                                    </span>
                                    <X className="size-3.5 text-muted-foreground/70" />
                                </button>
                            ))
                        ) : (
                            <span className="text-xs font-medium text-muted-foreground/70">
                                No active filters
                            </span>
                        )}
                    </div>

                    <button
                        type="button"
                        onClick={onResetFilters}
                        className="text-xs font-semibold text-primary transition-colors hover:text-primary/80"
                    >
                        Clear all filters
                        {isRefreshing ? (
                            <Spinner className="ml-2 inline size-3.5 align-[-0.1em]" />
                        ) : null}
                    </button>
                </div>

                <div className="flex items-center gap-3 self-start rounded-2xl bg-card px-4 py-3 shadow-[0_14px_30px_-24px_rgba(0,74,198,0.12)]">
                    <SlidersHorizontal className="size-4 text-muted-foreground/70" />
                    <span className="text-[10px] font-semibold tracking-[0.24em] text-muted-foreground/70 uppercase">
                        Sort by
                    </span>
                    <Select value={sort} onValueChange={onSortChange} disabled={isRefreshing}>
                        <SelectTrigger className="h-6 w-auto min-w-0 rounded-none border-0 bg-transparent px-0 py-0 text-sm font-semibold text-foreground shadow-none ring-0 focus-visible:ring-0">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent
                            align="end"
                            className="rounded-2xl border-transparent bg-card shadow-[0_20px_40px_-24px_rgba(0,74,198,0.12)]"
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
        </div>
    );
}

function buildToolbarChips(
    filters: JobFilters,
    onApplyFilters: (filters: JobFilters) => void,
): ToolbarChip[] {
    const chips: ToolbarChip[] = [];

    if (filters.work_model.length > 0) {
        chips.push({
            key: 'work-model',
            label: 'Work Model',
            value: filters.work_model.map((item) => formatWorkModelLabel(item)).join(', '),
            onRemove: () =>
                onApplyFilters({
                    ...filters,
                    work_model: [],
                    page: 1,
                }),
        });
    }

    if (filters.experience_level.length > 0) {
        chips.push({
            key: 'experience-level',
            label: 'Experience',
            value: filters.experience_level
                .map((item) => formatExperienceLevelLabel(item))
                .join(', '),
            onRemove: () =>
                onApplyFilters({
                    ...filters,
                    experience_level: [],
                    page: 1,
                }),
        });
    }

    if (filters.location.length > 0) {
        chips.push({
            key: 'location',
            label: 'Location',
            value: filters.location.map((item) => formatSlugLabel(item)).join(', '),
            onRemove: () =>
                onApplyFilters({
                    ...filters,
                    location: [],
                    page: 1,
                }),
        });
    }

    if (filters.category.length > 0) {
        chips.push({
            key: 'category',
            label: 'Category',
            value: filters.category.map((item) => formatSlugLabel(item)).join(', '),
            onRemove: () =>
                onApplyFilters({
                    ...filters,
                    category: [],
                    page: 1,
                }),
        });
    }

    if (filters.job_type.length > 0) {
        chips.push({
            key: 'job-type',
            label: 'Job Type',
            value: filters.job_type.map((item) => formatJobTypeLabel(item)).join(', '),
            onRemove: () =>
                onApplyFilters({
                    ...filters,
                    job_type: [],
                    page: 1,
                }),
        });
    }

    if (filters.salary_min !== null || filters.salary_max !== null) {
        chips.push({
            key: 'salary',
            label: 'Salary',
            value: formatSalaryRange({
                min: filters.salary_min,
                max: filters.salary_max,
                currency: null,
                is_visible: true,
            }),
            onRemove: () =>
                onApplyFilters({
                    ...filters,
                    salary_min: null,
                    salary_max: null,
                    page: 1,
                }),
        });
    }

    return chips;
}

function buildSummary(filters: JobFilters): string {
    const summaryParts = [
        filters.work_model.length > 0
            ? filters.work_model.map((item) => formatWorkModelLabel(item)).join(' + ')
            : null,
        filters.category.length > 0
            ? filters.category.map((item) => formatSlugLabel(item)).join(' + ')
            : null,
        filters.q.trim() !== '' ? filters.q : null,
    ].filter((value): value is string => value !== null);

    return summaryParts.length > 0 ? summaryParts.join(' • ') : 'all open roles';
}
