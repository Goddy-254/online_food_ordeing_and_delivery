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
    <img alt="Vue logo" class="logo" src="@/assets/logo.svg" width="125" height="125" />

    <div class="wrapper">
      <HelloWorld />

      <!-- <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
      </nav> -->
    </div>
  </header>

  <RouterView />
</template>


