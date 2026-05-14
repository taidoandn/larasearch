import { Bookmark } from 'lucide-react';
import { CompanyAvatar, HighlightedText, JobChipIcon } from '@/features/jobs/components/shared';
import {
    buildJobDisplayChips,
    isMatchingSkill,
    jobCompanyName,
    jobPositionMeta,
    prioritizeSkills,
} from '@/features/jobs/utils';
import { cn } from '@/lib/utils';
import type { JobResultItem } from '../../types';

export function JobResultRow({
    job,
    activeSkills,
    selected,
    onSelect,
}: {
    job: JobResultItem;
    activeSkills: string[];
    selected: boolean;
    onSelect: () => void;
}) {
    const compactTitle = job.title.length > 40 ? `${job.title.slice(0, 40)}…` : job.title;
    const prioritizedSkills = prioritizeSkills(job.skills, activeSkills);
    const visibleSkills = prioritizedSkills.slice(0, 3);
    const overflowCount = Math.max(prioritizedSkills.length - visibleSkills.length, 0);
    const companyName = jobCompanyName(job);

    return (
        <article
            aria-pressed={selected}
            onClick={onSelect}
            className={cn(
                'relative flex h-auto w-full flex-col gap-5 rounded-4xl border px-5 py-5 text-left transition-all sm:px-6 sm:py-6 lg:flex-row lg:items-center lg:justify-between',
                'justify-start whitespace-normal',
                selected
                    ? 'border-primary bg-primary/7 shadow-[0_22px_40px_-28px_rgba(0,74,198,0.18)]'
                    : 'border-card bg-card shadow-[0_18px_32px_-30px_rgba(0,74,198,0.10)] hover:-translate-y-0.5 hover:bg-white',
            )}
        >
            <div className="min-w-0 flex-1">
                <div className="flex items-start gap-4">
                    <CompanyAvatar
                        name={companyName}
                        logoUrl={job.company.logo_url}
                        size="compact"
                        className="shrink-0"
                    />

                    <div className="min-w-0 flex-1 space-y-1.5">
                        <div className="flex flex-wrap items-start gap-3 pr-10">
                            <h2 className="font-display text-xl leading-tight font-semibold tracking-tight text-foreground">
                                <HighlightedText
                                    text={compactTitle}
                                    highlight={job.highlight.title}
                                />
                            </h2>
                            {selected ? (
                                <span className="rounded-full bg-white/80 px-3 py-1 text-[10px] font-semibold tracking-[0.16em] text-primary uppercase">
                                    Selected
                                </span>
                            ) : null}
                        </div>

                        <p className="truncate text-base font-medium text-slate-600">
                            {jobPositionMeta(job)}
                        </p>
                    </div>
                </div>

                <div className="mt-5 flex flex-wrap gap-2.5">
                    {buildJobDisplayChips(job).map((chip) => (
                        <span
                            key={`${chip.type}-${chip.label}`}
                            className={cn(
                                'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold',
                                chip.emphasis === 'primary'
                                    ? 'bg-blue-50 text-primary'
                                    : 'bg-slate-100 text-slate-600',
                            )}
                        >
                            <JobChipIcon type={chip.type} />
                            {chip.label}
                        </span>
                    ))}
                </div>

                <div className="mt-5 flex flex-wrap gap-2">
                    {visibleSkills.map((skill) => (
                        <span
                            key={skill}
                            className={cn(
                                'rounded-xl border px-3 py-1 text-[11px] transition-colors',
                                isMatchingSkill(skill, activeSkills)
                                    ? selected
                                        ? 'border-primary/25 bg-white font-extrabold text-primary shadow-[0_12px_22px_-18px_rgba(0,74,198,0.35)]'
                                        : 'border-primary/15 bg-blue-50 font-extrabold text-primary shadow-[0_12px_22px_-18px_rgba(0,74,198,0.24)]'
                                    : selected
                                      ? 'border-card bg-card font-semibold text-muted-foreground'
                                      : 'border-border/80 bg-secondary font-medium text-muted-foreground',
                            )}
                        >
                            {skill}
                        </span>
                    ))}
                    {overflowCount > 0 ? (
                        <span
                            className={cn(
                                'rounded-xl border px-3 py-1 text-[11px] font-semibold',
                                selected
                                    ? 'border-card bg-card text-muted-foreground'
                                    : 'border-border/80 bg-secondary text-muted-foreground',
                            )}
                        >
                            +{overflowCount}
                        </span>
                    ) : null}
                </div>
            </div>

            <div className="flex items-center justify-end gap-4 lg:min-w-16 lg:flex-col lg:items-end lg:justify-center">
                <span
                    className={cn(
                        'inline-flex size-10 items-center justify-center rounded-full',
                        selected ? 'bg-card text-primary' : 'bg-secondary text-muted-foreground/70',
                    )}
                >
                    <Bookmark
                        className={cn(
                            'size-5',
                            selected ? 'fill-primary text-primary' : 'text-muted-foreground/35',
                        )}
                    />
                </span>
            </div>
        </article>
    );
}
