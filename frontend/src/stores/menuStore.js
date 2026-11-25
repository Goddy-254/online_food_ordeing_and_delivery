
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useMenuStore = defineStore('menu', () => {

    // State
    const categories = ref([])
    const menuItems = ref([])

    const loading = ref(false)
    const error = ref(null)

    // Fetch categories
    const fetchCategories = async () => {
        try {
            loading.value = true
            error.value = null

            const response = await api.get('/categories')

            categories.value = response.data.data ?? response.data

        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch categories'

            console.error(err)

        } finally {
            loading.value = false
        }
    }

    // Fetch menu items
    const fetchMenuItems = async () => {
        try {
            loading.value = true
            error.value = null

            const response = await api.get('/menu-items')

            menuItems.value = response.data.data ?? response.data

        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch menu items'

            console.error(err)

        } finally {
            loading.value = false
        }
    }

    return {
        categories,
        menuItems,
        loading,
        error,
        fetchCategories,
        fetchMenuItems
    }
})
