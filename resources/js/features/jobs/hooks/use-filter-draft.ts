import { useEffect, useRef, useState } from 'react';
import type { JobFilters } from '@/features/jobs/types';

type UseFilterDraftParams = {
    filters: JobFilters;
    onApply: (nextFilters: JobFilters) => void;
};

export function useFilterDraft({ filters, onApply }: UseFilterDraftParams) {
    const [values, setValues] = useState<JobFilters>(filters);
    const hasMountedQueryRef = useRef(false);
    const hasMountedSalaryRef = useRef(false);

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

    const updateDraftValue = <K extends keyof JobFilters>(key: K, value: JobFilters[K]) => {
        setValues((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const applyImmediately = (nextValues: JobFilters) => {
        setValues(nextValues);
        onApply({
            ...nextValues,
            page: 1,
        });
    };

    return {
        values,
        updateDraftValue,
        applyImmediately,
    };
}
