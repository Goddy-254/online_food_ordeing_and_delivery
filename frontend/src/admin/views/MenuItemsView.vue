<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'

const items = ref([])
const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const editingId = ref(null)
const form = reactive({ name: '', category_id: '', price: '', image: '', is_available: true })
const currency = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 2 })

async function loadData() {
  loading.value = true
  try {
    const [menuResponse, categoryResponse] = await Promise.all([
      api.get('/admin/menu-items'),
      api.get('/admin/categories'),
    ])
    items.value = menuResponse.data.data || []
    categories.value = categoryResponse.data.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load menu items.'
  } finally {
    loading.value = false
  }
}

function resetForm() {
  editingId.value = null
  Object.assign(form, { name: '', category_id: '', price: '', image: '', is_available: true })
}

function editItem(item) {
  editingId.value = item.id
  Object.assign(form, { name: item.name, category_id: String(item.category_id), price: item.price, image: item.image || '', is_available: item.is_available })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function saveItem() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  const payload = { ...form, category_id: Number(form.category_id), price: Number(form.price), image: form.image || null }
  try {
    const response = editingId.value
      ? await api.put(`/admin/menu-items/${editingId.value}`, payload)
      : await api.post('/admin/menu-items', payload)
    successMessage.value = response.data.message
    resetForm()
    await loadData()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat()[0] || 'Could not save menu item.'
  } finally {
    saving.value = false
  }
}

async function removeItem(item) {
  if (!window.confirm(`Remove “${item.name}” from the menu?`)) return
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const { data } = await api.delete(`/admin/menu-items/${item.id}`)
    successMessage.value = data.message
    await loadData()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not remove this item. It may already be part of an order.'
  }
}

async function toggleAvailability(item) {
  errorMessage.value = ''
  try {
    await api.patch(`/admin/menu-items/${item.id}`, { is_available: !item.is_available })
    item.is_available = !item.is_available
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not change item availability.'
  }
}

onMounted(loadData)
</script>

<template>
  <PageHeading title="Menu items" description="Create, update, and remove dishes shown on the customer menu." />
  <div v-if="successMessage" class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ successMessage }}</div>
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>

  <section class="mb-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="mb-5 flex items-center justify-between gap-3"><div><h2 class="text-lg font-black">{{ editingId ? 'Edit menu item' : 'Add menu item' }}</h2><p class="mt-1 text-sm text-slate-500">Prices are stored in Kenyan shillings.</p></div><button v-if="editingId" class="text-sm font-bold text-slate-500 hover:text-slate-900" @click="resetForm">Cancel edit</button></div>
    <form class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="saveItem">
      <label class="text-sm font-semibold text-slate-700">Item name<input v-model.trim="form.name" required maxlength="255" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5"></label>
      <label class="text-sm font-semibold text-slate-700">Category<select v-model="form.category_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5"><option value="" disabled>Select a category</option><option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.name }}</option></select></label>
      <label class="text-sm font-semibold text-slate-700">Price (KSh)<input v-model.number="form.price" type="number" min="0" step="1" required class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5"></label>
      <label class="text-sm font-semibold text-slate-700">Image URL<input v-model.trim="form.image" type="url" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="https://…"></label>
      <label class="flex items-center gap-3 text-sm font-semibold text-slate-700"><input v-model="form.is_available" type="checkbox" class="h-4 w-4 accent-emerald-600">Available on customer menu</label>
      <div class="flex gap-2 md:col-span-2 xl:col-span-4"><button :disabled="saving || !categories.length" class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-50">{{ saving ? 'Saving…' : editingId ? 'Save changes' : 'Add item' }}</button><p v-if="!categories.length" class="self-center text-sm text-amber-700">Add a category before creating menu items.</p></div>
    </form>
  </section>

  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-black">Current menu <span class="ml-1 font-medium text-slate-400">{{ items.length }}</span></h2></div>
    <div v-if="loading" class="p-8 text-center text-slate-500">Loading menu…</div>
    <div v-else-if="!items.length" class="p-10 text-center text-slate-500">There are no menu items yet.</div>
    <div v-else class="divide-y divide-slate-100">
      <article v-for="item in items" :key="item.id" class="flex flex-wrap items-center gap-4 px-5 py-4">
        <img v-if="item.image" :src="item.image" :alt="item.name" class="h-14 w-14 rounded-xl object-cover" />
        <div v-else class="grid h-14 w-14 place-items-center rounded-xl bg-emerald-50 text-xl font-black text-emerald-700">{{ item.name?.charAt(0) }}</div>
        <div class="min-w-0 flex-1"><p class="truncate font-bold text-slate-900">{{ item.name }}</p><p class="mt-1 text-sm text-slate-500">{{ item.category?.name || 'Uncategorized' }} · <span :class="item.is_available ? 'text-emerald-700' : 'text-slate-400'">{{ item.is_available ? 'Available' : 'Hidden' }}</span></p></div>
        <p class="font-black">{{ currency.format(Number(item.price)) }}</p>
        <div class="flex gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold hover:bg-slate-50" @click="editItem(item)">Edit</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold hover:bg-slate-50" @click="toggleAvailability(item)">{{ item.is_available ? 'Hide' : 'Show' }}</button><button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50" @click="removeItem(item)">Remove</button></div>
      </article>
    </div>
  </section>
</template>
