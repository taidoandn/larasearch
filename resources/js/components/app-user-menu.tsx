import { usePage } from '@inertiajs/react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

type AppUserMenuProps = {
    align?: 'center' | 'end' | 'start';
    avatarClassName?: string;
    avatarFallbackClassName?: string;
    triggerClassName?: string;
};

export function AppUserMenu({
    align = 'end',
    avatarClassName,
    avatarFallbackClassName,
    triggerClassName,
}: AppUserMenuProps) {
    const {
        props: { auth },
    } = usePage();
    const getInitials = useInitials();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className={cn(
                        'size-10 rounded-full p-1 text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-zinc-50',
                        triggerClassName,
                    )}
                >
                    <Avatar
                        className={cn(
                            'size-8 overflow-hidden rounded-full',
                            avatarClassName,
                        )}
                    >
                        <AvatarImage
                            src={auth.user.avatar}
                            alt={auth.user.name}
                        />
                        <AvatarFallback
                            className={cn(
                                'rounded-full bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white',
                                avatarFallbackClassName,
                            )}
                        >
                            {getInitials(auth.user.name)}
                        </AvatarFallback>
                    </Avatar>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align={align}>
                <UserMenuContent user={auth.user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
