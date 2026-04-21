import { sectionLabelClassName } from '@/features/jobs/utils';
import { cn } from '@/lib/utils';

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
            <p className="font-mono text-sm font-semibold tracking-tight text-slate-700 dark:text-zinc-300">
                {value}
            </p>
        </div>
    );
}
