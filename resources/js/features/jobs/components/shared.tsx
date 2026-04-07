import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export const sectionLabelClassName =
    'text-[10px] font-semibold uppercase tracking-[0.24em] text-zinc-400 dark:text-zinc-500';

export function DetailSection({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="space-y-8">
            <h2 className="border-b border-zinc-200 pb-4 text-3xl font-black tracking-tight text-zinc-950 dark:border-zinc-800 dark:text-zinc-50">
                {title}
            </h2>
            {children}
        </section>
    );
}

export function LedgerMetric({
    label,
    value,
    align = 'left',
}: {
    label: string;
    value: string;
    align?: 'left' | 'right';
}) {
    return (
        <div className={cn('space-y-1', align === 'right' && 'text-right')}>
            <p className={sectionLabelClassName}>{label}</p>
            <p className="font-mono text-xs font-semibold tracking-tight text-zinc-700 dark:text-zinc-300">
                {value}
            </p>
        </div>
    );
}

export function MetadataRow({ icon, text }: { icon: ReactNode; text: string }) {
    return (
        <div className="flex items-center gap-3">
            <span className="text-zinc-400 dark:text-zinc-500">{icon}</span>
            <span>{text}</span>
        </div>
    );
}
