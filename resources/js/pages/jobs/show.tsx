import { Head } from '@inertiajs/react';
import { JobDetailInfo } from '@/features/jobs/components/job-detail-info';
import { JobHeader } from '@/features/jobs/components/job-header';
import type {
    JobDetailItem,
    JobResultItem,
    JobSearchContext,
} from '@/features/jobs/types';
import SearchLayout from '@/layouts/search-layout';
import { index as jobsIndex, show as jobsShow } from '@/routes/jobs';
import type { BreadcrumbItem } from '@/types';

type Props = {
    job: JobDetailItem;
    relatedJobs: JobResultItem[];
    searchContext: JobSearchContext;
};

export default function JobShowPage({
    job,
    relatedJobs,
    searchContext,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Larasearch',
            href: jobsIndex({ query: searchContext.index_query }),
        },
        {
            title: 'Jobs',
            href: jobsIndex({ query: searchContext.index_query }),
        },
        {
            title: job.title,
            href: jobsShow(job.slug, { query: searchContext.index_query }),
        },
    ];

    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title={job.title} />
            <div className="bg-white dark:bg-zinc-950">
                <JobHeader job={job} />
                <JobDetailInfo
                    job={job}
                    relatedJobs={relatedJobs}
                    searchContext={searchContext}
                />
            </div>
        </SearchLayout>
    );
}
