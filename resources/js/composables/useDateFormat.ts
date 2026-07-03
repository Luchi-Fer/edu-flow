export type UseDateFormatReturn = {
    formatDate: (
        value?: string | null,
        options?: Intl.DateTimeFormatOptions,
    ) => string;
    toDateInputValue: (value?: string | null) => string;
};

const DEFAULT_OPTIONS: Intl.DateTimeFormatOptions = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
};

export function formatDate(
    value?: string | null,
    options: Intl.DateTimeFormatOptions = DEFAULT_OPTIONS,
): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('es-AR', options).format(date);
}

/**
 * Extract the `YYYY-MM-DD` portion an `<input type="date">` needs — Laravel's
 * `date` cast serializes to a full ISO datetime (e.g.
 * `2010-05-01T00:00:00.000000Z`), which the input silently rejects and
 * renders blank.
 */
export function toDateInputValue(value?: string | null): string {
    if (!value) {
        return '';
    }

    return value.slice(0, 10);
}

export function useDateFormat(): UseDateFormatReturn {
    return { formatDate, toDateInputValue };
}
