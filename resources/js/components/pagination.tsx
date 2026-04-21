import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type PaginationProps = {
    page: number;
    totalPages: number;
    disabled?: boolean;
    onPageChange: (page: number) => void;
    className?: string;
};

export function Pagination({
    page,
    totalPages,
    disabled = false,
    onPageChange,
    className,
}: PaginationProps) {
    if (totalPages <= 1) {
        return null;
    }

    const pages = buildPaginationPages(page, totalPages);

    return (
        <div className={cn('flex flex-wrap items-center justify-center gap-2', className)}>
            <Button
                variant="outline"
                onClick={() => onPageChange(page - 1)}
                disabled={disabled || page === 1}
                className="size-11 rounded-2xl border-slate-200 bg-white p-0 text-slate-400 shadow-[0_12px_24px_-20px_rgba(15,23,42,0.18)] hover:bg-primary hover:text-white disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
            >
                <ChevronLeft className="size-4" />
            </Button>

            {pages.map((item, index) =>
                item === 'ellipsis' ? (
                    <span
                        key={`ellipsis-${index}`}
                        className="px-2 text-sm text-zinc-400 dark:text-zinc-500"
                    >
                        ...
                    </span>
                ) : (
                    <Button
                        key={item}
                        variant="outline"
                        onClick={() => onPageChange(item)}
                        disabled={disabled}
                        aria-current={page === item ? 'page' : undefined}
                        className={cn(
                            'size-11 rounded-2xl border-slate-200 bg-white p-0 text-sm font-semibold text-slate-600 shadow-[0_12px_24px_-20px_rgba(15,23,42,0.18)] hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800',
                            page === item && 'border-primary bg-primary text-white hover:bg-primary dark:bg-zinc-900 dark:text-primary',
                        )}
                    >
                        {item}
                    </Button>
                ),
            )}

            <Button
                variant="outline"
                onClick={() => onPageChange(page + 1)}
                disabled={disabled || page === totalPages}
                className="size-11 rounded-2xl border-slate-200 bg-white p-0 text-slate-400 shadow-[0_12px_24px_-20px_rgba(15,23,42,0.18)] hover:bg-primary hover:text-white disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
            >
                <ChevronRight className="size-4" />
            </Button>
        </div>
    );
}

function buildPaginationPages(currentPage: number, totalPages: number): Array<number | 'ellipsis'> {
    if (totalPages <= 7) {
        return Array.from({ length: totalPages }, (_, index) => index + 1);
    }

    if (currentPage <= 3) {
        return [1, 2, 3, 4, 'ellipsis', totalPages];
    }

    if (currentPage >= totalPages - 2) {
        return [1, 'ellipsis', totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
    }

    return [1, 'ellipsis', currentPage - 1, currentPage, currentPage + 1, 'ellipsis', totalPages];
}
