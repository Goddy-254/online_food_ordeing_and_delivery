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

      <!-- Simple service highlights sit just below the hero as a quick trust-building summary. -->
      <div class="grid gap-3 pt-5 sm:grid-cols-3 lg:gap-4 lg:pt-6">
        <div class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-2xl ring-1 ring-emerald-100">🥬</span><div><p class="font-extrabold text-slate-900">Prepared to order</p><p class="mt-1 text-sm text-slate-500">Made fresh when you order</p></div></div>
        <div class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-2xl ring-1 ring-orange-100">🛵</span><div><p class="font-extrabold text-slate-900">Delivered to you</p><p class="mt-1 text-sm text-slate-500">Add delivery details at checkout</p></div></div>
        <div class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-sky-50 text-2xl ring-1 ring-sky-100">📱</span><div><p class="font-extrabold text-slate-900">Easy online ordering</p><p class="mt-1 text-sm text-slate-500">Browse, add to cart, and check out</p></div></div>
      </div>
    </section>

    <!-- Category shortcuts keep browsing simple on small and large screens. -->
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
      <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-[.16em] text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-600" />Find your craving</p><h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl lg:text-[2.75rem]">What sounds good?</h2><p class="mt-2 max-w-xl text-sm leading-6 text-slate-500 sm:text-base">Pick a category and find the thing you’ve been craving.</p></div>
        <RouterLink to="/menu" class="hidden items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-extrabold text-slate-800 transition hover:border-emerald-300 hover:bg-emerald-50 sm:inline-flex">See full menu <span aria-hidden="true">→</span></RouterLink>
      </div>
      <div v-if="categoryCards.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 lg:gap-4">
        <RouterLink v-for="category in categoryCards" :key="category.id" :to="{ name: 'menu', query: { category: category.id } }" class="group relative isolate min-h-44 overflow-hidden rounded-[1.5rem] border border-white/80 p-4 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl sm:min-h-52 sm:p-5" :class="category.color">
          <span class="absolute -right-5 -top-6 -z-10 h-28 w-28 rounded-full bg-white/50 transition duration-500 group-hover:scale-125 sm:h-36 sm:w-36" />
          <div class="flex h-full flex-col justify-between">
            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/80 text-3xl shadow-sm ring-1 ring-white transition duration-300 group-hover:rotate-3 group-hover:scale-105 sm:h-16 sm:w-16 sm:text-4xl">{{ category.icon }}</span>
            <div class="mt-5 flex items-end justify-between gap-2"><div><h3 class="font-black text-slate-900 sm:text-lg">{{ category.name }}</h3><p class="mt-1 text-xs leading-5 text-slate-600">{{ category.description }}</p></div><span class="mb-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/80 text-sm font-bold text-slate-800 transition group-hover:bg-slate-900 group-hover:text-white" aria-hidden="true">↗</span></div>
          </div>
        </RouterLink>
      </div>
      <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">Browse all dishes and categories in our menu.</div>
      <RouterLink to="/menu" class="mt-5 inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-extrabold text-slate-800 sm:hidden">See full menu <span aria-hidden="true">→</span></RouterLink>
    </section>

    <!-- Featured dishes are loaded from the same API used by the menu page. -->
    <section class="relative overflow-hidden border-y border-slate-200/70 bg-white py-14 sm:py-20">
      <div class="pointer-events-none absolute -right-28 top-0 h-72 w-72 rounded-full bg-orange-100/70 blur-3xl" />
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end lg:mb-10">
          <div><p class="inline-flex rounded-full bg-orange-100 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-[.16em] text-orange-800">A taste of the menu</p><h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl lg:text-[2.75rem]">Fresh from our kitchen</h2><p class="mt-2 text-slate-500">A few dishes to get your next meal started.</p></div>
          <RouterLink to="/menu" class="inline-flex items-center gap-2 text-sm font-extrabold text-emerald-800 transition hover:text-emerald-600">Explore every dish <span aria-hidden="true">→</span></RouterLink>
        </div>

        <div v-if="menuStore.menuItemsLoading" class="rounded-3xl border border-slate-200 bg-slate-50 p-10 text-center text-slate-500"><span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-white text-2xl shadow-sm">🍳</span>Loading today’s menu…</div>
        <div v-else-if="featuredItems.length" class="relative grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
          <article v-for="food in featuredItems" :key="food.id" class="group overflow-hidden rounded-[1.6rem] border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="relative h-56 overflow-hidden bg-emerald-50 sm:h-60">
              <img v-if="food.image" :src="food.image" :alt="food.name" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
              <div v-else class="grid h-full place-items-center text-6xl">🍽️</div>
              <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-slate-950/30 to-transparent" />
              <span v-if="food.category?.name" class="absolute left-4 top-4 rounded-full border border-white/70 bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm backdrop-blur">{{ food.category.name }}</span>
            </div>
            <div class="p-5 sm:p-6">
              <div class="min-h-14"><h3 class="text-lg font-black text-slate-900">{{ food.name }}</h3><p class="mt-1 line-clamp-2 text-sm leading-5 text-slate-500">{{ food.description || 'Freshly prepared and ready to enjoy.' }}</p></div>
              <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <p class="text-lg font-black text-emerald-800">{{ currency.format(Number(food.price)) }}</p>
                <button class="inline-flex items-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-200" @click="cartStore.addToCart(food)">Add to cart <span class="grid h-5 w-5 place-items-center rounded-full bg-white/15" aria-hidden="true">+</span></button>
              </div>
            </div>
          </article>
        </div>
        <div v-else class="rounded-3xl border border-slate-200 bg-slate-50 p-9 text-center sm:p-12">
          <span class="text-4xl">🍽️</span><h3 class="mt-3 text-lg font-black">The menu is getting ready</h3><p class="mt-1 text-sm text-slate-500">Visit the full menu to see what’s available.</p><RouterLink to="/menu" class="mt-4 inline-flex rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white">Browse menu</RouterLink>
        </div>
      </div>
    </section>

    <!-- Three short steps make the first order flow clear for new customers. -->
    <section id="how-it-works" class="relative isolate overflow-hidden bg-[#122b22] py-16 text-white sm:py-20">
      <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_80%_0%,_rgba(34,197,94,.16),_transparent_42%)]" />
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center"><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-300">Simple from start to finish</p><h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl lg:text-[2.75rem]">Good food in three easy steps</h2><p class="mt-3 text-sm leading-6 text-emerald-50/70 sm:text-base">From choosing a dish to confirming your order, we keep it straightforward.</p></div>
        <div class="relative mt-10 grid gap-4 md:grid-cols-3 lg:mt-14 lg:gap-6">
          <div class="absolute left-[16.5%] right-[16.5%] top-8 hidden border-t border-dashed border-emerald-100/25 md:block" />
          <article class="relative rounded-3xl border border-white/10 bg-white/[.06] p-6 backdrop-blur-sm transition hover:bg-white/[.09] sm:p-7"><span class="relative z-10 grid h-16 w-16 place-items-center rounded-2xl border border-emerald-200/20 bg-[#17392c] text-xl font-black text-emerald-200 shadow-lg shadow-black/10">01</span><h3 class="mt-6 text-lg font-black">Explore the menu</h3><p class="mt-2 text-sm leading-6 text-emerald-50/65">Browse pizzas, burgers, chicken, drinks, and more.</p></article>
          <article class="relative rounded-3xl border border-white/10 bg-white/[.06] p-6 backdrop-blur-sm transition hover:bg-white/[.09] sm:p-7"><span class="relative z-10 grid h-16 w-16 place-items-center rounded-2xl border border-orange-200/20 bg-[#3d3023] text-xl font-black text-orange-200 shadow-lg shadow-black/10">02</span><h3 class="mt-6 text-lg font-black">Build your order</h3><p class="mt-2 text-sm leading-6 text-emerald-50/65">Choose your favorites and set the quantity that works for you.</p></article>
          <article class="relative rounded-3xl border border-white/10 bg-white/[.06] p-6 backdrop-blur-sm transition hover:bg-white/[.09] sm:p-7"><span class="relative z-10 grid h-16 w-16 place-items-center rounded-2xl border border-sky-200/20 bg-[#203846] text-xl font-black text-sky-200 shadow-lg shadow-black/10">03</span><h3 class="mt-6 text-lg font-black">Check out</h3><p class="mt-2 text-sm leading-6 text-emerald-50/65">Sign in, add your delivery address, and place your order.</p></article>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20">
      <div class="relative -mt-7 flex flex-col items-start justify-between gap-6 overflow-hidden rounded-[2rem] border border-emerald-900/5 bg-emerald-100 p-7 shadow-xl shadow-emerald-950/5 sm:p-10 md:flex-row md:items-center lg:-mt-9 lg:px-14 lg:py-12">
        <div class="pointer-events-none absolute -right-8 -top-16 h-56 w-56 rounded-full border-[28px] border-emerald-200/60" />
        <div class="relative"><p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-800">Ready when you are</p><h2 class="mt-2 max-w-2xl text-3xl font-black leading-tight text-emerald-950 sm:text-4xl">Make your next meal the easy part of your day.</h2></div>
        <RouterLink to="/menu" class="relative inline-flex shrink-0 items-center gap-2 rounded-xl bg-emerald-800 px-6 py-3.5 font-extrabold text-white shadow-lg shadow-emerald-900/15 transition hover:-translate-y-0.5 hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300">Order something good <span aria-hidden="true">→</span></RouterLink>
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
