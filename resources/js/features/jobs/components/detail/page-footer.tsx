import { Link } from '@inertiajs/react';
import { Globe, Orbit } from 'lucide-react';
import { home } from '@/routes';
import { index as jobsIndex } from '@/routes/jobs';
import { edit as editProfile } from '@/routes/profile';

export function PageFooter() {
    return (
        <footer className="mt-20 border-t border-slate-200 bg-white py-16">
            <div className="mx-auto grid w-full max-w-360 gap-12 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div className="lg:col-span-2">
                    <div className="flex flex-col gap-6">
                        <div>
                            <p className="font-display text-3xl font-extrabold tracking-tight text-primary">
                                Larasearch
                            </p>
                            <p className="mt-4 max-w-md text-lg leading-8 text-slate-600">
                                The curated job marketplace for engineers comparing role fit,
                                systems complexity, and long-term growth.
                            </p>
                        </div>

                        <div className="flex gap-4">
                            <span className="flex size-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600">
                                <Globe className="size-5" />
                            </span>
                            <span className="flex size-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600">
                                <Orbit className="size-5" />
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <p className="text-sm font-bold tracking-[0.22em] text-slate-500 uppercase">
                        Platform
                    </p>
                    <div className="mt-6 flex flex-col gap-4 font-semibold text-slate-900">
                        <Link href={jobsIndex()} className="transition-colors hover:text-primary">
                            Curated Jobs
                        </Link>
                        <span className="text-slate-500">Talent Network</span>
                        <span className="text-slate-500">Salary Insights</span>
                    </div>
                </div>

                <div>
                    <p className="text-sm font-bold tracking-[0.22em] text-slate-500 uppercase">
                        Company
                    </p>
                    <div className="mt-6 flex flex-col gap-4 font-semibold text-slate-900">
                        <Link href={home()} className="transition-colors hover:text-primary">
                            About Larasearch
                        </Link>
                        <Link href={editProfile()} className="transition-colors hover:text-primary">
                            Account Settings
                        </Link>
                        <span className="text-slate-500">Privacy Policy</span>
                    </div>
                </div>
            </div>

            <div className="mx-auto mt-14 flex w-full max-w-360 flex-col items-center justify-between gap-4 border-t border-slate-200 px-4 pt-8 text-center text-[11px] font-bold tracking-[0.24em] text-slate-500 uppercase sm:px-6 lg:flex-row lg:px-8">
                <span>© 2026 Larasearch. All rights reserved.</span>
                <div className="flex gap-6">
                    <span>Terms</span>
                    <span>Cookies</span>
                </div>
            </div>
        </footer>
    );
}
