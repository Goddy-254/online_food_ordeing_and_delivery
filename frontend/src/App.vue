<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useMenuStore } from '@/stores/menuStore'
import Navbar from '@/components/Navbar.vue'
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'

const menuStore = useMenuStore()
const route = useRoute()
const { categories, loading, error } = storeToRefs(menuStore)

// Keep customer navigation and its category request out of the admin portal.
onMounted(() => {
  if (!route.path.startsWith('/admin')) menuStore.fetchCategories()
})
</script>

<template>
  <header>
      <Navbar v-if="!route.path.startsWith('/admin')" />
      <!-- <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
      </nav> -->
  </header>

  <RouterView />
</template>


