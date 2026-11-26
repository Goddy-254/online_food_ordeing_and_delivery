<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from 'vue-router'

import { useMenuStore } from "@/stores/menuStore"

import CategoryList from "@/components/CategoryList.vue";
import FoodCard from "@/components/FoodCard.vue";


const menuStore = useMenuStore();
const route = useRoute()

// Selected category
const selectedCategory = ref(null);

// Honor category shortcuts from the home page and browser navigation.
watch(() => route.query.category, (category) => {
  const id = Array.isArray(category) ? category[0] : category
  selectedCategory.value = id && Number.isFinite(Number(id)) ? Number(id) : null
}, { immediate: true })

// Fetch categories and menu items
const pageLoading = ref(true);

onMounted(async () => {
  try {
    await Promise.all([
      menuStore.fetchCategories(),
      menuStore.fetchMenuItems(),
    ]);
  } finally {
    pageLoading.value = false;
  }
});

// Filter menu items
const filteredMenuItems = computed(() => {
  if (selectedCategory.value === null) {
    return menuStore.menuItems;
  }

  return menuStore.menuItems.filter(
    (item) => Number(item.category_id) === Number(selectedCategory.value),
  );
});

// Handle category selection
const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId;
};
</script>

<template>
  <main class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Page heading -->
      <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900">Our Menu</h1>

        <p class="text-gray-500 mt-2">
          Discover delicious meals prepared just for you.
        </p>
      </div>

      <!-- Loading -->
      <div v-if="pageLoading" class="text-center py-16">
        <p class="text-gray-600">Loading menu...</p>
      </div>

      <!-- Error -->
      <div v-else-if="menuStore.categoriesError || menuStore.menuItemsError">
        <p class="text-red-600">
          {{ menuStore.categoriesError || menuStore.menuItemsError }}
        </p>
      </div>

      <!-- Menu content -->
      <div v-else>
        <!-- Categories -->
        <CategoryList
          :categories="menuStore.categories"
          :selected-category="selectedCategory"
          @select-category="selectCategory"
        />

        <!-- Food items -->
        <section class="mt-10">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Available Meals</h2>

            <span class="text-sm text-gray-500">
              {{ filteredMenuItems.length }} meals
            </span>
          </div>

          <!-- Empty state -->
          <div v-if="filteredMenuItems.length === 0" class="text-center py-16">
            <h3 class="text-xl font-semibold text-gray-700">No meals found</h3>

            <p class="text-gray-500 mt-2">Try selecting another category.</p>
          </div>

          <!-- Food cards -->
          <div
            v-else
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
          >
            <FoodCard
              v-for="food in filteredMenuItems"
              :key="food.id"
              :food="food"
            />
          </div>
        </section>
      </div>
    </div>
  </main>
</template>
