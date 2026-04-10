import { Search, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import {
    useJobSuggestions,
} from '@/features/jobs/hooks/use-job-suggestions';
import type { JobFacetItem, JobFilters as JobFiltersState, JobResultsPayload, JobSuggestionItem } from '@/features/jobs/types';
import {
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSlugLabel,
    formatSalaryRange,
    formatWorkModelLabel,
    getFacetLabel,
    sectionLabelClassName,
} from '@/features/jobs/utils';

type JobsFilterProps = {
    filters: JobFiltersState;
    facets: JobResultsPayload['facets'];
    isRefreshing: boolean;
    onApply: (nextFilters: JobFiltersState) => void;
    onReset: () => void;
};

type DraftFilters = JobFiltersState;
type FilterChip = {
    key: string;
    label: string;
    value: string;
    filterKey:
        | 'q'
        | 'location'
        | 'category'
        | 'skills'
        | 'job_type'
        | 'work_model'
        | 'experience_level'
        | 'salary_range'
        | 'salary_min'
        | 'salary_max';
    skillValue?: string;
};

const controlClassName =
    'h-8 rounded-none border-0 bg-transparent px-0 py-0 text-sm leading-none font-medium text-zinc-900 shadow-none ring-0 focus-visible:ring-0 dark:bg-transparent dark:text-zinc-100';

const selectControlClassName = `${controlClassName} [&_[data-slot=select-value]]:flex [&_[data-slot=select-value]]:items-center [&_[data-slot=select-value]]:leading-none [&_[data-slot=select-value]]:min-h-0`;

const defaultFilterLabel = 'All';
const emptySelectValue = '__all__';

export function JobsFilters({
    filters,
    facets,
    isRefreshing,
    onApply,
    onReset,
}: JobsFilterProps) {
    const [values, setValues] = useState<DraftFilters>(filters);
    const {
        suggestions,
        isSuggesting,
        isSuggestionsOpen,
        activeSuggestionIndex,
        setActiveSuggestionIndex,
        openSuggestions,
        closeSuggestions,
        clearSuggestions,
    } = useJobSuggestions(values.q);

    useEffect(() => {
        setValues(filters);
    }, [filters]);

    const categoryOptions = useMemo(
        () => buildFacetSelectOptions(facets.categories, 'All Categories', filters.category),
        [facets.categories, filters.category],
    );

    const workModelOptions = useMemo(
        () => buildFacetSelectOptions(facets.work_models, 'All Models', filters.work_model),
        [facets.work_models, filters.work_model],
    );

    const experienceOptions = useMemo(
        () => buildFacetSelectOptions(facets.experience_levels, 'All Levels', filters.experience_level),
        [facets.experience_levels, filters.experience_level],
    );

    const jobTypeOptions = useMemo(
        () => buildFacetSelectOptions(facets.job_types, 'All Types', filters.job_type),
        [facets.job_types, filters.job_type],
    );

    const skillOptions = useMemo(
        () =>
            uniqueFacetItems(facets.skills).map((item) => ({
                label: formatFacetOptionLabel(item),
                value: item.value,
            })),
        [facets.skills],
    );

    const chips = useMemo(() => buildFilterChips(filters), [filters]);

    const updateValue = <K extends keyof JobFiltersState>(
        key: K,
        value: JobFiltersState[K],
    ) => {
        setValues((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const submit = (nextValues: DraftFilters = values) => {
        onApply({
            ...nextValues,
            page: 1,
        });
    };

    const submitAndUpdate = (nextValues: DraftFilters) => {
        setValues(nextValues);
        submit(nextValues);
    };

    const clearFilter = (
        key:
            | 'q'
            | 'location'
            | 'category'
            | 'skills'
            | 'job_type'
            | 'work_model'
            | 'experience_level'
            | 'salary_range'
            | 'salary_min'
            | 'salary_max',
        skillValue?: string,
    ) => {
        switch (key) {
            case 'q':
                submitAndUpdate({
                    ...filters,
                    q: '',
                    page: 1,
                });
                break;
            case 'location':
                submitAndUpdate({
                    ...filters,
                    location: '',
                    page: 1,
                });
                break;
            case 'category':
                submitAndUpdate({
                    ...filters,
                    category: '',
                    page: 1,
                });
                break;
            case 'skills':
                submitAndUpdate({
                    ...filters,
                    skills: filters.skills.filter((skill) => skill !== skillValue),
                    page: 1,
                });
                break;
            case 'job_type':
                submitAndUpdate({
                    ...filters,
                    job_type: '',
                    page: 1,
                });
                break;
            case 'work_model':
                submitAndUpdate({
                    ...filters,
                    work_model: '',
                    page: 1,
                });
                break;
            case 'experience_level':
                submitAndUpdate({
                    ...filters,
                    experience_level: '',
                    page: 1,
                });
                break;
            case 'salary_min':
            case 'salary_range':
                submitAndUpdate({
                    ...filters,
                    salary_min: null,
                    salary_max: key === 'salary_range' ? null : filters.salary_max,
                    page: 1,
                });
                break;
            case 'salary_max':
                submitAndUpdate({
                    ...filters,
                    salary_max: null,
                    page: 1,
                });
                break;
        }
    };

    const toggleSkill = (skill: string, checked: boolean) => {
        const nextSkills = checked
            ? Array.from(new Set([...values.skills, skill]))
            : values.skills.filter((current) => current !== skill);

        updateValue('skills', nextSkills);
    };

    const resetFilters = () => {
        const nextValues = {
            q: '',
            location: '',
            category: '',
            skills: [],
            job_type: '',
            work_model: '',
            experience_level: '',
            salary_min: null,
            salary_max: null,
            sort: 'best_match',
            page: 1,
            per_page: filters.per_page,
        };

        setValues(nextValues);
        onReset();
    };

    const canSubmit = ! isRefreshing;

    return (
        <form
            className="space-y-5 px-4 py-4 sm:px-6"
            onSubmit={(event) => {
                event.preventDefault();
                submit();
            }}
        >
            <div className="flex flex-col gap-3 xl:flex-row xl:items-stretch">
                <div className="grid flex-1 gap-px overflow-hidden border border-zinc-200 bg-zinc-200 md:grid-cols-2 xl:grid-cols-6 dark:border-zinc-800 dark:bg-zinc-800">
                    <TextFilterField
                        label="Keyword"
                        value={values.q}
                        onValueChange={(value) => updateValue('q', value)}
                        suggestions={suggestions}
                        isSuggesting={isSuggesting}
                        activeSuggestionIndex={activeSuggestionIndex}
                        onActiveSuggestionIndexChange={setActiveSuggestionIndex}
                        onSuggestionSelect={(suggestion) => {
                            const nextValues = {
                                ...values,
                                q: suggestion.label,
                            };

                            setValues(nextValues);
                            clearSuggestions();
                            submit(nextValues);
                        }}
                        onSuggestionsVisibilityChange={(open) => {
                            if (open) {
                                openSuggestions();

                                return;
                            }

                            closeSuggestions();
                        }}
                        suggestionsOpen={isSuggestionsOpen}
                    />
                    <TextFilterField
                        label="Location"
                        value={values.location}
                        onValueChange={(value) => updateValue('location', value)}
                    />
                    <SelectFilterField
                        label="Category"
                        value={values.category}
                        placeholder={defaultFilterLabel}
                        options={categoryOptions}
                        onValueChange={(value) => updateValue('category', value)}
                    />
                    <SkillsFilterField
                        label="Skills"
                        value={values.skills}
                        options={skillOptions}
                        onValueChange={toggleSkill}
                    />
                    <SalaryRangeField
                        label="Salary Range"
                        salaryMin={values.salary_min}
                        salaryMax={values.salary_max}
                        onSalaryMinChange={(value) =>
                            updateValue(
                                'salary_min',
                                value === '' ? null : Number(value),
                            )
                        }
                        onSalaryMaxChange={(value) =>
                            updateValue(
                                'salary_max',
                                value === '' ? null : Number(value),
                            )
                        }
                    />
                    <SelectFilterField
                        label="Work Model"
                        value={values.work_model}
                        placeholder={defaultFilterLabel}
                        options={workModelOptions}
                        onValueChange={(value) => updateValue('work_model', value)}
                    />
                    <SelectFilterField
                        label="Experience"
                        value={values.experience_level}
                        placeholder={defaultFilterLabel}
                        options={experienceOptions}
                        onValueChange={(value) =>
                            updateValue('experience_level', value)
                        }
                    />
                    <SelectFilterField
                        label="Job Type"
                        value={values.job_type}
                        placeholder={defaultFilterLabel}
                        options={jobTypeOptions}
                        onValueChange={(value) => updateValue('job_type', value)}
                    />
                </div>

                <Button
                    type="submit"
                    disabled={! canSubmit}
                    className="h-auto w-32 rounded-none bg-primary px-6 py-3 text-[11px] font-semibold tracking-[0.22em] text-primary-foreground uppercase shadow-none hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span className="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap">
                        {isRefreshing ? (
                            <>
                                <Spinner className="size-4 shrink-0" />
                                Searching
                            </>
                        ) : (
                            'Search'
                        )}
                    </span>
                </Button>
            </div>

            <div className="flex flex-wrap items-center gap-2">
                {chips.length > 0 ? (
                    chips.map((chip) => (
                        <button
                            key={chip.key}
                            type="button"
                            onClick={() =>
                                clearFilter(chip.filterKey, chip.skillValue)
                            }
                            className="inline-flex items-center gap-2 bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                        >
                            <span className={sectionLabelClassName}>
                                {chip.label}
                            </span>
                            <span>{chip.value}</span>
                            <X className="size-3.5 text-zinc-400" />
                        </button>
                    ))
                ) : (
                    <p className="text-[11px] font-medium tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                        No active filters
                    </p>
                )}

                <Button
                    variant="ghost"
                    type="button"
                    onClick={resetFilters}
                    disabled={! canSubmit}
                    className="h-auto rounded-none px-0 py-0 text-[10px] font-semibold tracking-[0.24em] text-primary uppercase shadow-none hover:bg-transparent hover:text-primary/80 dark:text-primary"
                >
                    Reset Filters
                </Button>
            </div>
        </form>
    );
}

function TextFilterField({
    label,
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
        <div className="relative space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{label}</Label>
            <Input
                value={value}
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
                    if (! suggestionsOpen || suggestions.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        onActiveSuggestionIndexChange((activeSuggestionIndex + 1) % suggestions.length);
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        onActiveSuggestionIndexChange(
                            activeSuggestionIndex <= 0 ? suggestions.length - 1 : activeSuggestionIndex - 1,
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

            {isSuggesting ? (
                <div className="pointer-events-none absolute top-8 right-3 text-zinc-400 dark:text-zinc-500">
                    <Spinner className="size-3.5" />
                </div>
            ) : null}

            {suggestionsOpen && suggestions.length > 0 ? (
                <div className="absolute inset-x-0 top-full z-20 mt-px border border-zinc-200 bg-white shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:border-zinc-800 dark:bg-zinc-950">
                    {suggestions.map((suggestion, index) => (
                        <button
                            key={`${suggestion.type}-${suggestion.label}`}
                            type="button"
                            onMouseEnter={() => onActiveSuggestionIndexChange(index)}
                            onMouseDown={(event) => {
                                event.preventDefault();
                                onSuggestionSelect(suggestion);
                            }}
                            className={`flex w-full items-center justify-between px-3 py-2 text-left text-sm ${
                                index === activeSuggestionIndex
                                    ? 'bg-accent text-zinc-950 dark:bg-zinc-900 dark:text-zinc-50'
                                    : 'bg-white text-zinc-700 dark:bg-zinc-950 dark:text-zinc-300'
                            }`}
                        >
                            <span className="truncate">{suggestion.label}</span>
                            <span className="inline-flex items-center gap-1 text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase dark:text-zinc-500">
                                <Search className="size-3" />
                                {suggestion.type.replace('_', ' ')}
                            </span>
                        </button>
                    ))}
                </div>
            ) : null}
        </div>
    );
}

function SelectFilterField({
    label,
    value,
    placeholder,
    options,
    onValueChange,
}: {
    label: string;
    value: string;
    placeholder: string;
    options: Array<{ label: string; value: string }>;
    onValueChange: (value: string) => void;
}) {
    return (
        <div className="space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{label}</Label>

            <Select
                value={value === '' ? emptySelectValue : value}
                onValueChange={(nextValue) =>
                    onValueChange(nextValue === emptySelectValue ? '' : nextValue)
                }
            >
                <SelectTrigger className={`${selectControlClassName} w-full`}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent
                    align="start"
                    className="rounded-none border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950"
                >
                    {options.map((option) => (
                        <SelectItem
                            key={option.value || emptySelectValue}
                            value={option.value === '' ? emptySelectValue : option.value}
                        >
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}

function SkillsFilterField({
    label,
    value,
    options,
    onValueChange,
}: {
    label: string;
    value: string[];
    options: Array<{ label: string; value: string }>;
    onValueChange: (skill: string, checked: boolean) => void;
}) {
    const selectedSummary = value.length > 0 ? `${value.length} selected` : 'All';

    return (
        <div className="space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{label}</Label>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        type="button"
                        variant="ghost"
                        className="flex h-8 w-full justify-between rounded-none border-0 bg-transparent px-0 py-0 text-sm font-medium text-zinc-900 shadow-none hover:bg-transparent dark:text-zinc-100"
                    >
                        <span className="truncate">
                            {selectedSummary}
                        </span>
                        <span className="text-[10px] tracking-[0.24em] text-zinc-400 uppercase dark:text-zinc-500">
                            {value.length}
                        </span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    align="start"
                    sideOffset={8}
                    className="w-[var(--radix-dropdown-menu-trigger-width)] min-w-80 rounded-none border-zinc-200 bg-white p-2 dark:border-zinc-800 dark:bg-zinc-950"
                >
                    <div className="px-2 pb-2">
                        <p className={sectionLabelClassName}>Pick Skills</p>
                    </div>
                    <div className="max-h-64 overflow-y-auto pr-1">
                        {options.length > 0 ? (
                            options.map((option) => (
                                <DropdownMenuCheckboxItem
                                    key={option.value}
                                    checked={value.includes(option.value)}
                                    onSelect={(event) => event.preventDefault()}
                                    onCheckedChange={(checked) =>
                                        onValueChange(
                                            option.value,
                                            checked === true,
                                        )
                                    }
                                    className="flex items-center gap-3 rounded-none py-2 pr-2 pl-8 text-sm"
                                >
                                    <span className="flex-1">
                                        {option.label}
                                    </span>
                                </DropdownMenuCheckboxItem>
                            ))
                        ) : (
                            <div className="px-2 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                No skill facets yet
                            </div>
                        )}
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}

function SalaryRangeField({
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
    return (
        <div className="space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{label}</Label>
            <div className="grid grid-cols-2 gap-2">
                <Input
                    type="number"
                    value={salaryMin ?? ''}
                    onChange={(event) => onSalaryMinChange(event.target.value)}
                    placeholder="Min"
                    className={controlClassName}
                />
                <Input
                    type="number"
                    value={salaryMax ?? ''}
                    onChange={(event) => onSalaryMaxChange(event.target.value)}
                    placeholder="Max"
                    className={controlClassName}
                />
            </div>
        </div>
    );
}

function formatFacetOptionLabel(item: JobFacetItem): string {
    return `${getFacetLabel(item)} (${item.count})`;
}

function buildFacetSelectOptions(
    items: JobFacetItem[],
    emptyLabel: string,
    selectedValue: string,
): Array<{ label: string; value: string }> {
    const options = uniqueFacetItems(items).map((item) => ({
        label: formatFacetOptionLabel(item),
        value: item.value,
    }));

    if (
        selectedValue.trim() !== ''
        && ! options.some((option) => option.value === selectedValue)
    ) {
        options.unshift({
            label: formatSlugLabel(selectedValue),
            value: selectedValue,
        });
    }

    return [
        { label: emptyLabel, value: '' },
        ...options,
    ];
}

function buildFilterChips(values: DraftFilters): FilterChip[] {
    const chips: FilterChip[] = [];

    if (values.q.trim() !== '') {
        chips.push({
            key: 'q',
            filterKey: 'q',
            label: 'Keyword',
            value: values.q,
        });
    }

    if (values.location.trim() !== '') {
        chips.push({
            key: 'location',
            filterKey: 'location',
            label: 'Location',
            value: values.location,
        });
    }

    if (values.category.trim() !== '') {
        chips.push({
            key: 'category',
            filterKey: 'category',
            label: 'Category',
            value: formatSlugLabel(values.category),
        });
    }

    values.skills.forEach((skill) => {
        chips.push({
            key: `skill-${skill}`,
            filterKey: 'skills',
            label: 'Skill',
            value: skill,
            skillValue: skill,
        });
    });

    if (values.salary_min !== null || values.salary_max !== null) {
        chips.push({
            key: 'salary_range',
            filterKey: 'salary_range',
            label: 'Salary',
            value: formatSalaryChip(values.salary_min, values.salary_max),
        });
    }

    if (values.work_model.trim() !== '') {
        chips.push({
            key: 'work_model',
            filterKey: 'work_model',
            label: 'Work Model',
            value: formatWorkModelLabel(values.work_model),
        });
    }

    if (values.experience_level.trim() !== '') {
        chips.push({
            key: 'experience_level',
            filterKey: 'experience_level',
            label: 'Experience',
            value: formatExperienceLevelLabel(values.experience_level),
        });
    }

    if (values.job_type.trim() !== '') {
        chips.push({
            key: 'job_type',
            filterKey: 'job_type',
            label: 'Job Type',
            value: formatJobTypeLabel(values.job_type),
        });
    }

    return chips;
}

function formatSalaryChip(min: number | null, max: number | null): string {
    return formatSalaryRange({
        min,
        max,
        currency: null,
        is_visible: true,
    });
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
