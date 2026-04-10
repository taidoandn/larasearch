import { useEffect, useRef, useState } from 'react';
import { fetchJobSuggestions } from '@/features/jobs/api/fetch-job-suggestions';
import type { JobSuggestionItem } from '@/features/jobs/types';

const minimumSuggestionKeywordLength = 2;
const suggestionDebounceDelay = 180;

export function useJobSuggestions(keyword: string) {
    const [suggestions, setSuggestions] = useState<JobSuggestionItem[]>([]);
    const [isSuggesting, setIsSuggesting] = useState(false);
    const [isSuggestionsOpen, setIsSuggestionsOpen] = useState(false);
    const [activeSuggestionIndex, setActiveSuggestionIndex] = useState<number>(-1);
    const suggestionRequestVersion = useRef(0);

    useEffect(() => {
        const normalizedKeyword = keyword.trim();
        const requestVersion = ++suggestionRequestVersion.current;

        if (normalizedKeyword.length < minimumSuggestionKeywordLength) {
            clearSuggestions();

            return;
        }

        const controller = new AbortController();
        const timeoutId = window.setTimeout(async () => {
            if (requestVersion !== suggestionRequestVersion.current) {
                return;
            }

            setIsSuggesting(true);

            try {
                const items = await fetchJobSuggestions(normalizedKeyword, controller.signal);

                if (requestVersion !== suggestionRequestVersion.current) {
                    return;
                }

                setSuggestions(items);
                setActiveSuggestionIndex(items.length > 0 ? 0 : -1);
                setIsSuggestionsOpen(items.length > 0);
            } catch {
                if (! controller.signal.aborted && requestVersion === suggestionRequestVersion.current) {
                    clearSuggestions();
                }
            } finally {
                if (requestVersion === suggestionRequestVersion.current) {
                    setIsSuggesting(false);
                }
            }
        }, suggestionDebounceDelay);

        return () => {
            controller.abort();
            window.clearTimeout(timeoutId);
        };
    }, [keyword]);

    const openSuggestions = (): void => {
        if (suggestions.length > 0) {
            setIsSuggestionsOpen(true);
        }
    };

    const closeSuggestions = (): void => {
        setIsSuggestionsOpen(false);
    };

    const clearSuggestions = (): void => {
        setSuggestions([]);
        setIsSuggestionsOpen(false);
        setActiveSuggestionIndex(-1);
        setIsSuggesting(false);
    };

    return {
        suggestions,
        isSuggesting,
        isSuggestionsOpen,
        activeSuggestionIndex,
        setActiveSuggestionIndex,
        openSuggestions,
        closeSuggestions,
        clearSuggestions,
    };
}
