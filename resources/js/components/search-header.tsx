import { Link } from '@inertiajs/react';
import { Bell, Menu, Search, Settings } from 'lucide-react';
import { AppUserMenu } from '@/components/app-user-menu';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { index as jobsIndex } from '@/routes/jobs';
import { edit as editProfile } from '@/routes/profile';
import type { BreadcrumbItem } from '@/types';

export type SearchLayoutSection = 'search' | 'settings';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const navItems = [
    {
        title: 'Explore',
        href: jobsIndex(),
    },
];

export function SearchHeader({ breadcrumbs = [] }: Props) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <>
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white">
                <div className="mx-auto flex h-16 w-full max-w-360 items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <div className="lg:hidden">
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="h-9 w-9 rounded-full text-slate-500 hover:bg-slate-100 hover:text-primary"
                                >
                                    <Menu className="size-4" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent
                                side="left"
                                className="flex h-full w-72 flex-col bg-white px-0"
                            >
                                <SheetTitle className="sr-only">Search navigation</SheetTitle>
                                <SheetHeader className="border-b border-zinc-200 px-5 pb-4 text-left">
                                    <Link
                                        href={home()}
                                        className="text-2xl font-semibold tracking-tight text-primary"
                                    >
                                        Larasearch
                                    </Link>
                                </SheetHeader>
                                <div className="flex flex-1 flex-col justify-between px-3 py-4">
                                    <nav className="space-y-1">
                                        {navItems.map((item) => {
                                            const isActive = isCurrentOrParentUrl(item.href);

                                            return (
                                                <Button
                                                    key={item.title}
                                                    variant="ghost"
                                                    asChild
                                                    className={cn(
                                                        'h-11 w-full justify-start rounded-xl px-3 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900',
                                                        isActive &&
                                                            'bg-blue-50 text-primary hover:bg-blue-50 hover:text-primary',
                                                    )}
                                                >
                                                    <Link href={item.href}>{item.title}</Link>
                                                </Button>
                                            );
                                        })}
                                    </nav>

                                    <div className="space-y-2">
                                        <Button
                                            variant="ghost"
                                            asChild
                                            className="h-11 w-full justify-start rounded-xl px-3 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                                        >
                                            <Link href={jobsIndex()}>
                                                <Search className="size-4" />
                                                Search database
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            asChild
                                            className="h-11 w-full justify-start rounded-xl px-3 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
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
                        className="min-w-0 text-[2rem] font-semibold tracking-tight text-primary"
                    >
                        Larasearch
                    </Link>

                    <nav className="ml-5 hidden lg:block">
                        <NavigationMenu>
                            <NavigationMenuList className="gap-6">
                                {navItems.map((item) => {
                                    const isActive = isCurrentOrParentUrl(item.href);

                                    return (
                                        <NavigationMenuItem key={item.title}>
                                            <Link
                                                href={item.href}
                                                className={cn(
                                                    navigationMenuTriggerStyle(),
                                                    'h-16 rounded-none border-b-2 border-transparent bg-transparent px-0 text-base font-medium text-slate-500 hover:bg-transparent hover:text-primary',
                                                    isActive &&
                                                        'border-primary text-primary hover:bg-transparent hover:text-primary',
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

                    <div className="ml-auto flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="rounded-full text-slate-500 hover:bg-slate-100 hover:text-primary"
                        >
                            <span aria-hidden="true">
                                <Bell className="size-4" />
                            </span>
                        </Button>

                        <Button
                            variant="ghost"
                            size="icon"
                            asChild
                            className="rounded-full text-slate-500 hover:bg-slate-100 hover:text-primary"
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
                <div className="border-b border-slate-200 bg-white">
                    <div className="mx-auto flex h-11 w-full max-w-360 items-center px-4 text-sm text-slate-500 sm:px-6 lg:px-8">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                </div>
            ) : null}
        </>
    );
}
