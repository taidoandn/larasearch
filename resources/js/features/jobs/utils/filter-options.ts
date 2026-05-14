import type { JobFacetItem, MultiSelectOption } from '@/features/jobs/types';
import { formatSlugLabel } from './formatters';

export function buildFacetChecklistOptions(
    items: JobFacetItem[],
    selectedValues: string[],
    formatLabel: (value: string) => string = formatSlugLabel,
): MultiSelectOption[] {
    const options = uniqueFacetItems(items).map((item) => ({
        label: item.label?.trim() || formatLabel(item.value),
        value: item.value,
        count: item.count,
        disabled: item.count === 0 && !selectedValues.includes(item.value),
    }));

    selectedValues.forEach((selectedValue) => {
        if (options.some((option) => option.value === selectedValue)) {
            return;
        }

        options.unshift({
            label: formatLabel(selectedValue),
            value: selectedValue,
            count: 0,
            disabled: false,
        });
    });

    return options;
}

function uniqueFacetItems(items: JobFacetItem[]): JobFacetItem[] {
    const seen = new Set<string>();

    return items.filter((item) => {
        if (seen.has(item.value)) {
            return false;
        }

        seen.add(item.value);

        return true;
    });
}
