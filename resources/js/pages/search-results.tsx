import { Head } from '@inertiajs/react';
import { SearchResultsContent } from '@/components/search-results';
import type {
    SearchFilters,
    SearchResultsPayload,
} from '@/components/search-results/types';
import SearchLayout from '@/layouts/search-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Larasearch',
        href: '/search',
    },
    {
        title: 'Search Results',
        href: '/search',
    },
];

export default function SearchResultsPage({
    results,
    filters,
}: {
    results: SearchResultsPayload;
    filters: SearchFilters;
}) {
    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title="Larasearch Search Results" />
            <SearchResultsContent results={results} filters={filters} />
        </SearchLayout>
    );
}
