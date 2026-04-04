import {
    technicalHighlights,
    technicalPreviewLines,
} from '@/components/marketing/content';
import { MarketingSection } from '@/components/marketing/section-shell';

export function TechnicalSection() {
    return (
        <MarketingSection className="bg-zinc-950 px-6 py-10 text-zinc-50 sm:px-8 lg:px-10 lg:py-12">
            <div className="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
                <div className="space-y-8">
                    <div className="space-y-4">
                        <p className="text-xs font-semibold tracking-[0.2em] text-zinc-500 uppercase">
                            Technical Foundation
                        </p>
                        <h2 className="max-w-xl text-4xl font-semibold tracking-[-0.03em] text-white">
                            Architected for speed.
                        </h2>
                    </div>

                    <div className="space-y-5">
                        {technicalHighlights.map((item, index) => (
                            <article
                                key={item.title}
                                className="grid gap-3 sm:grid-cols-[24px_1fr]"
                            >
                                <div className="flex size-6 items-center justify-center bg-zinc-900 font-mono text-[8px] text-zinc-400">
                                    {String(index + 1).padStart(2, '0')}
                                </div>
                                <div className="space-y-2">
                                    <h3 className="text-sm font-semibold tracking-tight text-white">
                                        {item.title}
                                    </h3>
                                    <p className="max-w-lg text-sm leading-5 text-zinc-400">
                                        {item.description}
                                    </p>
                                </div>
                            </article>
                        ))}
                    </div>
                </div>

                <div className="space-y-3 bg-zinc-900 p-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.2em] text-zinc-500 uppercase">
                                Query execution trace
                            </p>
                        </div>
                        <p className="font-mono text-xs tracking-[0.22em] text-zinc-500 uppercase">
                            System Insight
                        </p>
                    </div>

                    <pre className="overflow-x-auto bg-zinc-950 p-5 font-mono text-xs leading-5 text-zinc-300">
                        <code>{technicalPreviewLines.join('\n')}</code>
                    </pre>
                </div>
            </div>
        </MarketingSection>
    );
}
