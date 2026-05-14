import { Pagination } from '@/components/pagination';
import type { JobResultItem, JobResultsPayload } from '@/features/jobs/types';
import { JobResultRow } from './job-result-row';
import { EmptyState, ResultsSkeleton } from './results-list-states';

export function ResultsList({
    items,
    activeSkills,
    pagination,
    isRefreshing,
    selectedJobId,
    onSelectJob,
    onPageChange,
    onReset,
}: {
    items: JobResultItem[];
    activeSkills: string[];
    pagination: JobResultsPayload['pagination'];
    isRefreshing: boolean;
    selectedJobId: number | null;
    onSelectJob: (job: JobResultItem) => void;
    onPageChange: (page: number) => void;
    onReset: () => void;
}) {
    if (isRefreshing && items.length === 0) {
        return <ResultsSkeleton />;
    }

    return (
        <div className="bg-transparent">
            {items.length === 0 ? (
                <EmptyState onReset={onReset} />
            ) : (
                <div className="space-y-5 bg-secondary px-4 py-4 sm:px-6 sm:py-6">
                    {items.map((job) => (
                        <JobResultRow
                            key={job.id}
                            job={job}
                            activeSkills={activeSkills}
                            selected={selectedJobId === job.id}
                            onSelect={() => onSelectJob(job)}
                        />
                    ))}
                </div>
            )}

            <div className="flex flex-col items-center gap-3 px-4 py-8 sm:px-6 sm:py-10">
                {pagination.total_pages > 1 ? (
                    <div className="rounded-[28px] bg-card px-5 py-4 shadow-[0_20px_36px_-30px_rgba(0,74,198,0.12)]">
                        <Pagination
                            page={pagination.page}
                            totalPages={pagination.total_pages}
                            disabled={isRefreshing}
                            onPageChange={onPageChange}
                        />
                    </div>
                ) : null}
                <p className="font-mono text-[10px] tracking-[0.28em] text-muted-foreground/70 uppercase">
                    {pagination.total === 0
                        ? 'Showing 0 results'
                        : `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results`}
                </p>
            </div>
        </div>
    );
}
