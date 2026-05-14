import { Search } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { JobSuggestionItem } from '@/features/jobs/types';
import { cn } from '@/lib/utils';
import { controlClassName, renderFilterIcon } from './shared';

export function TextFilterField({
    label,
    placeholder,
    value,
    onValueChange,
    suggestions = [],
    isSuggesting = false,
    activeSuggestionIndex = -1,
    onActiveSuggestionIndexChange = () => {},
    onSuggestionSelect = () => {},
    onSuggestionsVisibilityChange = () => {},
    suggestionsOpen = false,
}: {
    label: string;
    placeholder: string;
    value: string;
    onValueChange: (value: string) => void;
    suggestions?: JobSuggestionItem[];
    isSuggesting?: boolean;
    activeSuggestionIndex?: number;
    onActiveSuggestionIndexChange?: (value: number) => void;
    onSuggestionSelect?: (suggestion: JobSuggestionItem) => void;
    onSuggestionsVisibilityChange?: (open: boolean) => void;
    suggestionsOpen?: boolean;
}) {
    return (
        <section className="relative space-y-2.5">
            <Label className="flex items-center gap-2 text-sm font-semibold text-muted-foreground">
                {renderFilterIcon(label)}
                {label}
            </Label>
            <div className="rounded-2xl bg-card px-3 py-2 shadow-[inset_0_0_0_1px_rgba(25,28,30,0.08)] transition-shadow focus-within:shadow-[0_0_0_4px_rgba(0,74,198,0.08)]">
                <Input
                    value={value}
                    placeholder={placeholder}
                    onChange={(event) => onValueChange(event.target.value)}
                    onFocus={() => {
                        if (suggestions.length > 0) {
                            onSuggestionsVisibilityChange(true);
                        }
                    }}
                    onBlur={() => {
                        window.setTimeout(() => onSuggestionsVisibilityChange(false), 120);
                    }}
                    onKeyDown={(event) => {
                        if (!suggestionsOpen || suggestions.length === 0) {
                            return;
                        }

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            onActiveSuggestionIndexChange(
                                (activeSuggestionIndex + 1) % suggestions.length,
                            );
                        }

                        if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            onActiveSuggestionIndexChange(
                                activeSuggestionIndex <= 0
                                    ? suggestions.length - 1
                                    : activeSuggestionIndex - 1,
                            );
                        }

                        if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                            event.preventDefault();
                            onSuggestionSelect(suggestions[activeSuggestionIndex]);
                        }

                        if (event.key === 'Escape') {
                            onSuggestionsVisibilityChange(false);
                        }
                    }}
                    className={controlClassName}
                />
            </div>

            {isSuggesting ? (
                <div className="pointer-events-none absolute top-[2.45rem] right-3 text-muted-foreground/70">
                    <Spinner className="size-3.5" />
                </div>
            ) : null}

            {suggestionsOpen && suggestions.length > 0 ? (
                <div className="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-2xl border border-transparent bg-card shadow-[0_20px_40px_-24px_rgba(0,74,198,0.12)]">
                    {suggestions.map((suggestion, index) => (
                        <button
                            key={`${suggestion.type}-${suggestion.label}`}
                            type="button"
                            onMouseEnter={() => onActiveSuggestionIndexChange(index)}
                            onMouseDown={(event) => {
                                event.preventDefault();
                                onSuggestionSelect(suggestion);
                            }}
                            className={cn(
                                'flex w-full items-center justify-between px-3 py-2 text-left text-sm transition-colors',
                                index === activeSuggestionIndex
                                    ? 'bg-primary/8 text-foreground'
                                    : 'bg-card text-muted-foreground hover:bg-secondary',
                            )}
                        >
                            <span className="truncate">{suggestion.label}</span>
                            <span className="inline-flex items-center gap-1 text-[10px] font-semibold tracking-[0.22em] text-muted-foreground/70 uppercase">
                                <Search className="size-3" />
                                {suggestion.type.replace('_', ' ')}
                            </span>
                        </button>
                    ))}
                </div>
            ) : null}
        </section>
    );
}
