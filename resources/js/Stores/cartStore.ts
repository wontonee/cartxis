import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export interface CartItem {
    id: string;
    product_id: number;
    product_name: string;
    product_slug: string;
    product_image: string | null;
    price: number;
    quantity: number;
    attributes?: Record<string, string>;
}

export interface CartState {
    items: CartItem[];
    loading: boolean;
    error: string | null;
}

export const useCartStore = defineStore('cart', () => {
    // State
    const items = ref<CartItem[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const itemCount = computed(() => {
        return items.value.reduce((total, item) => total + item.quantity, 0);
    });

    const subtotal = computed(() => {
        return items.value.reduce((total, item) => total + (item.price * item.quantity), 0);
    });

    const isEmpty = computed(() => items.value.length === 0);

    // Normalize API cart item fields to match CartItem interface
    const normalizeItems = (rawItems: any[]): CartItem[] => {
        return rawItems.map((item: any) => ({
            ...item,
            product_name: item.product_name || item.name || '',
            product_slug: item.product_slug || item.slug || '',
            product_image: item.product_image || item.image || null,
        }));
    };

    // Actions
    const fetchCart = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/cart', {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch cart');
            }

            const data = await response.json();
            items.value = normalizeItems(data.items || []);
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Unknown error';
            console.error('Cart fetch error:', e);
        } finally {
            loading.value = false;
        }
    };

    const addToCart = async (
        productId: number,
        quantity: number = 1,
        attributes?: Record<number, number>
    ) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity,
                    attributes,
                }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to add to cart');
            }

            const data = await response.json();
            items.value = normalizeItems(data.items || []);

            return { success: true, message: data.message || 'Added to cart successfully' };
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Unknown error';
            console.error('Add to cart error:', e);
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    // Alias for addToCart to support different naming conventions
    const addItem = addToCart;

    const updateQuantity = async (itemId: string | number, quantity: number) => {
        if (quantity < 1) {
            return removeItem(itemId);
        }

        // Optimistic update - update UI immediately
        const itemIndex = items.value.findIndex(item => item.id === itemId);
        if (itemIndex === -1) {
            return { success: false, message: 'Item not found' };
        }

        const previousQuantity = items.value[itemIndex].quantity;
        items.value[itemIndex].quantity = quantity;

        error.value = null;

        try {
            const response = await fetch(`/api/cart/update/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ quantity }),
            });

            if (!response.ok) {
                // Revert on error
                items.value[itemIndex].quantity = previousQuantity;
                throw new Error('Failed to update quantity');
            }

            const data = await response.json();
            items.value = normalizeItems(data.items || []);

            return { success: true };
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Unknown error';
            console.error('Update quantity error:', e);
            return { success: false };
        }
    };

    const removeItem = async (itemId: string | number) => {
        // Optimistic update - remove from UI immediately
        const itemIndex = items.value.findIndex(item => item.id === itemId);
        if (itemIndex === -1) {
            return { success: false, message: 'Item not found' };
        }

        const removedItem = items.value[itemIndex];
        items.value.splice(itemIndex, 1);

        error.value = null;

        try {
            const response = await fetch(`/api/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (!response.ok) {
                // Revert on error
                items.value.splice(itemIndex, 0, removedItem);
                throw new Error('Failed to remove item');
            }

            const data = await response.json();
            items.value = normalizeItems(data.items || []);

            return { success: true };
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Unknown error';
            console.error('Remove item error:', e);
            return { success: false };
        }
    };

    const clearCart = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/cart/clear', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to clear cart');
            }

            items.value = [];

            return { success: true };
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Unknown error';
            console.error('Clear cart error:', e);
            return { success: false };
        } finally {
            loading.value = false;
        }
    };

    return {
        // State
        items,
        loading,
        error,
        // Getters
        itemCount,
        subtotal,
        isEmpty,
        // Actions
        fetchCart,
        addToCart,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
    };
});
