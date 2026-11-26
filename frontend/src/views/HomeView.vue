<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useMenuStore } from '@/stores/menuStore'
import api from '@/services/api'

const menuStore = useMenuStore()
const cartStore = useCartStore()
const storeInfo = ref({ store_open: true, operating_hours: '' })
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 })

const featuredItems = computed(() => menuStore.menuItems.slice(0, 3))

// Give each category a recognizable icon and description without changing API data.
const categoryDetails = {
  pizza: { icon: '🍕', description: 'Fresh from the oven', color: 'bg-orange-50' },
  burgers: { icon: '🍔', description: 'Juicy, stacked favorites', color: 'bg-amber-50' },
  drinks: { icon: '🥤', description: 'A refreshing extra', color: 'bg-sky-50' },
  chicken: { icon: '🍗', description: 'Crispy, tender, tasty', color: 'bg-rose-50' },
  snacks: { icon: '🍟', description: 'Little bites, big flavor', color: 'bg-yellow-50' },
}

const categoryCards = computed(() => menuStore.categories.slice(0, 5).map((category) => ({
  ...category,
  ...(categoryDetails[category.slug] || { icon: '🍽️', description: 'Find your next favorite', color: 'bg-emerald-50' }),
})))

onMounted(async () => {
  // Load real menu and store data so the landing page stays in sync with admin changes.
  await Promise.all([
    menuStore.fetchMenuItems(),
    api.get('/store-info').then(({ data }) => { storeInfo.value = data }).catch(() => {}),
  ])
})
</script>

