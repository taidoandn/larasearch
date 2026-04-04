import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { SearchHeader } from '@/components/search-header';
import type { SearchLayoutSection } from '@/components/search-header';
import type { AppLayoutProps } from '@/types';

type SearchLayoutProps = AppLayoutProps & {
    section?: SearchLayoutSection;
};

export default function SearchLayout({
    children,
    breadcrumbs = [],
}: SearchLayoutProps) {
    return (
        <AppShell variant="search">
            <SearchHeader breadcrumbs={breadcrumbs} />
            <AppContent variant="search">{children}</AppContent>
        </AppShell>
    );
}
