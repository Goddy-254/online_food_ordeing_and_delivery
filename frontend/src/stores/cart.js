
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {

    // 1. STATE
    const cartItems = ref([])


    // 2. ADD FOOD TO CART
    const addToCart = (food) => {

        // Check whether food already exists in cart
        const existingItem = cartItems.value.find(
            item => item.id === food.id
        )

        if (existingItem) {

            // Increase quantity if food already exists
            existingItem.quantity++

        } else {

            // Add new food with quantity 1
            cartItems.value.push({
                ...food,
                quantity: 1
            })

        }
    }


    // 3. REMOVE FOOD FROM CART
    const removeFromCart = (foodId) => {

        cartItems.value = cartItems.value.filter(
            item => item.id !== foodId
        )

    }


    // 4. INCREASE QUANTITY
    const increaseQuantity = (foodId) => {

        const item = cartItems.value.find(
            item => item.id === foodId
        )

        if (item) {
            item.quantity++
        }

    }


    // 5. DECREASE QUANTITY
    const decreaseQuantity = (foodId) => {

        const item = cartItems.value.find(
            item => item.id === foodId
        )

        if (item && item.quantity > 1) {
            item.quantity--
        }

    }


    // 6. CALCULATE TOTAL PRICE
    const cartTotal = computed(() => {

        return cartItems.value.reduce(
            (total, item) => total + Number(item.price) * item.quantity,
            0
        )

    })


    // 7. COUNT TOTAL ITEMS
    const cartCount = computed(() => {

        return cartItems.value.reduce(
            (count, item) => count + item.quantity,
            0
        )

    })

// after the order has already been created
const clearCart = () => {
  cartItems.value = []
}



    // RETURN STATE, ACTIONS AND COMPUTED VALUES
    return {
        cartItems,
        addToCart,
        removeFromCart,
        increaseQuantity,
        decreaseQuantity,
        clearCart,
        cartTotal,
        cartCount
    }

})
