
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useMenuStore = defineStore('menu', () => {

    // State
    const categories = ref([])
    const menuItems = ref([])

    const categoriesError = ref(null)
    const menuItemsError = ref(null)

    const categoriesLoading = ref(false)
    const menuItemsLoading = ref(false)

    // Fetch categories
    const fetchCategories = async () => {
        try {
            categoriesLoading.value = true
            categoriesError.value = null

            const response = await api.get('/categories')

            categories.value = response.data.data ?? response.data

        } catch (err) {
            categoriesError.value =
                err.response?.data?.message ||
                'Failed to fetch categories'

            console.error('Categories error:', err)

        } finally {
            categoriesLoading.value = false
        }
    }

    // Fetch menu items
    const fetchMenuItems = async () => {
        try {
            menuItemsLoading.value = true
            menuItemsError.value = null

            const response = await api.get('/menu-items')

            menuItems.value = response.data.data ?? response.data

        } catch (err) {
            menuItemsError.value =
                err.response?.data?.message ||
                'Failed to fetch menu items'

            console.error('Menu items error:', err)

        } finally {
            menuItemsLoading.value = false
        }
    }

    return {
        categories,
        menuItems,

        categoriesError,
        menuItemsError,

        categoriesLoading,
        menuItemsLoading,

        fetchCategories,
        fetchMenuItems
    }
})

