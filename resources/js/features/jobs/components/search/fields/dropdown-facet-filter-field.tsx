import { useMemo, useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { MultiSelectOption } from '@/features/jobs/types';
import { getNextDropdownFacetState, sectionLabelClassName } from '@/features/jobs/utils';
import { renderFilterIcon } from './shared';

export function DropdownFacetFilterField({
    label,
    value,
    options,
    placeholder = 'Select...',
    emptyMessage,
    searchable = false,
    searchPlaceholder = 'Search...',
    onValueChange,
}: {
    label: string;
    value: string[];
    options: MultiSelectOption[];
    placeholder?: string;
    emptyMessage?: string;
    searchable?: boolean;
    searchPlaceholder?: string;
    onValueChange: (value: string[]) => void;
}) {
    const [searchValue, setSearchValue] = useState('');
    const selectedOptions = options.filter((option) => value.includes(option.value));
    const visibleOptions = useMemo(() => {
        return filterOptions(options, searchValue);
    }, [options, searchValue]);

    return (
        <section className="space-y-2.5">
            <Label
                className={`${sectionLabelClassName} flex items-center gap-2 text-muted-foreground`}
            >
                {renderFilterIcon(label)}
                {label}
            </Label>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <button
                        type="button"
                        className="flex w-full items-center justify-between rounded-2xl bg-card px-3 py-2 text-left text-sm font-medium text-foreground shadow-[inset_0_0_0_1px_rgba(25,28,30,0.08)]"
                    >
                        <SelectedOptionSummary
                            placeholder={placeholder}
                            selectedOptions={selectedOptions}
                        />
                        <span className="text-[10px] font-semibold tracking-[0.2em] text-muted-foreground/55 uppercase">
                            {value.length}
                        </span>
                    </button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="start"
                    sideOffset={8}
                    className="w-(--radix-dropdown-menu-trigger-width) min-w-80 rounded-2xl border-transparent bg-card p-2 shadow-[0_20px_40px_-24px_rgba(0,74,198,0.12)]"
                >
                    <div className="px-2 pb-2">
                        <p className={sectionLabelClassName}>Pick {label}</p>
                    </div>
                    {searchable ? (
                        <div className="px-2 pb-2">
                            <Input
                                value={searchValue}
                                placeholder={searchPlaceholder}
                                onChange={(event) => setSearchValue(event.target.value)}
                                className="h-9 rounded-xl border-0 bg-secondary px-3 text-sm shadow-none ring-0 focus-visible:ring-0"
                            />
                        </div>
                    ) : null}
                    <div className="max-h-64 overflow-y-auto pr-1">
                        {visibleOptions.length > 0 ? (
                            visibleOptions.map((option) => {
                                const checked = value.includes(option.value);

                                return (
                                    <DropdownMenuCheckboxItem
                                        key={option.value}
                                        checked={checked}
                                        disabled={option.disabled === true && !checked}
                                        onSelect={(event) => event.preventDefault()}
                                        onCheckedChange={(nextChecked) => {
                                            const nextState = getNextDropdownFacetState({
                                                currentValue: value,
                                                optionValue: option.value,
                                                nextChecked,
                                                searchValue,
                                                searchable,
                                            });

                                            setSearchValue(nextState.nextSearchValue);
                                            onValueChange(nextState.nextValue);
                                        }}
                                        className="flex items-center gap-3 rounded-xl py-2 pr-2 pl-8 text-sm"
                                    >
                                        <span className="flex-1">{option.label}</span>
                                        {option.count !== undefined ? (
                                            <span className="rounded-full bg-secondary px-2 py-0.5 text-[11px] text-muted-foreground/70">
                                                {option.count.toLocaleString()}
                                            </span>
                                        ) : null}
                                    </DropdownMenuCheckboxItem>
                                );
                            })
                        ) : (
                            <div className="px-2 py-3 text-sm text-muted-foreground">
                                {facetEmptyMessage(label, searchValue, searchable, emptyMessage)}
                            </div>
                        )}
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
        </section>
    );
}

function SelectedOptionSummary({
    placeholder,
    selectedOptions,
}: {
    placeholder: string;
    selectedOptions: MultiSelectOption[];
}) {
    return (
        <span className="flex min-w-0 flex-wrap items-center gap-2">
            {selectedOptions.length > 0 ? (
                selectedOptions.slice(0, 2).map((option) => (
                    <span
                        key={option.value}
                        className="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-semibold text-primary"
                    >
                        {option.label}
                    </span>
                ))
            ) : (
                <span className="text-muted-foreground/70">{placeholder}</span>
            )}
            {selectedOptions.length > 2 ? (
                <span className="rounded-lg bg-secondary px-2 py-1 text-[10px] font-semibold text-muted-foreground">
                    +{selectedOptions.length - 2}
                </span>
            ) : null}
        </span>
    );
}

function filterOptions(options: MultiSelectOption[], searchValue: string): MultiSelectOption[] {
    const normalizedSearch = searchValue.trim().toLowerCase();

    if (normalizedSearch === '') {
        return options;
    }

    return options.filter((option) => option.label.toLowerCase().includes(normalizedSearch));
}

function facetEmptyMessage(
    label: string,
    searchValue: string,
    searchable: boolean,
    emptyMessage?: string,
): string {
    const trimmedSearchValue = searchValue.trim();

    if (searchable && trimmedSearchValue !== '') {
        return `No ${label.toLowerCase()} facets match "${trimmedSearchValue}"`;
    }

    return emptyMessage ?? `No ${label.toLowerCase()} facets yet`;
}
