import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Currency = {
    country_id: number | null;
    country_name: string | null;
    code: string;
    symbol: string;
    digits: number;
};

const fallbackCurrency: Currency = {
    country_id: null,
    country_name: null,
    code: 'BDT',
    symbol: '৳',
    digits: 2,
};

export function useCurrency() {
    const page = usePage();

    const currency = computed<Currency>(() => ({
        ...fallbackCurrency,
        ...((page.props.currency as Partial<Currency> | undefined) ?? {}),
    }));

    const money = (value: number | string | null | undefined, empty = '0') => {
        if (value === null || value === undefined || value === '') {
            return empty;
        }

        const digits = Math.max(0, Number(currency.value.digits ?? 2));
        const amount = Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        });

        return `${amount} ${currency.value.symbol}`;
    };

    return { currency, money };
}
