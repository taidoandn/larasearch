import {
    Bookmark,
    BriefcaseBusiness,
    CircleDot,
    Clock3,
    TrendingUp,
    WalletCards,
} from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { CompanyAvatar, HighlightedText } from '@/features/jobs/components/shared';
import type { JobResultItem, JobResultsPayload } from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from '@/features/jobs/utils';
import { cn } from '@/lib/utils';

export function ResultsList({
    items,
    activeSkills,
    pagination,
    isRefreshing,
    selectedJobId,
    onSelectJob,
    onPageChange,
    onReset,
}: {
    items: JobResultItem[];
    activeSkills: string[];
    pagination: JobResultsPayload['pagination'];
    isRefreshing: boolean;
    selectedJobId: number | null;
    onSelectJob: (job: JobResultItem) => void;
    onPageChange: (page: number) => void;
    onReset: () => void;
}) {
    if (isRefreshing && items.length === 0) {
        return <ResultsSkeleton />;
    }

    return (
        <div className="bg-transparent">
            {items.length === 0 ? (
                <EmptyState onReset={onReset} />
            ) : (
                <div className="space-y-5 bg-secondary px-4 py-4 sm:px-6 sm:py-6">
                    {items.map((job) => (
                        <JobResultRow
                            key={job.id}
                            job={job}
                            activeSkills={activeSkills}
                            selected={selectedJobId === job.id}
                            onSelect={() => onSelectJob(job)}
                        />
                    ))}
                </div>
            )}

            <div className="flex flex-col items-center gap-3 px-4 py-8 sm:px-6 sm:py-10">
                {pagination.total_pages > 1 ? (
                    <div className="rounded-[28px] bg-card px-5 py-4 shadow-[0_20px_36px_-30px_rgba(0,74,198,0.12)]">
                        <Pagination
                            page={pagination.page}
                            totalPages={pagination.total_pages}
                            disabled={isRefreshing}
                            onPageChange={onPageChange}
                        />
                    </div>
                ) : null}
                <p className="font-mono text-[10px] tracking-[0.28em] text-muted-foreground/70 uppercase">
                    {pagination.total === 0
                        ? 'Showing 0 results'
                        : `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results`}
                </p>
            </div>
        </div>
    );
}

function JobResultRow({
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
    const salary = formatSalaryRange(job.salary);
    const compactTitle = job.title.length > 40 ? `${job.title.slice(0, 40)}…` : job.title;
    const prioritizedSkills = prioritizeSkills(job.skills, activeSkills);
    const visibleSkills = prioritizedSkills.slice(0, 3);
    const overflowCount = Math.max(prioritizedSkills.length - visibleSkills.length, 0);
    const companyName = job.company.name ?? 'Unknown company';
    const positionMeta = [companyName, job.primary_location ?? 'Remote']
        .filter(Boolean)
        .join(' • ');
    const chips = [
        {
            label: salary,
            icon: WalletCards,
            className: 'bg-blue-50 text-primary',
        },
        {
            label: job.work_model_label ?? formatWorkModelLabel(job.work_model, 'Unknown model'),
            icon: BriefcaseBusiness,
            className: 'bg-slate-100 text-slate-600',
        },
        {
            label: job.experience_level_label ?? formatExperienceLevelLabel(job.experience_level),
            icon: TrendingUp,
            className: 'bg-slate-100 text-slate-600',
        },
        {
            label: job.job_type_label ?? formatJobTypeLabel(job.job_type, 'Not specified'),
            icon: CircleDot,
            className: 'bg-slate-100 text-slate-600',
        },
        {
            label: formatDisplayDate(job.published_at),
            icon: Clock3,
            className: 'bg-slate-100 text-slate-600',
        },
    ];

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
                            {positionMeta}
                        </p>
                    </div>
                </div>

                <div className="mt-5 flex flex-wrap gap-2.5">
                    {chips.map((chip) => (
                        <span
                            key={`${chip.label}-${chip.className}`}
                            className={cn(
                                'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold',
                                chip.className,
                            )}
                        >
                            <chip.icon className="size-4" />
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

function prioritizeSkills(skills: string[], activeSkills: string[]): string[] {
    const matchedSkills: string[] = [];
    const unmatchedSkills: string[] = [];

    for (const skill of skills) {
        if (isMatchingSkill(skill, activeSkills)) {
            matchedSkills.push(skill);
        } else {
            unmatchedSkills.push(skill);
        }
    }

    return [...matchedSkills, ...unmatchedSkills];
}

function isMatchingSkill(skill: string, activeSkills: string[]): boolean {
    if (activeSkills.length === 0) {
        return false;
    }

    const normalizedSkill = normalizeSkillValue(skill);

    return activeSkills.some((activeSkill) => normalizeSkillValue(activeSkill) === normalizedSkill);
}

function normalizeSkillValue(skill: string): string {
    return skill
        .trim()
        .toLowerCase()
        .replaceAll(/[^a-z0-9]+/g, '-')
        .replaceAll(/^-+|-+$/g, '');
}

function EmptyState({ onReset }: { onReset: () => void }) {
    return (
        <div className="px-4 py-12 sm:px-6">
            <div className="max-w-xl rounded-[28px] bg-card p-8 shadow-[0_20px_36px_-30px_rgba(0,74,198,0.12)]">
                <div className="space-y-4">
                    <p className="font-display text-sm font-semibold text-foreground">
                        No jobs matched your current search.
                    </p>
                    <p className="text-sm leading-6 text-muted-foreground">
                        Broaden the keyword, clear a filter, or reset the search to view the full
                        job set.
                    </p>
                    <Button
                        variant="outline"
                        onClick={onReset}
                        className="rounded-full border-transparent bg-card px-5 py-3 text-[11px] font-semibold tracking-[0.24em] text-foreground uppercase shadow-[inset_0_0_0_1px_rgba(25,28,30,0.12)] hover:bg-secondary"
                    >
                        Reset Search
                    </Button>
                </div>
            </div>
        </div>
    );
}

function ResultsSkeleton() {
    return (
        <div className="space-y-4 px-4 py-4 sm:px-6 sm:py-6">
            {Array.from({ length: 5 }).map((_, index) => (
                <div
                    key={index}
                    className="grid gap-4 rounded-4xl border border-white bg-white px-5 py-5 lg:grid-cols-[1fr_120px] lg:items-center"
                >
                    <div className="space-y-3">
                        <Skeleton className="h-4 w-2/3 rounded-none" />
                        <Skeleton className="h-3 w-1/2 rounded-none" />
                        <Skeleton className="h-3 w-3/4 rounded-none" />
                    </div>
                    <div className="flex gap-8">
                        <div className="space-y-2">
                            <Skeleton className="h-3 w-10 rounded-none" />
                            <Skeleton className="h-3 w-24 rounded-none" />
                        </div>
                        <div className="space-y-2">
                            <Skeleton className="h-3 w-10 rounded-none" />
                            <Skeleton className="h-3 w-20 rounded-none" />
                        </div>
                    </div>
                    <div className="flex items-center justify-between gap-4 lg:justify-end">
                        <div className="space-y-2 text-right">
                            <Skeleton className="h-3 w-12 rounded-none" />
                            <Skeleton className="h-3 w-16 rounded-none" />
                        </div>
                        <Skeleton className="size-4 rounded-none" />
                    </div>
                </div>
            ))}
        </div>
    );
}
