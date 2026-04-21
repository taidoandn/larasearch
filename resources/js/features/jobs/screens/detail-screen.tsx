import { Head, Link } from '@inertiajs/react';
import {
    DetailInfo,
    Header,
    PageFooter,
    RelatedList,
    Sidebar,
    StickyCta,
} from '@/features/jobs/components/detail';
import type { JobDetailItem, JobResultItem, JobSearchContext } from '@/features/jobs/types';
import SearchLayout from '@/layouts/search-layout';
import { index as jobsIndex, show as jobsShow } from '@/routes/jobs';
import type { BreadcrumbItem } from '@/types';

type Props = {
    job: JobDetailItem;
    relatedJobs: JobResultItem[];
    searchContext: JobSearchContext;
};

export function DetailScreen({ job, relatedJobs, searchContext }: Props) {
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
            <div className="mx-auto flex w-full max-w-360 flex-col px-4 pb-24 sm:px-6 lg:px-8">
                <div className="pt-8 pb-14">
                    <Header job={job} />

                    <div className="mt-12 grid gap-12 lg:grid-cols-12 lg:items-start">
                        <div className="lg:col-span-8">
                            <DetailInfo job={job} />
                        </div>

                        <div className="lg:col-span-4">
                            <Sidebar job={job} />
                        </div>
                    </div>

                    <section className="mt-24 border-t border-slate-200 pt-16">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="font-display text-4xl font-extrabold tracking-tight text-slate-950">
                                    Similar Roles
                                </h2>
                                <p className="mt-2 text-base text-slate-500">
                                    Hand-picked matches based on your interest in this role.
                                </p>
                            </div>

                            <Link
                                href={jobsIndex({ query: searchContext.index_query })}
                                className="text-sm font-bold text-primary transition-colors hover:text-blue-700"
                            >
                                View all matching jobs
                            </Link>
                        </div>

                        <div className="mt-10">
                            <RelatedList
                                jobId={job.id}
                                relatedJobs={relatedJobs}
                                searchQuery={searchContext.index_query}
                            />
                        </div>
                    </section>
                </div>
            </div>

            <PageFooter />
            <StickyCta job={job} />
        </SearchLayout>
    );
}
