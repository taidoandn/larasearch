import { Link } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import type { ComponentProps } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { LandingSection } from './section-shell';

export function HeroSection({
    ctaLabel,
    ctaHref,
}: {
    ctaLabel: string;
    ctaHref: ComponentProps<typeof Link>['href'];
}) {
    const [query, setQuery] = useState('');

    return (
        <LandingSection className="px-6 pt-10 pb-12 sm:px-8 lg:px-10 lg:pt-8 lg:pb-14">
            <div className="mx-auto flex max-w-3xl flex-col items-center gap-7 text-center">
                <div className="space-y-4">
                    <p className="text-xs font-semibold tracking-[0.24em] text-zinc-400 uppercase dark:text-zinc-500">
                        Precision Search Workflow
                    </p>
                    <h1 className="text-5xl leading-none font-semibold tracking-[-0.05em] text-zinc-950 sm:text-6xl dark:text-zinc-50">
                        Find relevant jobs faster.
                    </h1>
                    <p className="mx-auto max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                        High-performance job search powered by Elasticsearch.
                        Precision discovery for technical talent.
                    </p>
                </div>

                <div className="w-full max-w-136">
                    <div className="bg-white p-2 dark:bg-zinc-900">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-stretch">
                            <div className="flex-1 bg-zinc-50 px-3 dark:bg-zinc-950">
                                <label className="flex h-10 items-center gap-2 text-left">
                                    <Search className="size-3.5 text-zinc-400 dark:text-zinc-500" />
                                    <Input
                                        value={query}
                                        placeholder="Search roles, skills, companies..."
                                        onChange={(event) =>
                                            setQuery(event.target.value)
                                        }
                                        className="h-auto border-0 bg-transparent px-0 py-0 text-sm text-zinc-600 shadow-none ring-0 focus-visible:ring-0 dark:text-zinc-300"
                                    />
                                </label>
                            </div>

                            <Button
                                asChild
                                className="h-10 rounded-none bg-primary px-4 text-xs font-semibold tracking-[0.2em] text-primary-foreground uppercase shadow-none hover:bg-primary/90 sm:self-stretch"
                            >
                                <Link href={ctaHref}>{ctaLabel}</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </LandingSection>
    );
}
