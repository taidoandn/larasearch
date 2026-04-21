import {
    Bookmark,
    BriefcaseBusiness,
    CircleDot,
    Clock3,
    MapPin,
    TrendingUp,
    WalletCards,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CompanyIdentity } from '@/features/jobs/components/shared/company-identity';
import { HighlightedText } from '@/features/jobs/components/shared/highlighted-text';
import { cn } from '@/lib/utils';

type SummaryAction = {
    label: string;
    href?: string | null;
    icon?: ReactNode;
};

type SummaryChip = {
    label: string;
    type: 'salary' | 'work-model' | 'experience' | 'job-type' | 'published-at';
    emphasis?: 'primary';
};

export type JobSummaryPanelProps = {
    companyName: string;
    companyLogoUrl?: string | null;
    companyMeta?: string | null;
    companyVerified?: boolean;
    location: string;
    title: string;
    titleHighlight?: string | null;
    chips: SummaryChip[];
    skills: string[];
    highlightedSummary?: ReactNode;
    mapLabel: string;
    contextLabel: string;
    contextValue: string;
    secondaryAction: SummaryAction;
};

export function JobSummaryPanel({
    companyName,
    companyLogoUrl,
    companyMeta,
    companyVerified = false,
    location,
    title,
    titleHighlight,
    chips,
    skills,
    highlightedSummary,
    mapLabel,
    contextLabel,
    contextValue,
    secondaryAction,
}: JobSummaryPanelProps) {
    const visibleSkills = skills.slice(0, 4);
    const remainingSkills = Math.max(skills.length - visibleSkills.length, 0);

    return (
        <section className="relative overflow-hidden rounded-4xl bg-white p-7 shadow-[0_24px_60px_-40px_rgba(0,74,198,0.18)] lg:p-8">
            <div className="relative z-10 space-y-7">
                <div className="flex items-start justify-between gap-4">
                    <CompanyIdentity
                        name={companyName}
                        logoUrl={companyLogoUrl}
                        verified={companyVerified}
                        size="compact"
                        className="max-w-xs flex-1"
                        meta={[location, companyMeta].filter(Boolean).join(' • ')}
                    />

                    <div className="flex shrink-0">
                        <SummaryButton action={secondaryAction} kind="icon-secondary" />
                    </div>
                </div>

                <div className="space-y-5">
                    <h1 className="max-w-86 font-display text-4xl leading-none font-semibold tracking-tight text-slate-950">
                        <HighlightedText text={title} highlight={titleHighlight ?? null} />
                    </h1>

                    <div className="flex flex-wrap gap-2.5">
                        {chips.map((chip) => (
                            <span
                                key={`${chip.type}-${chip.label}`}
                                className={cn(
                                    'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold',
                                    chip.emphasis === 'primary'
                                        ? 'bg-blue-50 text-primary'
                                        : 'bg-slate-100 text-slate-600',
                                )}
                            >
                                {summaryChipIcon(chip.type)}
                                {chip.label}
                            </span>
                        ))}
                    </div>
                </div>

                <section className="space-y-4">
                    <h2 className="text-xs font-semibold tracking-[0.28em] text-slate-500 uppercase">
                        Technical Requirements
                    </h2>
                    <div className="flex flex-wrap gap-2">
                        {visibleSkills.map((skill) => (
                            <Badge
                                key={skill}
                                variant="outline"
                                className="rounded-xl border-slate-200 bg-slate-50 px-4 py-2 text-xs font-medium text-slate-700"
                            >
                                {skill}
                            </Badge>
                        ))}

                        {remainingSkills > 0 ? (
                            <Badge
                                variant="outline"
                                className="rounded-xl border-slate-200 bg-slate-50 px-4 py-2 text-xs font-medium text-slate-500"
                            >
                                +{remainingSkills} more
                            </Badge>
                        ) : null}
                    </div>
                </section>

                <section className="space-y-4">
                    <div className="flex items-center justify-between gap-4">
                        <div className="space-y-2">
                            <h2 className="text-xs font-semibold tracking-[0.28em] text-slate-500 uppercase">
                                {contextLabel}
                            </h2>
                            <p className="flex flex-wrap items-center gap-2 text-sm text-slate-600">
                                <BriefcaseBusiness className="size-4 text-slate-400" />
                                <span>{contextValue}</span>
                            </p>
                        </div>

                        <span className="text-xs font-semibold text-primary">Explore Area</span>
                    </div>

                    <div className="overflow-hidden rounded-4xl bg-[linear-gradient(180deg,rgba(148,163,184,0.72),rgba(203,213,225,0.65))]">
                        <div className="relative flex h-36 items-end justify-between px-6 py-5">
                            <p className="relative z-10 max-w-xs text-sm leading-6 text-white/90">
                                {mapLabel}
                            </p>
                            <div className="relative z-10 flex size-10 items-center justify-center rounded-full bg-white text-primary shadow-lg">
                                <MapPin className="size-4" />
                            </div>
                        </div>
                    </div>
                </section>

                {highlightedSummary ? (
                    <section className="rounded-4xl border border-slate-100 bg-slate-50/95 px-5 py-5">
                        <h2 className="text-xs font-semibold tracking-[0.28em] text-slate-500 uppercase">
                            Highlighted Summary
                        </h2>
                        <div className="mt-4 text-sm leading-7 text-slate-600">
                            {highlightedSummary}
                        </div>
                    </section>
                ) : null}
            </div>
        </section>
    );
}

function SummaryButton({
    action,
    kind,
    className,
}: {
    action: SummaryAction;
    kind: 'primary' | 'icon-secondary';
    className?: string;
}) {
    const icon =
        action.icon ?? (kind === 'icon-secondary' ? <Bookmark className="size-4" /> : null);

    if (action.href) {
        return (
            <Button
                asChild
                className={cn(
                    'text-sm font-semibold shadow-none',
                    kind === 'primary'
                        ? 'h-11 rounded-full bg-[linear-gradient(135deg,#2563eb_0%,#1d4ed8_100%)] px-6 text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.7)] hover:opacity-95'
                        : 'size-11 rounded-full border border-slate-200 bg-white px-0 text-slate-700 hover:bg-slate-50',
                    className,
                )}
                variant={kind === 'primary' ? 'default' : 'outline'}
            >
                <a href={action.href} target="_blank" rel="noreferrer">
                    {icon}
                    {kind === 'primary' ? (
                        action.label
                    ) : (
                        <span className="sr-only">{action.label}</span>
                    )}
                </a>
            </Button>
        );
    }

    if (kind === 'primary') {
        return (
            <Button
                disabled
                className={cn(
                    'h-11 rounded-full bg-[linear-gradient(135deg,#2563eb_0%,#1d4ed8_100%)] px-6 text-sm font-semibold text-white shadow-none disabled:opacity-70',
                    className,
                )}
                variant="default"
            >
                {action.label}
            </Button>
        );
    }

    return (
        <Button
            variant="outline"
            className={cn(
                'size-11 rounded-full border-slate-200 bg-white px-0 text-slate-700 shadow-none hover:bg-slate-50',
                className,
            )}
        >
            {icon}
            <span className="sr-only">{action.label}</span>
        </Button>
    );
}

function summaryChipIcon(type: SummaryChip['type']): ReactNode {
    switch (type) {
        case 'salary':
            return <WalletCards className="size-4" />;
        case 'work-model':
            return <BriefcaseBusiness className="size-4" />;
        case 'experience':
            return <TrendingUp className="size-4" />;
        case 'job-type':
            return <CircleDot className="size-4" />;
        case 'published-at':
            return <Clock3 className="size-4" />;
    }
}
