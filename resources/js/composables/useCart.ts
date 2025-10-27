import { useCartStore } from '@/Stores/cartStore';
import { storeToRefs } from 'pinia';

/**
 * Reusable cart composable for easy cart management
 * Can be used in any component across themes
 */
export function useCart() {
    const cartStore = useCartStore();
    const { 
        items, 
        loading, 
        error, 
        itemCount, 
        subtotal, 
        isEmpty,
    } = storeToRefs(cartStore);

    /**
     * Add product to cart
     * @param productId - Product ID
     * @param quantity - Quantity (default: 1)
     * @param attributes - Product attributes (color, size, etc.)
     */
    const addToCart = async (
        productId: number,
        quantity: number = 1,
        attributes?: Record<number, number>
    ) => {
        return await cartStore.addToCart(productId, quantity, attributes);
    };

    /**
     * Update cart item quantity
     * @param itemId - Cart item ID
     * @param quantity - New quantity
     */
    const updateQuantity = async (itemId: number, quantity: number) => {
        return await cartStore.updateQuantity(itemId, quantity);
    };

    /**
     * Remove item from cart
     * @param itemId - Cart item ID
     */
    const removeItem = async (itemId: number) => {
        return await cartStore.removeItem(itemId);
    };

    /**
     * Clear all cart items
     */
    const clearCart = async () => {
        return await cartStore.clearCart();
    };

    /**
     * Fetch cart items
     */
    const fetchCart = async () => {
        return await cartStore.fetchCart();
    };

    return {
        // State
        items,
        loading,
        error,
        // Computed
        itemCount,
        subtotal,
        isEmpty,
        // Actions
        addToCart,
        updateQuantity,
        removeItem,
        clearCart,
        fetchCart,
    };
}
