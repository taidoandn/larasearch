import type { JobFilters } from '@/features/jobs/types';
import {
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSalaryRange,
    formatSlugLabel,
    formatWorkModelLabel,
} from './formatters';

export type ToolbarChip = {
    key: string;
    label: string;
    value: string;
    onRemove: () => void;
};

export function buildToolbarChips(
    filters: JobFilters,
    onApplyFilters: (filters: JobFilters) => void,
): ToolbarChip[] {
    const chips: ToolbarChip[] = [];

    addArrayFilterChip(chips, filters, onApplyFilters, {
        key: 'work-model',
        label: 'Work Model',
        filterKey: 'work_model',
        formatValue: formatWorkModelLabel,
    });

    addArrayFilterChip(chips, filters, onApplyFilters, {
        key: 'experience-level',
        label: 'Experience',
        filterKey: 'experience_level',
        formatValue: formatExperienceLevelLabel,
    });

    addArrayFilterChip(chips, filters, onApplyFilters, {
        key: 'location',
        label: 'Location',
        filterKey: 'location',
        formatValue: formatSlugLabel,
    });

    addArrayFilterChip(chips, filters, onApplyFilters, {
        key: 'category',
        label: 'Category',
        filterKey: 'category',
        formatValue: formatSlugLabel,
    });

    addArrayFilterChip(chips, filters, onApplyFilters, {
        key: 'job-type',
        label: 'Job Type',
        filterKey: 'job_type',
        formatValue: formatJobTypeLabel,
    });

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

export function buildSearchSummary(filters: JobFilters): string {
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

function addArrayFilterChip<K extends ArrayFilterKey>(
    chips: ToolbarChip[],
    filters: JobFilters,
    onApplyFilters: (filters: JobFilters) => void,
    config: {
        key: string;
        label: string;
        filterKey: K;
        formatValue: (value: string) => string;
    },
): void {
    const values = filters[config.filterKey];

    if (values.length === 0) {
        return;
    }

    chips.push({
        key: config.key,
        label: config.label,
        value: values.map((item) => config.formatValue(item)).join(', '),
        onRemove: () =>
            onApplyFilters({
                ...filters,
                [config.filterKey]: [],
                page: 1,
            }),
    });
}

type ArrayFilterKey =
    | 'category'
    | 'experience_level'
    | 'job_type'
    | 'location'
    | 'skills'
    | 'work_model';
