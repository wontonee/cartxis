import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface Currency {
    code: string;
    symbol: string;
    symbolPosition: 'before' | 'after';
    decimalPlaces: number;
}

/**
 * Currency composable for formatting prices
 * Uses the default currency from Inertia shared props
 */
export function useCurrency() {
    const page = usePage();
    
    const currency = computed<Currency | null>(() => {
        return (page.props.currency as Currency) || null;
    });

    /**
     * Format a price with the default currency
     * @param amount - The amount to format
     * @param options - Optional formatting options
     */
    const formatPrice = (amount: number, options?: {
        decimals?: number;
        showSymbol?: boolean;
    }): string => {
        const curr = currency.value;
        
        if (!curr) {
            // Fallback if no currency is set
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: options?.decimals ?? 2,
                maximumFractionDigits: options?.decimals ?? 2,
            }).format(amount);
        }

        const decimals = options?.decimals ?? curr.decimalPlaces;
        const showSymbol = options?.showSymbol !== false;
        
        // Format the number
        const formattedAmount = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(amount);

        if (!showSymbol) {
            return formattedAmount;
        }

        // Add currency symbol based on position
        return curr.symbolPosition === 'before'
            ? `${curr.symbol}${formattedAmount}`
            : `${formattedAmount}${curr.symbol}`;
    };

    /**
     * Format a price with currency code instead of symbol
     * @param amount - The amount to format
     */
    const formatPriceWithCode = (amount: number): string => {
        const curr = currency.value;
        
        if (!curr) {
            return `USD ${amount.toFixed(2)}`;
        }

        const formattedAmount = amount.toFixed(curr.decimalPlaces);
        return `${curr.code} ${formattedAmount}`;
    };

    /**
     * Get the currency symbol
     */
    const getSymbol = (): string => {
        return currency.value?.symbol || '$';
    };

    /**
     * Get the currency code
     */
    const getCode = (): string => {
        return currency.value?.code || 'USD';
    };

    return {
        currency,
        formatPrice,
        formatPriceWithCode,
        getSymbol,
        getCode,
    };
}
