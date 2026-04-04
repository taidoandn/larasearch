import { Bolt, SlidersHorizontal, Sparkles } from 'lucide-react';
import { marketingFeatures } from '@/components/marketing/content';
import { MarketingSection } from '@/components/marketing/section-shell';

const icons = [Bolt, Sparkles, SlidersHorizontal] as const;

export function FeatureHighlightsSection() {
    return (
        <MarketingSection
            id="features"
            className="px-6 py-8 sm:px-8 lg:px-10 lg:py-10"
        >
            <div className="grid gap-px bg-zinc-200/80 md:grid-cols-3 dark:bg-zinc-800/80">
                {marketingFeatures.map((feature, index) => {
                    const Icon = icons[index];

                    return (
                        <article
                            key={feature.title}
                            className="flex min-h-32 flex-col gap-4 bg-zinc-100/70 px-5 py-5 dark:bg-zinc-900/60"
                        >
                            <div className="flex items-center gap-2">
                                <Icon className="size-3.5 text-indigo-600 dark:text-indigo-300" />
                                <span className="text-sm font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                                    {feature.title}
                                </span>
                            </div>
                            <p className="max-w-xs text-sm leading-5 text-zinc-500 dark:text-zinc-400">
                                {feature.description}
                            </p>
                        </article>
                    );
                })}
            </div>
        </MarketingSection>
    );
}
