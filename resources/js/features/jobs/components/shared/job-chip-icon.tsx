import { BriefcaseBusiness, CircleDot, Clock3, TrendingUp, WalletCards } from 'lucide-react';
import type { JobDisplayChipType } from '@/features/jobs/utils';

export function JobChipIcon({ type }: { type: JobDisplayChipType }) {
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
