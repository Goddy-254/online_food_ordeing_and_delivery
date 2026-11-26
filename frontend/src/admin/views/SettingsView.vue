<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'

const settings = reactive({ delivery_fee: 0, operating_hours: '', store_open: true })
const loading = ref(true)
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

async function loadSettings() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/settings')
    Object.assign(settings, {
      delivery_fee: Number(data.delivery_fee || 0),
      operating_hours: data.operating_hours || '',
      store_open: data.store_open === '1' || data.store_open === true,
    })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load store settings.'
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const { data } = await api.put('/admin/settings', settings)
    Object.assign(settings, {
      delivery_fee: Number(data.settings.delivery_fee || 0),
      operating_hours: data.settings.operating_hours || '',
      store_open: data.settings.store_open === '1' || data.settings.store_open === true,
    })
    successMessage.value = data.message
  } catch (error) {
    errorMessage.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat()[0] || 'Could not save settings.'
  } finally {
    saving.value = false
  }
}

onMounted(loadSettings)
</script>

<template>
  <PageHeading title="Store settings" description="Control the delivery fee, published hours, and whether customers can place orders." />
  <div v-if="successMessage" class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ successMessage }}</div>
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>
  <div v-if="loading" class="rounded-2xl bg-white p-8 text-slate-500">Loading settings…</div>
  <form v-else class="max-w-3xl space-y-6" @submit.prevent="saveSettings">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="mb-6"><h2 class="text-lg font-black">Delivery</h2><p class="mt-1 text-sm text-slate-500">This fee will be added to new customer orders and saved with each order.</p></div>
      <label class="block max-w-sm text-sm font-semibold text-slate-700">Delivery fee (KSh)<input v-model.number="settings.delivery_fee" type="number" min="0" step="0.01" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="mb-6"><h2 class="text-lg font-black">Hours & availability</h2><p class="mt-1 text-sm text-slate-500">Customers can see your hours; closing the store blocks new orders on the server.</p></div>
      <label class="block text-sm font-semibold text-slate-700">Published operating hours<input v-model.trim="settings.operating_hours" maxlength="255" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="Every day, 9:00 AM – 9:00 PM"></label>
      <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4"><input v-model="settings.store_open" type="checkbox" class="mt-1 h-4 w-4 accent-emerald-600"><span><span class="block font-bold">Accepting orders</span><span class="mt-1 block text-sm text-slate-500">Turn this off to prevent new orders while the store is closed.</span></span></label>
    </section>
    <button :disabled="saving" class="rounded-xl bg-slate-950 px-6 py-3 font-bold text-white hover:bg-emerald-700 disabled:opacity-50">{{ saving ? 'Saving…' : 'Save settings' }}</button>
  </form>
</template>
