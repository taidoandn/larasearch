import type { ReactNode } from 'react';

export function DetailSection({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="space-y-6">
            <h2 className="text-sm font-semibold tracking-tight text-slate-900">
                {title}
            </h2>
            {children}
        </section>
    );
}
