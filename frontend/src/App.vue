<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useMenuStore } from '@/stores/menuStore'
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'

const menuStore = useMenuStore()
const { categories, loading, error } = storeToRefs(menuStore)

onMounted(() => menuStore.fetchCategories())
</script>

<template>
  <header>
      <!-- <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
      </nav> -->
  </header>

  <RouterView />
</template>


