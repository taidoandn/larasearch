import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
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
import { sectionLabelClassName } from '@/features/jobs/components/shared';
import type { JobFilters as JobFiltersState } from '@/features/jobs/types';
import { index as jobsIndex } from '@/routes/jobs';

const controlClassName =
    'h-7 rounded-none border-0 bg-transparent px-0 py-0 text-sm leading-none font-medium text-zinc-900 shadow-none ring-0 focus-visible:ring-0 dark:bg-transparent dark:text-zinc-100';

const selectControlClassName = `${controlClassName} [&_[data-slot=select-value]]:flex [&_[data-slot=select-value]]:items-center [&_[data-slot=select-value]]:leading-none [&_[data-slot=select-value]]:min-h-0`;
const workModelOptions = [
    { label: 'All Models', value: '' },
    { label: 'Remote', value: 'remote' },
    { label: 'Hybrid', value: 'hybrid' },
    { label: 'Onsite', value: 'onsite' },
];

const experienceLevelOptions = [
    { label: 'All Levels', value: '' },
    { label: 'Entry', value: 'entry' },
    { label: 'Mid', value: 'mid' },
    { label: 'Senior', value: 'senior' },
    { label: 'Lead', value: 'lead' },
];

const jobTypeOptions = [
    { label: 'All Types', value: '' },
    { label: 'Full-Time', value: 'full-time' },
    { label: 'Contract', value: 'contract' },
    { label: 'Internship', value: 'internship' },
];

const chipLabelMap: Record<string, string> = {
    q: 'Keyword',
    location: 'Location',
    job_type: 'Job Type',
    work_model: 'Work Model',
    experience_level: 'Experience',
};

export function JobsFilters({
    filters,
}: {
    filters: JobFiltersState;
}) {
    const [values, setValues] = useState<JobFiltersState>(filters);

    useEffect(() => {
        setValues(filters);
    }, [filters]);

    const chips = Object.entries(values)
        .filter(([key]) =>
            ['q', 'location', 'job_type', 'work_model', 'experience_level'].includes(key),
        )
        .filter(([, value]) => typeof value === 'string' && value.trim().length > 0)
        .map(([id, value]) => ({
            id,
            label: chipLabelMap[id] ?? id,
            value: String(value),
        }));

    const updateValue = (id: keyof JobFiltersState, value: string) => {
        setValues((current) => ({
            ...current,
            [id]: value,
        }));
    };

    const submitFilters = () => {
        router.get(jobsIndex.url(), {
            ...values,
            page: 1,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        const nextValues = {
            ...filters,
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
            per_page: 20,
        };

        setValues(nextValues);

        router.get(jobsIndex.url(), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <div className="space-y-5 px-4 py-4 sm:px-6">
            <div className="flex flex-col gap-3 xl:flex-row xl:items-stretch">
                <div className="grid flex-1 gap-px overflow-hidden border border-zinc-200 bg-zinc-200 md:grid-cols-2 xl:grid-cols-5 dark:border-zinc-800 dark:bg-zinc-800">
                    <TextFilterField
                        label="Keyword"
                        value={values.q}
                        onValueChange={(value) => updateValue('q', value)}
                    />
                    <TextFilterField
                        label="Location"
                        value={values.location}
                        onValueChange={(value) => updateValue('location', value)}
                    />
                    <SelectFilterField
                        label="Work Model"
                        value={values.work_model}
                        options={workModelOptions}
                        onValueChange={(value) => updateValue('work_model', value)}
                    />
                    <SelectFilterField
                        label="Experience"
                        value={values.experience_level}
                        options={experienceLevelOptions}
                        onValueChange={(value) => updateValue('experience_level', value)}
                    />
                    <SelectFilterField
                        label="Job Type"
                        value={values.job_type}
                        options={jobTypeOptions}
                        onValueChange={(value) => updateValue('job_type', value)}
                    />
                </div>

                <Button
                    onClick={submitFilters}
                    className="h-auto rounded-none bg-primary px-6 py-3 text-[11px] font-semibold tracking-[0.22em] text-primary-foreground uppercase shadow-none hover:bg-primary/90"
                >
                    Search
                </Button>
            </div>

            <div className="flex flex-wrap items-center gap-2">
                {chips.map((chip) => (
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

function TextFilterField({
    label,
    value,
    onValueChange,
}: {
    label: string;
    value: string;
    onValueChange: (value: string) => void;
}) {
    return (
        <div className="space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{label}</Label>
            <Input
                value={value}
                onChange={(event) => onValueChange(event.target.value)}
                className={controlClassName}
            />
        </div>
    );
}

function SelectFilterField({
    label,
    value,
    options,
    onValueChange,
}: {
    label: string;
    value: string;
    options: Array<{ label: string; value: string }>;
    onValueChange: (value: string) => void;
}) {
    return (
        <div className="space-y-1 bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <Label className={sectionLabelClassName}>{label}</Label>

            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger className={`${selectControlClassName} w-full`}>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent
                    align="start"
                    className="rounded-none border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950"
                >
                    {options.map((option) => (
                        <SelectItem key={option.label} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
