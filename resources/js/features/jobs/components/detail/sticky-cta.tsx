import { Bookmark } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { JobDetailItem } from '@/features/jobs/types';
import { formatSalaryRange } from '@/features/jobs/utils';

export function StickyCta({ job }: { job: JobDetailItem }) {
    const applyUrl = job.application_url ?? job.company.website;

    return (
        <div className="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-4 py-4 backdrop-blur md:hidden">
            <div className="mx-auto flex w-full max-w-360 items-center gap-3">
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-bold text-slate-950">{job.title}</p>
                    <p className="truncate text-xs text-slate-500">
                        {job.company.name} • {formatSalaryRange(job.salary)}
                    </p>
                </div>

                <div className="flex min-w-[11rem] gap-3">
                    <Button
                        asChild={Boolean(applyUrl)}
                        disabled={!applyUrl}
                        className="min-w-0 flex-1 rounded-xl bg-[linear-gradient(135deg,#2563eb_0%,#1d4ed8_100%)] font-bold text-white hover:opacity-95"
                    >
                        {applyUrl ? (
                            <a href={applyUrl} target="_blank" rel="noreferrer">
                                Apply
                            </a>
                        ) : (
                            <span>Apply</span>
                        )}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        className="size-10 rounded-xl border-slate-200 bg-white text-slate-700"
                    >
                        <Bookmark />
                    </Button>
                </div>
            </div>
        </div>
    );
}
