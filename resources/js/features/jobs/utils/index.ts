import { formatDisplayDate, formatDisplayDateTime } from '@/lib/formatters';
export { buildJobSearchUrl, compactJobSearchQuery } from './search-query';

export const sectionLabelClassName = 'text-sm font-semibold';

export function formatSlugLabel(value: string): string {
    return value
        .trim()
        .split(/[-_]+/)
        .filter((part) => part !== '')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

export function formatJobTypeLabel(value: string | null | undefined, fallback = 'Unknown'): string {
    return formatEnumLabel(value, fallback);
}

export function formatWorkModelLabel(
    value: string | null | undefined,
    fallback = 'Unknown',
): string {
    return formatEnumLabel(value, fallback);
}

export function formatExperienceLevelLabel(
    value: string | null | undefined,
    fallback = 'Unknown',
): string {
    return formatEnumLabel(value, fallback);
}

export function formatSalaryRange({
    min,
    max,
    currency,
    is_visible,
}: {
    min: number | null;
    max: number | null;
    currency: string | null;
    is_visible: boolean;
}): string {
    if (!is_visible || (min === null && max === null)) {
        return 'Comp undisclosed';
    }

    const prefix = currency ? `${currency} ` : '';
    const lowerBound = min ?? max;
    const upperBound = max ?? min;

    if (lowerBound === null && upperBound === null) {
        return 'Comp undisclosed';
    }

    if (lowerBound !== null && upperBound !== null) {
        return `${prefix}${lowerBound.toLocaleString()} - ${upperBound.toLocaleString()}`;
    }

    return lowerBound !== null
        ? `${prefix}${lowerBound.toLocaleString()}+`
        : `${prefix}${upperBound?.toLocaleString() ?? ''}`;
}

export { formatDisplayDate, formatDisplayDateTime };

function formatEnumLabel(value: string | null | undefined, fallback: string): string {
    if (value === null || value === undefined || value.trim() === '') {
        return fallback;
    }

    return formatSlugLabel(value);
}
