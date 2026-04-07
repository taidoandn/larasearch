import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function LandingSection({
    id,
    className,
    children,
}: {
    id?: string;
    className?: string;
    children: ReactNode;
}) {
    return (
        <section
            id={id}
            className={cn('px-4 py-16 sm:px-6 lg:px-8 lg:py-24', className)}
        >
            <div className="mx-auto w-full max-w-6xl">{children}</div>
        </section>
    );
}

export function SectionEyebrow({ children }: { children: ReactNode }) {
    return (
        <p className="text-[10px] font-semibold tracking-[0.26em] text-zinc-400 uppercase dark:text-zinc-500">
            {children}
        </p>
    );
}
