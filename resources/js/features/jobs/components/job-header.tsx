import { Button } from '@/components/ui/button';
import { HighlightedText } from '@/features/jobs/components/highlighted-text';
import type { JobDetailItem } from '@/features/jobs/types';
import { formatSalaryRange } from '@/features/jobs/utils';

export function JobHeader({ job }: { job: JobDetailItem }) {
    const salary = formatSalaryRange(job.salary);
    const primaryLocation = job.primary_location ?? job.locations[0] ?? 'Remote';
    const applyUrl = job.application_url ?? job.company.website;
    const isApplyDisabled = applyUrl === null;

    return (
        <header className="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div className="mx-auto flex w-full max-w-420 flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 xl:flex-row xl:items-center xl:justify-between xl:py-8">
                <div className="space-y-2">
                    <h1 className="text-3xl font-black tracking-tight text-zinc-950 dark:text-zinc-50">
                        <HighlightedText text={job.title} highlight={job.highlight.title} />
                    </h1>
                    <div className="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                        <span className="font-semibold text-zinc-950 dark:text-zinc-100">
                            {job.company.name}
                        </span>
                        <span className="text-zinc-300 dark:text-zinc-700">
                            •
                        </span>
                        <span>{primaryLocation}</span>
                        <span className="text-zinc-300 dark:text-zinc-700">
                            •
                        </span>
                        <span>{salary}</span>
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        disabled
                        className="h-11 rounded-none border-zinc-200 px-5 text-[10px] font-bold tracking-[0.24em] uppercase shadow-none hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-900"
                    >
                        Save Job Soon
                    </Button>
                    <Button
                        asChild={! isApplyDisabled}
                        disabled={isApplyDisabled}
                        className="h-11 rounded-none bg-primary px-6 text-[10px] font-bold tracking-[0.24em] text-primary-foreground uppercase shadow-none hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        {applyUrl ? (
                            <a
                                href={applyUrl}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Apply Now
                            </a>
                        ) : (
                            <span>Apply Now</span>
                        )}
                    </Button>
                </div>
            </div>
        </header>
    );
}
