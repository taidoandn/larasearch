import type { JobSuggestionItem } from '@/features/jobs/types';
import { suggest as jobsSuggest } from '@/routes/jobs';

export async function fetchSuggestions(
    keyword: string,
    signal: AbortSignal,
): Promise<JobSuggestionItem[]> {
    const response = await window.fetch(jobsSuggest.url({
        query: {
            q: keyword,
        },
    }), {
        headers: {
            Accept: 'application/json',
        },
        signal,
    });

    if (! response.ok) {
        throw new Error('Suggest request failed');
    }

    const payload = await response.json() as { items?: JobSuggestionItem[] };

    return Array.isArray(payload.items) ? payload.items.slice(0, 5) : [];
}
