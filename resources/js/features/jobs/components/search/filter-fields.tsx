import { BriefcaseBusiness, Layers3, MapPin, Search, TrendingUp, Wallet, Zap } from 'lucide-react';
import { useMemo, useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { JobSuggestionItem } from '@/features/jobs/types';
import { sectionLabelClassName } from '@/features/jobs/utils';
import { cn } from '@/lib/utils';
import { getNextDropdownFacetState } from './dropdown-facet-state';

export type MultiSelectOption = {
    label: string;
    value: string;
    count?: number;
    disabled?: boolean;
};

const controlClassName =
    'h-7 rounded-none border-0 bg-transparent px-0 py-0 text-sm leading-none font-medium text-foreground shadow-none ring-0 focus-visible:ring-0 placeholder:text-muted-foreground/70';
const labelIconClassName = 'size-3.5 text-muted-foreground/70';

const salaryBounds = {
    min: 60_000,
    max: 240_000,
    step: 5_000,
} as const;

export function TextFilterField({
    label,
    placeholder,
    value,
    onValueChange,
    suggestions = [],
    isSuggesting = false,
    activeSuggestionIndex = -1,
    onActiveSuggestionIndexChange = () => {},
    onSuggestionSelect = () => {},
    onSuggestionsVisibilityChange = () => {},
    suggestionsOpen = false,
}: {
    label: string;
    placeholder: string;
    value: string;
    onValueChange: (value: string) => void;
    suggestions?: JobSuggestionItem[];
    isSuggesting?: boolean;
    activeSuggestionIndex?: number;
    onActiveSuggestionIndexChange?: (value: number) => void;
    onSuggestionSelect?: (suggestion: JobSuggestionItem) => void;
    onSuggestionsVisibilityChange?: (open: boolean) => void;
    suggestionsOpen?: boolean;
}) {
    return (
        <section className="relative space-y-2.5">
            <Label className="flex items-center gap-2 text-sm font-semibold text-muted-foreground">
                {renderFilterIcon(label)}
                {label}
            </Label>
            <div className="rounded-2xl bg-card px-3 py-2 shadow-[inset_0_0_0_1px_rgba(25,28,30,0.08)] transition-shadow focus-within:shadow-[0_0_0_4px_rgba(0,74,198,0.08)]">
                <Input
                    value={value}
                    placeholder={placeholder}
                    onChange={(event) => onValueChange(event.target.value)}
                    onFocus={() => {
                        if (suggestions.length > 0) {
                            onSuggestionsVisibilityChange(true);
                        }
                    }}
                    onBlur={() => {
                        window.setTimeout(() => onSuggestionsVisibilityChange(false), 120);
                    }}
                    onKeyDown={(event) => {
                        if (!suggestionsOpen || suggestions.length === 0) {
                            return;
                        }

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            onActiveSuggestionIndexChange(
                                (activeSuggestionIndex + 1) % suggestions.length,
                            );
                        }

                        if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            onActiveSuggestionIndexChange(
                                activeSuggestionIndex <= 0
                                    ? suggestions.length - 1
                                    : activeSuggestionIndex - 1,
                            );
                        }

                        if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                            event.preventDefault();
                            onSuggestionSelect(suggestions[activeSuggestionIndex]);
                        }

                        if (event.key === 'Escape') {
                            onSuggestionsVisibilityChange(false);
                        }
                    }}
                    className={controlClassName}
                />
            </div>

            {isSuggesting ? (
                <div className="pointer-events-none absolute top-[2.45rem] right-3 text-muted-foreground/70">
                    <Spinner className="size-3.5" />
                </div>
            ) : null}

            {suggestionsOpen && suggestions.length > 0 ? (
                <div className="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-2xl border border-transparent bg-card shadow-[0_20px_40px_-24px_rgba(0,74,198,0.12)]">
                    {suggestions.map((suggestion, index) => (
                        <button
                            key={`${suggestion.type}-${suggestion.label}`}
                            type="button"
                            onMouseEnter={() => onActiveSuggestionIndexChange(index)}
                            onMouseDown={(event) => {
                                event.preventDefault();
                                onSuggestionSelect(suggestion);
                            }}
                            className={cn(
                                'flex w-full items-center justify-between px-3 py-2 text-left text-sm transition-colors',
                                index === activeSuggestionIndex
                                    ? 'bg-primary/8 text-foreground'
                                    : 'bg-card text-muted-foreground hover:bg-secondary',
                            )}
                        >
                            <span className="truncate">{suggestion.label}</span>
                            <span className="inline-flex items-center gap-1 text-[10px] font-semibold tracking-[0.22em] text-muted-foreground/70 uppercase">
                                <Search className="size-3" />
                                {suggestion.type.replace('_', ' ')}
                            </span>
                        </button>
                    ))}
                </div>
            ) : null}
        </section>
    );
}

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
        const normalizedSearch = searchValue.trim().toLowerCase();

        if (normalizedSearch === '') {
            return options;
        }

        return options.filter((option) => option.label.toLowerCase().includes(normalizedSearch));
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
                                {searchable && searchValue.trim() !== ''
                                    ? `No ${label.toLowerCase()} facets match "${searchValue.trim()}"`
                                    : (emptyMessage ?? `No ${label.toLowerCase()} facets yet`)}
                            </div>
                        )}
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
        </section>
    );
}

