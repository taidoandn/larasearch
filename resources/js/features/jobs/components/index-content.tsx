import type {
    JobFilters as JobFiltersState,
    JobResultsPayload,
} from '@/features/jobs/types';
import { JobsResultsList } from './index/results-list';
import { JobsResultsToolbar } from './index/results-toolbar';
import { JobsFilters } from './index/search-filters';

export function JobsIndexContent({
    results,
    filters,
}: {
    results: JobResultsPayload;
    filters: JobFiltersState;
}) {
    return (
        <div className="flex flex-1 flex-col bg-zinc-50/50 dark:bg-zinc-950">
            <div className="border-b border-zinc-200 bg-white/95 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <JobsFilters filters={filters} />
            </div>

            <JobsResultsToolbar
                total={results.pagination.total}
                sort={results.sort}
                filters={filters}
            />
            <JobsResultsList items={results.items} pagination={results.pagination} filters={filters} />
        </div>
    );
}
