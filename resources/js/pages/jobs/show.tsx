import { Head } from '@inertiajs/react';
import { getSearchJobById, JobShowContent, searchResults } from '@/features/jobs';
import SearchLayout from '@/layouts/search-layout';
import { index as jobsIndex, show as jobsShow } from '@/routes/jobs';
import type { BreadcrumbItem } from '@/types';

type Props = {
    jobId: string;
};

export default function JobShowPage({ jobId }: Props) {
    const job = getSearchJobById(jobId) ?? searchResults[0];

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Larasearch', href: jobsIndex() },
        { title: 'Jobs', href: jobsIndex() },
        { title: job.title, href: jobsShow(job.id) },
    ];

    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title={job.title} />
            <JobShowContent job={job} />
        </SearchLayout>
    );
}
