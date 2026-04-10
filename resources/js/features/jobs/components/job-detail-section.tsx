import type { ReactNode } from 'react';

export function JobDetailSection({
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
