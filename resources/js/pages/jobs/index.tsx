import { Head } from '@inertiajs/react';
import { JobsIndexContent } from '@/features/jobs';
import type { JobFilters, JobResultsPayload } from '@/features/jobs';
import SearchLayout from '@/layouts/search-layout';
import { index as jobsIndex } from '@/routes/jobs';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Larasearch',
        href: jobsIndex(),
    },
    {
        title: 'Jobs',
        href: jobsIndex(),
    },
];

export default function JobsIndexPage({
    results,
    filters,
}: {
    results: JobResultsPayload;
    filters: JobFilters;
}) {
    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title="Larasearch Jobs" />
            <JobsIndexContent results={results} filters={filters} />
        </SearchLayout>
    );
}
