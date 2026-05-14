import { SearchScreen } from '@/features/jobs/screens';
import type { JobFilters, JobResultsPayload } from '@/features/jobs/types';

export default function JobsIndexPage({
    results,
    filters,
}: {
    results: JobResultsPayload;
    filters: JobFilters;
}) {
    return <SearchScreen results={results} filters={filters} />;
}
