import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { landingNavItems } from '@/features/landing/content';
import { login, register } from '@/routes';
import { index as jobsIndex } from '@/routes/jobs';

export function LandingHeader({
    isAuthenticated,
    canRegister,
}: {
    isAuthenticated: boolean;
    canRegister: boolean;
}) {
    return (
        <header className="px-6 pt-4 sm:px-8 lg:px-10">
            <div className="mx-auto grid max-w-6xl items-center gap-4 py-1 md:grid-cols-[1fr_auto_1fr]">
                <Link
                    href="/"
                    className="justify-self-start text-2xl font-semibold tracking-[-0.02em] text-zinc-950 dark:text-zinc-100"
                >
                    Larasearch
                </Link>

                <nav className="hidden items-center gap-6 justify-self-center md:flex">
                    {landingNavItems.map((item) => (
                        <a
                            key={item.href}
                            href={item.href}
                            className="text-[9px] font-medium tracking-[0.18em] text-zinc-400 uppercase transition-colors hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300"
                        >
                            {item.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-2 justify-self-end">
                    {isAuthenticated ? (
                        <Button
                            asChild
                            className="h-7 rounded-none bg-primary px-3 text-[9px] font-semibold tracking-[0.18em] text-primary-foreground uppercase shadow-none hover:bg-primary/90"
                        >
                            <Link href={jobsIndex()}>Open Search</Link>
                        </Button>
                    ) : (
                        <>
                            <Button
                                asChild
                                variant="ghost"
                                className="h-7 rounded-none px-2 text-[9px] font-semibold tracking-[0.18em] text-zinc-500 uppercase shadow-none hover:bg-transparent hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                            >
                                <Link href={login()}>Sign In</Link>
                            </Button>
                            {canRegister ? (
                                <Button
                                    asChild
                                    className="h-7 rounded-none bg-primary px-3 text-[9px] font-semibold tracking-[0.18em] text-primary-foreground uppercase shadow-none hover:bg-primary/90"
                                >
                                    <Link href={register()}>Sign Up</Link>
                                </Button>
                            ) : null}
                        </>
                    )}
                </div>
            </div>
        </header>
    );
}
