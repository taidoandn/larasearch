import { useState } from 'react';
import { getSearchJobById } from '@/components/search/mock-search-data';
import { ResultsList } from '@/components/search-results/results-list';
import { ResultsToolbar } from '@/components/search-results/results-toolbar';
import { SearchFilters } from '@/components/search-results/search-filters';
import { SearchSummarySheet } from '@/components/search-results/summary-panel';

export function SearchResultsContent() {
    const [selectedJobId, setSelectedJobId] = useState<string | null>(null);

    const selectedJob = selectedJobId
        ? (getSearchJobById(selectedJobId) ?? null)
        : null;

    return (
        <div className="flex flex-1 flex-col bg-zinc-50/50 dark:bg-zinc-950">
            <div className="border-b border-zinc-200 bg-white/95 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <SearchFilters />
            </div>

            <ResultsToolbar />
            <ResultsList
                selectedJobId={selectedJobId}
                onSelectJob={setSelectedJobId}
            />

            <SearchSummarySheet
                job={selectedJob}
                open={!!selectedJob}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedJobId(null);
                    }
                }}
            />
        </div>
    );
}
