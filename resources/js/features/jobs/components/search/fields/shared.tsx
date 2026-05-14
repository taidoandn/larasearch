import { BriefcaseBusiness, Layers3, MapPin, Search, TrendingUp, Wallet, Zap } from 'lucide-react';

export const controlClassName =
    'h-7 rounded-none border-0 bg-transparent px-0 py-0 text-sm leading-none font-medium text-foreground shadow-none ring-0 focus-visible:ring-0 placeholder:text-muted-foreground/70';

export const salaryBounds = {
    min: 60_000,
    max: 240_000,
    step: 5_000,
} as const;

const labelIconClassName = 'size-3.5 text-muted-foreground/70';

const filterIcons = {
    Keywords: Search,
    Location: MapPin,
    Category: Layers3,
    'Salary Range': Wallet,
    Skills: Zap,
    Experience: TrendingUp,
    'Work Model': BriefcaseBusiness,
    'Job Type': BriefcaseBusiness,
} as const;

export function renderFilterIcon(label: string) {
    const Icon = filterIcons[label as keyof typeof filterIcons];

    return Icon ? <Icon className={labelIconClassName} /> : null;
}
