import { useEffect, useMemo, useRef, useState } from 'react';
import { useSuggestions } from '@/features/jobs/hooks';
import type { JobFilters as JobFiltersState, JobResultsPayload } from '@/features/jobs/types';
import {
    buildFacetChecklistOptions,
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatWorkModelLabel,
} from '@/features/jobs/utils';
import { DropdownFacetFilterField, SalaryRangeField, TextFilterField } from './filter-fields';

type JobsFilterProps = {
    filters: JobFiltersState;
    facets: JobResultsPayload['facets'];
    isRefreshing: boolean;
    onApply: (nextFilters: JobFiltersState) => void;
};

type DraftFilters = JobFiltersState;

export function Filters({ filters, facets, onApply }: JobsFilterProps) {
    const [values, setValues] = useState<DraftFilters>(filters);
    const hasMountedQueryRef = useRef(false);
    const hasMountedSalaryRef = useRef(false);
    const {
        suggestions,
        isSuggesting,
        isSuggestionsOpen,
        activeSuggestionIndex,
        setActiveSuggestionIndex,
        openSuggestions,
        closeSuggestions,
        clearSuggestions,
    } = useSuggestions(values.q);

    useEffect(() => {
        setValues(filters);
    }, [filters]);

    useEffect(() => {
        if (!hasMountedQueryRef.current) {
            hasMountedQueryRef.current = true;

            return;
        }

        if (values.q === filters.q) {
            return;
        }

        const timeoutId = window.setTimeout(() => {
            onApply({
                ...values,
                page: 1,
            });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [filters.q, onApply, values]);

    useEffect(() => {
        if (!hasMountedSalaryRef.current) {
            hasMountedSalaryRef.current = true;

            return;
        }

        if (values.salary_min === filters.salary_min && values.salary_max === filters.salary_max) {
            return;
        }

        const timeoutId = window.setTimeout(() => {
            onApply({
                ...values,
                page: 1,
            });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [filters.salary_max, filters.salary_min, onApply, values]);

    const locationOptions = useMemo(
        () => buildFacetChecklistOptions(facets.locations, filters.location, (value) => value),
        [facets.locations, filters.location],
    );

    const categoryOptions = useMemo(
        () => buildFacetChecklistOptions(facets.categories, filters.category, (value) => value),
        [facets.categories, filters.category],
    );

    const jobTypeOptions = useMemo(
        () => buildFacetChecklistOptions(facets.job_types, filters.job_type, formatJobTypeLabel),
        [facets.job_types, filters.job_type],
    );

    const skillOptions = useMemo(
        () => buildFacetChecklistOptions(facets.skills, filters.skills, (value) => value),
        [facets.skills, filters.skills],
    );

    const workModelOptions = useMemo(
        () =>
            buildFacetChecklistOptions(
                facets.work_models,
                filters.work_model,
                formatWorkModelLabel,
            ),
        [facets.work_models, filters.work_model],
    );

    const experienceOptions = useMemo(
        () =>
            buildFacetChecklistOptions(
                facets.experience_levels,
                filters.experience_level,
                formatExperienceLevelLabel,
            ),
        [facets.experience_levels, filters.experience_level],
    );

    const updateDraftValue = <K extends keyof DraftFilters>(key: K, value: DraftFilters[K]) => {
        setValues((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const applyImmediately = (nextValues: DraftFilters) => {
        setValues(nextValues);
        onApply({
            ...nextValues,
            page: 1,
        });
    };

    return (
        <div className="space-y-4">
            <div className="grid gap-4">
                <TextFilterField
                    label="Keywords"
                    placeholder="Design, Engineering..."
                    value={values.q}
                    onValueChange={(value) => updateDraftValue('q', value)}
                    suggestions={suggestions}
                    isSuggesting={isSuggesting}
                    activeSuggestionIndex={activeSuggestionIndex}
                    onActiveSuggestionIndexChange={setActiveSuggestionIndex}
                    onSuggestionSelect={(suggestion) => {
                        const nextValues = {
                            ...values,
                            q: suggestion.label,
                        };

                        clearSuggestions();
                        applyImmediately(nextValues);
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

                <DropdownFacetFilterField
                    label="Location"
                    value={values.location}
                    options={locationOptions}
                    placeholder="Search locations..."
                    searchable
                    searchPlaceholder="Search locations..."
                    emptyMessage="No location facets yet"
                    onValueChange={(nextValues) =>
                        applyImmediately({
                            ...values,
                            location: nextValues,
                        })
                    }
                />

                <DropdownFacetFilterField
                    label="Category"
                    value={values.category}
                    options={categoryOptions}
                    onValueChange={(nextValues) =>
                        applyImmediately({
                            ...values,
                            category: nextValues,
                        })
                    }
                />

                <DropdownFacetFilterField
                    label="Job Type"
                    value={values.job_type}
                    options={jobTypeOptions}
                    onValueChange={(nextValues) =>
                        applyImmediately({
                            ...values,
                            job_type: nextValues,
                        })
                    }
                />

                <SalaryRangeField
                    label="Salary Range"
                    salaryMin={values.salary_min}
                    salaryMax={values.salary_max}
                    onSalaryMinChange={(value) =>
                        updateDraftValue('salary_min', value === '' ? null : Number(value))
                    }
                    onSalaryMaxChange={(value) =>
                        updateDraftValue('salary_max', value === '' ? null : Number(value))
                    }
                />

                <DropdownFacetFilterField
                    label="Work Model"
                    value={values.work_model}
                    options={workModelOptions}
                    onValueChange={(nextValues) =>
                        applyImmediately({
                            ...values,
                            work_model: nextValues,
                        })
                    }
                />

                <DropdownFacetFilterField
                    label="Experience"
                    value={values.experience_level}
                    options={experienceOptions}
                    onValueChange={(nextValues) =>
                        applyImmediately({
                            ...values,
                            experience_level: nextValues,
                        })
                    }
                />

                <DropdownFacetFilterField
                    label="Skills"
                    value={values.skills}
                    options={skillOptions}
                    placeholder="Add..."
                    emptyMessage="No skill facets yet"
                    onValueChange={(nextValues) =>
                        applyImmediately({
                            ...values,
                            skills: nextValues,
                        })
                    }
                />
            </div>
        </div>
    );
}
