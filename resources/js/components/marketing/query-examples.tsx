import { exampleQueries } from '@/components/marketing/content';
import {
    MarketingSection,
    SectionEyebrow,
} from '@/components/marketing/section-shell';

export function QueryExamplesSection() {
    return (
        <MarketingSection
            id="queries"
            className="px-6 py-10 sm:px-8 lg:px-10 lg:py-12"
        >
            <div className="space-y-4">
                <SectionEyebrow>Natural Queries</SectionEyebrow>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {exampleQueries.map((query, index) => (
                        <article
                            key={query}
                            className="min-h-22 bg-white px-4 py-3 dark:bg-zinc-900"
                        >
                            <p className="font-mono text-xs tracking-[0.2em] text-zinc-400 uppercase dark:text-zinc-500">
                                Query 0{index + 1}
                            </p>
                            <p className="mt-2 text-sm leading-5 text-primary dark:text-accent-foreground">
                                {query}
                            </p>
                        </article>
                    ))}
                </div>
            </div>
        </MarketingSection>
    );
}
