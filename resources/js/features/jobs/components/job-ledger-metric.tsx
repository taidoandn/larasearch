import { sectionLabelClassName } from '@/features/jobs/utils';
import { cn } from '@/lib/utils';

export function JobLedgerMetric({
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