<template>
  <main class="overflow-hidden bg-[#fbfaf7] text-slate-900">
    <!-- Hero: primary value proposition and direct menu actions. -->
    <section class="mx-auto max-w-7xl px-4 pb-12 pt-6 sm:px-6 lg:px-8 lg:pb-20 lg:pt-10">
      <div class="relative isolate overflow-hidden rounded-[2rem] bg-[#122b22] shadow-2xl shadow-emerald-950/15">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_15%_15%,_rgba(34,197,94,.2),_transparent_45%)]" />
        <div class="grid min-h-[500px] items-center lg:grid-cols-[1.05fr_.95fr]">
          <div class="px-7 py-12 sm:px-12 lg:px-16 lg:py-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-2 text-xs font-bold uppercase tracking-[.14em] text-emerald-200">
              <span class="h-2 w-2 rounded-full" :class="storeInfo.store_open ? 'bg-emerald-400' : 'bg-amber-400'" />
              {{ storeInfo.store_open ? 'Taking orders now' : 'Currently closed' }}
            </div>
            <h1 class="mt-7 max-w-2xl text-5xl font-black leading-[1.04] tracking-tight text-white sm:text-6xl xl:text-7xl">
              Good food.<br>
              <span class="text-emerald-300">Good mood.</span>
            </h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-emerald-50/75 sm:text-lg sm:leading-8">
              Your favorite meals, freshly prepared and ready to come to you. Pick something delicious and place your order in a few taps.
            </p>
            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
              <RouterLink to="/menu" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-400 px-6 py-3.5 font-extrabold text-emerald-950 shadow-lg shadow-emerald-950/20 transition hover:-translate-y-0.5 hover:bg-emerald-300">
                Explore the menu <span aria-hidden="true">→</span>
              </RouterLink>
              <a href="#how-it-works" class="inline-flex items-center justify-center rounded-xl border border-white/20 px-6 py-3.5 font-bold text-white transition hover:bg-white/10">How ordering works</a>
            </div>
            <p v-if="storeInfo.operating_hours" class="mt-6 text-sm text-emerald-50/60">Store hours: {{ storeInfo.operating_hours }}</p>
          </div>

          <div class="relative min-h-[320px] self-stretch lg:min-h-full">
            <img
              src="https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=1200&q=85"
              alt="Freshly baked pizza topped with colorful ingredients"
              class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-[#122b22]/75 via-transparent to-transparent lg:bg-gradient-to-r lg:from-[#122b22]/20 lg:via-transparent lg:to-transparent" />
            <div class="absolute bottom-6 left-6 right-6 flex items-center justify-between gap-3 rounded-2xl border border-white/25 bg-white/90 p-4 shadow-xl backdrop-blur sm:bottom-8 sm:left-8 sm:right-8">
              <div><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Made for your cravings</p><p class="mt-1 font-extrabold text-slate-900">A little joy in every bite</p></div>
              <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-2xl" aria-hidden="true">✦</span>
            </div>
          </div>
        </div>
      </div>

      <!-- A compact benefits strip provides useful context without unsupported promises. -->
      <div class="grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-3 sm:gap-6 lg:py-9">
        <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-xl">🥬</span><div><p class="font-extrabold">Prepared to order</p><p class="text-sm text-slate-500">Made fresh when you order</p></div></div>
        <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-orange-100 text-xl">🛵</span><div><p class="font-extrabold">Delivered to you</p><p class="text-sm text-slate-500">Add your delivery details at checkout</p></div></div>
        <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-sky-100 text-xl">📱</span><div><p class="font-extrabold">Easy online ordering</p><p class="text-sm text-slate-500">Browse, add to cart, and check out</p></div></div>
      </div>
    </section>

    <!-- Category shortcuts keep browsing simple on small and large screens. -->
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
      <div class="mb-7 flex items-end justify-between gap-4">
        <div><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">Find your craving</p><h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">What sounds good?</h2></div>
        <RouterLink to="/menu" class="hidden text-sm font-extrabold text-emerald-800 hover:text-emerald-600 sm:block">See full menu →</RouterLink>
      </div>
      <div v-if="categoryCards.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 lg:gap-4">
        <RouterLink v-for="category in categoryCards" :key="category.id" :to="{ name: 'menu', query: { category: category.id } }" class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg sm:p-5">
          <span class="grid h-14 w-14 place-items-center rounded-2xl text-3xl transition group-hover:scale-105" :class="category.color">{{ category.icon }}</span>
          <h3 class="mt-4 font-black text-slate-900">{{ category.name }}</h3>
          <p class="mt-1 text-xs leading-5 text-slate-500">{{ category.description }}</p>
        </RouterLink>
      </div>
      <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">Browse all dishes and categories in our menu.</div>
      <RouterLink to="/menu" class="mt-5 inline-flex font-extrabold text-emerald-800 sm:hidden">See full menu →</RouterLink>
    </section>

    <!-- Featured dishes are loaded from the same API used by the menu page. -->
    <section class="bg-white py-12 sm:py-16">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
          <div><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">A taste of the menu</p><h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Fresh from our kitchen</h2><p class="mt-2 text-slate-500">A few favorites to get you started.</p></div>
          <RouterLink to="/menu" class="font-extrabold text-emerald-800 hover:text-emerald-600">Explore every dish →</RouterLink>
        </div>

        <div v-if="menuStore.menuItemsLoading" class="rounded-2xl bg-slate-50 p-10 text-center text-slate-500">Loading today’s menu…</div>
        <div v-else-if="featuredItems.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <article v-for="food in featuredItems" :key="food.id" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <div class="relative h-56 overflow-hidden bg-emerald-50">
              <img v-if="food.image" :src="food.image" :alt="food.name" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
              <div v-else class="grid h-full place-items-center text-6xl">🍽️</div>
              <span v-if="food.category?.name" class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm">{{ food.category.name }}</span>
            </div>
            <div class="p-5">
              <h3 class="text-lg font-black text-slate-900">{{ food.name }}</h3>
              <p class="mt-1 text-sm text-slate-500">Freshly prepared and ready to enjoy.</p>
              <div class="mt-5 flex items-center justify-between gap-3">
                <p class="text-lg font-black text-emerald-800">{{ currency.format(Number(food.price)) }}</p>
                <button class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700" @click="cartStore.addToCart(food)">Add to cart <span aria-hidden="true">+</span></button>
              </div>
            </div>
          </article>
        </div>
        <div v-else class="rounded-2xl border border-slate-200 bg-slate-50 p-9 text-center">
          <span class="text-4xl">🍽️</span><h3 class="mt-3 text-lg font-black">The menu is getting ready</h3><p class="mt-1 text-sm text-slate-500">Visit the full menu to see what’s available.</p><RouterLink to="/menu" class="mt-4 inline-flex rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white">Browse menu</RouterLink>
        </div>
      </div>
    </section>

    <!-- Three short steps make the first order flow clear for new customers. -->
    <section id="how-it-works" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
      <div class="mx-auto max-w-2xl text-center"><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">Simple from start to finish</p><h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Good food in three easy steps</h2></div>
      <div class="mt-10 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 font-black text-emerald-800">01</span><h3 class="mt-5 text-lg font-black">Explore the menu</h3><p class="mt-2 text-sm leading-6 text-slate-500">Browse pizzas, burgers, chicken, drinks, and more.</p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="grid h-11 w-11 place-items-center rounded-xl bg-orange-100 font-black text-orange-800">02</span><h3 class="mt-5 text-lg font-black">Build your order</h3><p class="mt-2 text-sm leading-6 text-slate-500">Choose your favorites and set the quantity that works for you.</p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="grid h-11 w-11 place-items-center rounded-xl bg-sky-100 font-black text-sky-800">03</span><h3 class="mt-5 text-lg font-black">Check out</h3><p class="mt-2 text-sm leading-6 text-slate-500">Sign in, add your delivery address, and place your order.</p></article>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20">
      <div class="flex flex-col items-start justify-between gap-6 rounded-[2rem] bg-emerald-100 p-7 sm:p-10 md:flex-row md:items-center lg:px-14">
        <div><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-800">Ready when you are</p><h2 class="mt-2 max-w-2xl text-3xl font-black leading-tight text-emerald-950 sm:text-4xl">Make your next meal the easy part of your day.</h2></div>
        <RouterLink to="/menu" class="shrink-0 rounded-xl bg-emerald-800 px-6 py-3.5 font-extrabold text-white shadow-lg shadow-emerald-900/10 transition hover:-translate-y-0.5 hover:bg-emerald-700">Order something good →</RouterLink>
      </div>
    </section>

    <footer class="border-t border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-7 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p class="font-black text-slate-800">FoodExpress <span class="font-normal text-slate-400">· Good food, good mood.</span></p>
        <div class="flex gap-5"><RouterLink to="/menu" class="hover:text-emerald-800">Menu</RouterLink><RouterLink to="/cart" class="hover:text-emerald-800">Your cart</RouterLink><RouterLink to="/login" class="hover:text-emerald-800">Sign in</RouterLink></div>
      </div>
    </footer>
  </main>
</template>
