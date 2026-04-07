import { Head, usePage } from '@inertiajs/react';
import {
    FeatureHighlightsSection,
    FinalCtaSection,
    HeroSection,
    LandingFooter,
    LandingHeader,
    QueryExamplesSection,
    SearchPreviewSection,
    TechnicalSection,
} from '@/features/landing';
import { login, register } from '@/routes';
import { index as jobsIndex } from '@/routes/jobs';

export default function LandingPage({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;
    const isAuthenticated = !!auth.user;
    const primaryHref = isAuthenticated ? jobsIndex() : login();
    const secondaryHref = canRegister ? register() : login();

    return (
        <>
            <Head title="Larasearch" />

            <div className="min-h-screen bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-50">
                <LandingHeader
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

                <LandingFooter />
            </div>
        </>
    );
}
