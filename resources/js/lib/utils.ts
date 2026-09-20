import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

const displayDateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    const dateOnlyMatch = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
    const date = dateOnlyMatch
        ? new Date(
              Number(dateOnlyMatch[1]),
              Number(dateOnlyMatch[2]) - 1,
              Number(dateOnlyMatch[3]),
          )
        : new Date(value);

    return Number.isNaN(date.getTime())
        ? value
        : displayDateFormatter.format(date);
}
