import { BadgeCheck } from 'lucide-react';
import { CompanyAvatar } from '@/features/jobs/components/shared/company-avatar';
import { cn } from '@/lib/utils';

export function CompanyIdentity({
    name,
    logoUrl,
    meta,
    verified = false,
    size = 'default',
    layout = 'inline',
    className,
}: {
    name: string;
    logoUrl?: string | null;
    meta?: string | null;
    verified?: boolean;
    size?: 'compact' | 'default';
    layout?: 'inline' | 'stacked';
    className?: string;
}) {
    const isCompact = size === 'compact';
    const isStacked = layout === 'stacked';

    return (
        <div
            className={cn(
                'min-w-0',
                isStacked ? 'flex flex-col items-start gap-4' : 'flex items-start gap-3',
                className,
            )}
        >
            <CompanyAvatar name={name} logoUrl={logoUrl} size={size} />

            <div className={cn('min-w-0 space-y-1', isStacked ? 'w-full' : null)}>
                <div
                    className={cn(
                        'flex items-center gap-1.5 font-semibold tracking-tight text-slate-900',
                        isCompact ? 'text-sm' : '',
                        isStacked ? 'text-2xl' : 'text-base',
                    )}
                >
                    <span className="min-w-0 truncate">{name}</span>
                    {verified ? <BadgeCheck className="size-4 shrink-0 text-primary" /> : null}
                </div>

                {meta ? (
                    <p
                        className={cn(
                            'text-slate-500',
                            isStacked ? 'text-base leading-6 text-balance' : 'truncate text-sm',
                        )}
                    >
                        {meta}
                    </p>
                ) : null}
            </div>
        </div>
    );
}
