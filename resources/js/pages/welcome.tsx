import { Head, usePage } from '@inertiajs/react';
import { FeatureHighlightsSection } from '@/components/marketing/feature-highlights';
import { FinalCtaSection } from '@/components/marketing/final-cta';
import { MarketingFooter } from '@/components/marketing/footer';
import { MarketingHeader } from '@/components/marketing/header';
import { HeroSection } from '@/components/marketing/hero';
import { QueryExamplesSection } from '@/components/marketing/query-examples';
import { SearchPreviewSection } from '@/components/marketing/search-preview';
import { TechnicalSection } from '@/components/marketing/technical-section';
import { login, register } from '@/routes';
import { searchResults } from '@/routes/larasearch';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;
    const isAuthenticated = !!auth.user;
    const primaryHref = isAuthenticated ? searchResults() : login();
    const secondaryHref = canRegister ? register() : login();

    return (
        <>
            <Head title="Larasearch" />

            <div className="min-h-screen bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-50">
                <MarketingHeader
                    isAuthenticated={isAuthenticated}
                    canRegister={canRegister}
                />

                <main>
                    <HeroSection ctaLabel="Search Jobs" ctaHref={primaryHref} />
                    <FeatureHighlightsSection />
                    <SearchPreviewSection />
                    <QueryExamplesSection />
                    <TechnicalSection />
                    <FinalCtaSection
                        ctaHref={canRegister ? secondaryHref : primaryHref}
                    />
                </main>

                <MarketingFooter />
            </div>
        </>
    );
}
