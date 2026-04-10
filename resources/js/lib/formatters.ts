const mediumDateFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
});

const mediumDateTimeFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
});

export function formatDisplayDate(
    value: string | null | undefined,
    fallback = 'Unknown',
): string {
    const date = parseDateValue(value);

    if (! date) {
        return fallback;
    }

    return mediumDateFormatter.format(date);
}

export function formatDisplayDateTime(
    value: string | null | undefined,
    fallback = 'Unknown',
): string {
    const date = parseDateValue(value);

    if (! date) {
        return fallback;
    }

    return mediumDateTimeFormatter.format(date);
}

function parseDateValue(value: string | null | undefined): Date | null {
    if (! value) {
        return null;
    }

    const parsedValue = new Date(value);

    if (Number.isNaN(parsedValue.getTime())) {
        return null;
    }

    return parsedValue;
}
