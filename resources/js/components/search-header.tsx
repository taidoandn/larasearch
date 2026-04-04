import { Link } from '@inertiajs/react';
import { Menu, Search, Settings } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { AppUserMenu } from '@/components/app-user-menu';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { searchResults } from '@/routes/larasearch';
import { edit as editProfile } from '@/routes/profile';
import type { BreadcrumbItem } from '@/types';

export type SearchLayoutSection = 'search' | 'settings';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const navItems = [
    {
        title: 'Explore',
        href: searchResults(),
    },
];

export function SearchHeader({ breadcrumbs = [] }: Props) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <>
            <header className="sticky top-0 z-40 border-b border-zinc-200/80 bg-white/95 backdrop-blur-sm dark:border-zinc-800/80 dark:bg-zinc-950/90">
                <div className="mx-auto flex h-16 w-full max-w-420 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <div className="lg:hidden">
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="h-9 w-9 rounded-full text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100"
                                >
                                    <Menu className="size-4" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent
                                side="left"
                                className="flex h-full w-72 flex-col bg-white px-0 dark:bg-zinc-950"
                            >
                                <SheetTitle className="sr-only">
                                    Search navigation
                                </SheetTitle>
                                <SheetHeader className="border-b border-zinc-200 px-5 pb-4 text-left dark:border-zinc-800">
                                    <Link
                                        href={home()}
                                        className="flex items-center"
                                    >
                                        <AppLogo />
                                    </Link>
                                </SheetHeader>
                                <div className="flex flex-1 flex-col justify-between px-3 py-4">
                                    <nav className="space-y-1">
                                        {navItems.map((item) => {
                                            const isActive =
                                                isCurrentOrParentUrl(item.href);

                                            return (
                                                <Button
                                                    key={item.title}
                                                    variant="ghost"
                                                    asChild
                                                    className={cn(
                                                        'h-11 w-full justify-start rounded-xl px-3 text-sm font-medium',
                                                        isActive &&
                                                            'bg-accent text-primary hover:bg-accent hover:text-primary dark:bg-accent/30 dark:text-accent-foreground dark:hover:bg-accent/30 dark:hover:text-accent-foreground',
                                                    )}
                                                >
                                                    <Link href={item.href}>
                                                        {item.title}
                                                    </Link>
                                                </Button>
                                            );
                                        })}
                                    </nav>

                                    <div className="space-y-2">
                                        <Button
                                            variant="ghost"
                                            asChild
                                            className="h-11 w-full justify-start rounded-xl px-3"
                                        >
                                            <Link href={searchResults()}>
                                                <Search className="size-4" />
                                                Search database
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            asChild
                                            className="h-11 w-full justify-start rounded-xl px-3"
                                        >
                                            <Link href={editProfile()}>
                                                <Settings className="size-4" />
                                                Account settings
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    <Link
                        href={home()}
                        prefetch
                        className="flex min-w-0 items-center"
                    >
                        <AppLogo />
                    </Link>

                    <nav className="ml-4 hidden lg:block">
                        <NavigationMenu>
                            <NavigationMenuList className="gap-1">
                                {navItems.map((item) => {
                                    const isActive = isCurrentOrParentUrl(
                                        item.href,
                                    );

                                    return (
                                        <NavigationMenuItem key={item.title}>
                                            <Link
                                                href={item.href}
                                                className={cn(
                                                    navigationMenuTriggerStyle(),
                                                    'h-10 rounded-full bg-transparent px-4 text-sm font-medium text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100',
                                                    isActive &&
                                                        'bg-accent text-primary hover:bg-accent hover:text-primary dark:bg-accent/30 dark:text-accent-foreground dark:hover:bg-accent/30 dark:hover:text-accent-foreground',
                                                )}
                                            >
                                                {item.title}
                                            </Link>
                                        </NavigationMenuItem>
                                    );
                                })}
                            </NavigationMenuList>
                        </NavigationMenu>
                    </nav>

                    <div className="ml-auto flex items-center gap-2">
                        <Link
                            href={searchResults()}
                            className="hidden h-10 min-w-56 items-center gap-3 rounded-full border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-500 transition-colors hover:border-zinc-300 hover:text-zinc-900 lg:flex dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-100"
                        >
                            <Search className="size-4" />
                            <span className="truncate">Search database</span>
                            <span className="ml-auto rounded-full border border-zinc-200 px-2 py-0.5 text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase dark:border-zinc-700 dark:text-zinc-500">
                                /
                            </span>
                        </Link>

                        <Button
                            variant="ghost"
                            size="icon"
                            asChild
                            className="rounded-full text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100"
                        >
                            <Link href={editProfile()} aria-label="Settings">
                                <Settings className="size-4" />
                            </Link>
                        </Button>

                        <AppUserMenu />
                    </div>
                </div>
            </header>

            {breadcrumbs.length > 1 ? (
                <div className="border-b border-zinc-200/80 bg-white/80 dark:border-zinc-800/80 dark:bg-zinc-950/75">
                    <div className="mx-auto flex h-11 w-full max-w-420 items-center px-4 text-sm text-zinc-500 sm:px-6 lg:px-8 dark:text-zinc-400">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                </div>
            ) : null}
        </>
    );
}
