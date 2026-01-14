import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { useCart } from '@/composables/useCart'

interface WishlistItem {
  id: number
  product: {
    id: number
    name: string
    slug: string
    price: number
    special_price?: number
    image?: string
    is_in_stock: boolean
  }
  added_at: string
}

const wishlistItems = ref<WishlistItem[]>([])
const loading = ref(false)

export function useWishlist() {
  const page = usePage()
  const wishlistCount = computed(() => wishlistItems.value.length)

  const isInWishlist = (productId: number) => {
    return wishlistItems.value.some(item => item.product.id === productId)
  }

  const fetchWishlist = async () => {
    try {
      loading.value = true
      const response = await axios.get('/account/wishlist/get')
      
      if (response.data) {
        wishlistItems.value = response.data.items || []
      }
    } catch (error) {
      console.error('Error fetching wishlist:', error)
    } finally {
      loading.value = false
    }
  }

  const addToWishlist = async (productId: number) => {
    // Check if user is logged in
    const auth = page.props.auth as any
    if (!auth || !auth.user) {
      router.visit('/login?redirect=' + encodeURIComponent(window.location.pathname))
      return { success: false, message: 'Please login to add items to wishlist' }
    }

    try {
      loading.value = true
      const response = await axios.post('/account/wishlist/add', {
        product_id: productId
      })

      if (response.data && response.data.item) {
        wishlistItems.value.push(response.data.item)
        return { success: true, message: response.data.message || 'Added to wishlist' }
      }
      
      return { success: false, message: 'Failed to add to wishlist' }
    } catch (error: any) {
      console.error('Error adding to wishlist:', error)
      const message = error.response?.data?.message || 'Failed to add to wishlist'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const removeFromWishlist = async (wishlistItemId: number) => {
    try {
      loading.value = true
      const response = await axios.delete(`/account/wishlist/${wishlistItemId}`)

      if (response.status === 200) {
        wishlistItems.value = wishlistItems.value.filter(item => item.id !== wishlistItemId)
        return { success: true, message: 'Removed from wishlist' }
      }
      
      return { success: false, message: 'Failed to remove from wishlist' }
    } catch (error: any) {
      console.error('Error removing from wishlist:', error)
      const message = error.response?.data?.message || 'Failed to remove from wishlist'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const toggleWishlist = async (productId: number) => {
    const item = wishlistItems.value.find(item => item.product.id === productId)
    
    if (item) {
      return await removeFromWishlist(item.id)
    } else {
      return await addToWishlist(productId)
    }
  }

  const moveToCart = async (wishlistItemId: number) => {
    try {
      loading.value = true
      const response = await axios.post(`/account/wishlist/${wishlistItemId}/move-to-cart`)

      if (response.status === 200) {
        wishlistItems.value = wishlistItems.value.filter(item => item.id !== wishlistItemId)
        
        // Refresh cart count
        const { fetchCart } = useCart()
        await fetchCart()
        
        return { success: true, message: response.data.message || 'Moved to cart' }
      }
      
      return { success: false, message: 'Failed to move to cart' }
    } catch (error: any) {
      console.error('Error moving to cart:', error)
      const message = error.response?.data?.message || 'Failed to move to cart'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  return {
    wishlistItems,
    wishlistCount,
    loading,
    isInWishlist,
    fetchWishlist,
    addToWishlist,
    removeFromWishlist,
    toggleWishlist,
    moveToCart
  }
}
