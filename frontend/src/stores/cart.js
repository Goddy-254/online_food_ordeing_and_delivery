import { computed, ref, watch } from 'vue'
import { defineStore } from 'pinia'

const savedCart = () => {
  try {
    return JSON.parse(localStorage.getItem('savor-cart') || '[]')
  } catch {
    return []
  }
}

export const useCartStore = defineStore('cart', () => {
  const items = ref(savedCart())
  const count = computed(() => items.value.reduce((total, item) => total + item.quantity, 0))
  const total = computed(() => items.value.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0))

  function addItem(product) {
    const existing = items.value.find((item) => item.id === product.id)
    if (existing) existing.quantity += 1
    else items.value.push({ ...product, quantity: 1 })
  }

  function decrease(id) {
    const existing = items.value.find((item) => item.id === id)
    if (!existing) return
    if (existing.quantity <= 1) removeItem(id)
    else existing.quantity -= 1
  }

  function removeItem(id) {
    items.value = items.value.filter((item) => item.id !== id)
  }

  function clearCart() {
    items.value = []
  }

  watch(items, (value) => localStorage.setItem('savor-cart', JSON.stringify(value)), { deep: true })

  return { items, count, total, addItem, decrease, removeItem, clearCart }
})
