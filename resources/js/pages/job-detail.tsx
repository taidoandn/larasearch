import { Head } from '@inertiajs/react';
import { JobDetailContent } from '@/components/job-detail';
import {
    getSearchJobById,
    searchResults,
} from '@/components/search/mock-search-data';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type Props = {
    jobId: string;
};

export default function JobDetailPage({ jobId }: Props) {
    const job = getSearchJobById(jobId) ?? searchResults[0];

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Larasearch', href: '/search' },
        { title: 'Search Results', href: '/search' },
        { title: job.title, href: `/search/jobs/${job.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={job.title} />
            <JobDetailContent job={job} />
        </AppLayout>
    );
}
