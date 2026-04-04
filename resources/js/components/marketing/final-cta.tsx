import { Link } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import { MarketingSection } from '@/components/marketing/section-shell';
import { Button } from '@/components/ui/button';

export function FinalCtaSection({
    ctaHref,
}: {
    ctaHref: ComponentProps<typeof Link>['href'];
}) {
    return (
        <MarketingSection className="px-6 py-12 sm:px-8 lg:px-10 lg:py-14">
            <div className="flex flex-col items-center gap-4 text-center">
                <h2 className="max-w-2xl text-[1.9rem] font-semibold tracking-[-0.03em] text-zinc-950 dark:text-zinc-50">
                    Ready to upgrade your search?
                </h2>
                <Button
                    asChild
                    className="h-8 rounded-none bg-indigo-700 px-5 text-[9px] font-semibold tracking-[0.18em] text-white uppercase shadow-none hover:bg-indigo-800"
                >
                    <Link href={ctaHref}>Start exploring jobs now</Link>
                </Button>
            </div>
        </MarketingSection>
    );
}
