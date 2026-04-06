import { ResultsList } from '@/components/search-results/results-list';
import { ResultsToolbar } from '@/components/search-results/results-toolbar';
import { SearchFilters } from '@/components/search-results/search-filters';
import type {
    SearchFilters as SearchFiltersState,
    SearchResultsPayload,
} from '@/components/search-results/types';

export function SearchResultsContent({
    results,
    filters,
}: {
    results: SearchResultsPayload;
    filters: SearchFiltersState;
}) {
    return (
        <div className="flex flex-1 flex-col bg-zinc-50/50 dark:bg-zinc-950">
            <div className="border-b border-zinc-200 bg-white/95 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/90">
                <SearchFilters filters={filters} />
            </div>

            <ResultsToolbar
                total={results.pagination.total}
                sort={results.sort}
                filters={filters}
            />
            <ResultsList items={results.items} pagination={results.pagination} filters={filters} />
        </div>
    );
}
