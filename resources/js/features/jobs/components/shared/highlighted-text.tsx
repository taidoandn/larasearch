import { cn } from '@/lib/utils';

export function HighlightedText({
    text,
    highlight,
    className,
}: {
    text: string;
    highlight?: string | null;
    className?: string;
}) {
    if (! highlight) {
        return text;
    }

    const normalized = highlight.replaceAll('&lt;em&gt;', '<em>').replaceAll('&lt;/em&gt;', '</em>');
    const segments = normalized.split(/(<\/?em>)/);

    let isHighlighted = false;

    return segments.map((segment, index) => {
        if (segment === '<em>') {
            isHighlighted = true;

            return null;
        }

        if (segment === '</em>') {
            isHighlighted = false;

            return null;
        }

        if (segment === '') {
            return null;
        }

        return (
            <span
                key={`${index}-${segment}`}
                className={cn(isHighlighted && 'rounded-none bg-primary/10 text-primary', className)}
            >
                {segment}
            </span>
        );
    });
}
