import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

export function CompanyAvatar({
    name,
    logoUrl,
    size = 'default',
    className,
}: {
    name: string;
    logoUrl?: string | null;
    size?: 'compact' | 'default';
    className?: string;
}) {
    const getInitials = useInitials();
    const isCompact = size === 'compact';

    return (
        <Avatar
            className={cn(
                'overflow-hidden border border-slate-200 bg-white shadow-[0_18px_32px_-28px_rgba(15,23,42,0.55)]',
                isCompact ? 'size-12 rounded-2xl' : 'size-14 rounded-3xl',
                className,
            )}
        >
            {logoUrl ? <AvatarImage src={logoUrl} alt={name} className="object-cover" /> : null}
            <AvatarFallback
                className={cn(
                    'bg-slate-100 font-semibold text-primary',
                    isCompact ? 'rounded-2xl text-sm' : 'rounded-3xl text-base',
                )}
            >
                {getInitials(name)}
            </AvatarFallback>
        </Avatar>
    );
}
