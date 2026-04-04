import { Head } from '@inertiajs/react';
import { SearchResultsContent } from '@/components/search-results';
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

export default function SearchResultsPage() {
    return (
        <SearchLayout breadcrumbs={breadcrumbs}>
            <Head title="Larasearch Search Results" />
            <SearchResultsContent />
        </SearchLayout>
    );
}
