import { DetailScreen } from '@/features/jobs/screens';
import type { JobDetailItem, JobResultItem, JobSearchContext } from '@/features/jobs/types';

type Props = {
    job: JobDetailItem;
    relatedJobs: JobResultItem[];
    searchContext: JobSearchContext;
};

export default function JobShowPage({ job, relatedJobs, searchContext }: Props) {
    return <DetailScreen job={job} relatedJobs={relatedJobs} searchContext={searchContext} />;
}