export function SalaryRangeField({
    label,
    salaryMin,
    salaryMax,
    onSalaryMinChange,
    onSalaryMaxChange,
}: {
    label: string;
    salaryMin: number | null;
    salaryMax: number | null;
    onSalaryMinChange: (value: string) => void;
    onSalaryMaxChange: (value: string) => void;
}) {
    const normalizedMinimum = clampSalaryValue(salaryMin ?? salaryBounds.min);
    const normalizedMaximum = clampSalaryValue(salaryMax ?? salaryBounds.max);
    const sliderMinimum = Math.min(normalizedMinimum, normalizedMaximum);
    const sliderMaximum = Math.max(normalizedMinimum, normalizedMaximum);
    const sliderStart =
        ((sliderMinimum - salaryBounds.min) / (salaryBounds.max - salaryBounds.min)) * 100;
    const sliderEnd =
        ((sliderMaximum - salaryBounds.min) / (salaryBounds.max - salaryBounds.min)) * 100;

    return (
        <section className="space-y-3">
            <Label
                className={`${sectionLabelClassName} flex items-center gap-2 text-muted-foreground`}
            >
                {renderFilterIcon(label)}
                {label}
            </Label>

            <div className="rounded-2xl bg-card px-4 py-4 shadow-[inset_0_0_0_1px_rgba(25,28,30,0.08)]">
                <div className="relative mb-4 h-5">
                    <div className="absolute top-1/2 h-2 w-full -translate-y-1/2 rounded-full bg-secondary" />
                    <div
                        className="absolute top-1/2 h-2 -translate-y-1/2 rounded-full bg-primary"
                        style={{
                            left: `${sliderStart}%`,
                            width: `${Math.max(sliderEnd - sliderStart, 0)}%`,
                        }}
                    />
                    <input
                        type="range"
                        min={salaryBounds.min}
                        max={salaryBounds.max}
                        step={salaryBounds.step}
                        value={sliderMinimum}
                        onChange={(event) => {
                            const nextValue = Math.min(
                                Number(event.target.value),
                                sliderMaximum - salaryBounds.step,
                            );
                            onSalaryMinChange(String(nextValue));
                        }}
                        className="pointer-events-none absolute inset-0 z-20 w-full appearance-none bg-transparent [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:size-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)] [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:size-5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)]"
                    />
                    <input
                        type="range"
                        min={salaryBounds.min}
                        max={salaryBounds.max}
                        step={salaryBounds.step}
                        value={sliderMaximum}
                        onChange={(event) => {
                            const nextValue = Math.max(
                                Number(event.target.value),
                                sliderMinimum + salaryBounds.step,
                            );
                            onSalaryMaxChange(String(nextValue));
                        }}
                        className="pointer-events-none absolute inset-0 z-30 w-full appearance-none bg-transparent [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:size-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)] [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:size-5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)]"
                    />
                </div>

                <div className="mb-3 flex justify-between text-[11px] font-semibold text-primary">
                    <span>{formatCompactSalaryValue(sliderMinimum)}</span>
                    <span>{formatCompactSalaryValue(sliderMaximum)}</span>
                </div>

                <div className="grid grid-cols-2 gap-3">
                    <div className="rounded-2xl bg-secondary px-3 py-2">
                        <Input
                            type="number"
                            value={salaryMin ?? ''}
                            onChange={(event) => onSalaryMinChange(event.target.value)}
                            placeholder="Min"
                            className={controlClassName}
                        />
                    </div>
                    <div className="rounded-2xl bg-secondary px-3 py-2">
                        <Input
                            type="number"
                            value={salaryMax ?? ''}
                            onChange={(event) => onSalaryMaxChange(event.target.value)}
                            placeholder="Max"
                            className={controlClassName}
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}

function renderFilterIcon(label: string) {
    switch (label) {
        case 'Keywords':
            return <Search className={labelIconClassName} />;
        case 'Location':
            return <MapPin className={labelIconClassName} />;
        case 'Category':
            return <Layers3 className={labelIconClassName} />;
        case 'Salary Range':
            return <Wallet className={labelIconClassName} />;
        case 'Skills':
            return <Zap className={labelIconClassName} />;
        case 'Experience':
            return <TrendingUp className={labelIconClassName} />;
        case 'Work Model':
        case 'Job Type':
            return <BriefcaseBusiness className={labelIconClassName} />;
        default:
            return null;
    }
}

function clampSalaryValue(value: number): number {
    return Math.min(Math.max(value, salaryBounds.min), salaryBounds.max);
}

function formatCompactSalaryValue(value: number): string {
    return `$${Math.round(value / 1000)}k`;
}
