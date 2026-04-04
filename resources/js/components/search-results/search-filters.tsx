import { useState } from 'react';
import {
    activeFilterChips,
    searchFilters,
} from '@/components/search/mock-search-data';
import type {
    SearchFilterChip,
    SearchFilterField,
} from '@/components/search/mock-search-data';
import { sectionLabelClassName } from '@/components/search/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type FilterState = Record<string, string>;

const controlClassName =
    'h-7 rounded-none border-0 bg-transparent px-0 py-0 text-sm leading-none font-medium text-zinc-900 shadow-none ring-0 focus-visible:ring-0 dark:bg-transparent dark:text-zinc-100';

const selectControlClassName = `${controlClassName} [&_[data-slot=select-value]]:flex [&_[data-slot=select-value]]:items-center [&_[data-slot=select-value]]:leading-none [&_[data-slot=select-value]]:min-h-0`;

const chipLabelMap: Record<string, string> = {
    keyword: 'Role',
    location: 'Remote',
    'work-model': 'Work Model',
    experience: 'Experience',
    salary: 'Min Salary',
};

export function SearchFilters() {
    const initialValues: FilterState = Object.fromEntries(
        searchFilters.map((filter) => [filter.id, filter.value]),
    );
    const [values, setValues] = useState<FilterState>(initialValues);
    const chips: SearchFilterChip[] = Object.entries(values)
        .filter(([, value]) => value.trim().length > 0)
        .filter(([id, value]) => {
            if (id === 'work-model' && value === 'All Models') {
                return false;
            }

            if (id === 'experience' && value === 'All Levels') {
                return false;
            }

            return true;
        })
        .slice(0, 4)
        .map(([id, value]) => ({
            id,
            label: chipLabelMap[id] ?? id,
            value,
        }));

    const updateValue = (id: string, value: string) => {
        setValues((current) => ({ ...current, [id]: value }));
    };

    const resetFilters = () => {
        setValues(initialValues);
    };

    return (
        <div className="space-y-5 px-4 py-4 sm:px-6">
            <div className="flex flex-col gap-3 xl:flex-row xl:items-stretch">
                <div className="grid flex-1 gap-px overflow-hidden border border-zinc-200 bg-zinc-200 md:grid-cols-2 xl:grid-cols-5 dark:border-zinc-800 dark:bg-zinc-800">
                    {searchFilters.map((filter) => (
                        <FilterField
                            key={filter.id}
                            filter={filter}
                            value={values[filter.id] ?? ''}
                            onValueChange={updateValue}
                        />
                    ))}
                </div>

                <Button className="h-auto rounded-none bg-primary px-6 py-3 text-[11px] font-semibold tracking-[0.22em] text-primary-foreground uppercase shadow-none hover:bg-primary/90">
                    Search Database
                </Button>
            </div>

            <div className="flex flex-wrap items-center gap-2">
                {(chips.length > 0 ? chips : activeFilterChips).map((chip) => (
                    <div
                        key={chip.id}
                        className="inline-flex items-center gap-2 bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        <span className={sectionLabelClassName}>
                            {chip.label}
                        </span>
                        <span>{chip.value}</span>
                    </div>
                ))}

                <Button
                    variant="ghost"
                    onClick={resetFilters}
                    className="h-auto rounded-none px-0 py-0 text-[10px] font-semibold tracking-[0.24em] text-primary uppercase shadow-none hover:bg-transparent hover:text-primary/80 dark:text-primary"
                >
                    Reset Filters
                </Button>
            </div>
        </div>
    );
}

function FilterField({
    filter,
    value,
    onValueChange,
}: {
    filter: SearchFilterField;
    value: string;
    onValueChange: (id: string, value: string) => void;
}) {
    return (
        <div className="space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{filter.label}</Label>

            {filter.options ? (
                <Select
                    value={value}
                    onValueChange={(nextValue) =>
                        onValueChange(filter.id, nextValue)
                    }
                >
                    <SelectTrigger
                        className={`${selectControlClassName} w-full`}
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent
                        align="start"
                        className="rounded-none border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950"
                    >
                        {filter.options.map((option) => (
                            <SelectItem key={option} value={option}>
                                {option}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ) : (
                <Input
                    value={value}
                    onChange={(event) =>
                        onValueChange(filter.id, event.target.value)
                    }
                    className={controlClassName}
                />
            )}
        </div>
    );
}
