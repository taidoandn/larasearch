export function LandingFooter() {
    return (
        <footer className="bg-zinc-950 px-6 py-4 text-white sm:px-8 lg:px-10">
            <div className="mx-auto flex max-w-6xl flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div className="space-y-1">
                    <p className="text-sm font-semibold tracking-tight">
                        Larasearch
                    </p>
                    <p className="text-xs tracking-[0.16em] text-zinc-400 uppercase">
                        Precision search for technical careers
                    </p>
                </div>

                <div className="flex flex-wrap gap-4 text-xs tracking-[0.16em] text-zinc-400 uppercase">
                    <a
                        href="#features"
                        className="transition-colors hover:text-white"
                    >
                        Resources
                    </a>
                    <a
                        href="#preview"
                        className="transition-colors hover:text-white"
                    >
                        Documentation
                    </a>
                    <a
                        href="#queries"
                        className="transition-colors hover:text-white"
                    >
                        API
                    </a>
                    <a
                        href="#features"
                        className="transition-colors hover:text-white"
                    >
                        Terms of Service
                    </a>
                </div>
            </div>
        </footer>
    );
}
