import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';

export function EmptyState({ onReset }: { onReset: () => void }) {
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

export function ResultsSkeleton() {
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
