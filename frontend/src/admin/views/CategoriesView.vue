<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const editingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')
const form = reactive({ name: '', description: '' })

async function loadCategories() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/categories')
    categories.value = data.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load categories.'
  } finally {
    loading.value = false
  }
}

function resetForm() {
  editingId.value = null
  Object.assign(form, { name: '', description: '' })
}

function editCategory(category) {
  editingId.value = category.id
  Object.assign(form, { name: category.name, description: category.description || '' })
}

async function saveCategory() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const response = editingId.value
      ? await api.put(`/admin/categories/${editingId.value}`, form)
      : await api.post('/admin/categories', form)
    successMessage.value = response.data.message
    resetForm()
    await loadCategories()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat()[0] || 'Could not save category.'
  } finally {
    saving.value = false
  }
}

async function removeCategory(category) {
  if (!window.confirm(`Remove the “${category.name}” category?`)) return
  errorMessage.value = ''
  try {
    const { data } = await api.delete(`/admin/categories/${category.id}`)
    successMessage.value = data.message
    await loadCategories()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'This category cannot be removed while it has menu items.'
  }
}

onMounted(loadCategories)
</script>

<template>
  <PageHeading title="Categories" description="Group menu items so customers can browse the menu easily." />
  <div v-if="successMessage" class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ successMessage }}</div>
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>
  <div class="grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
    <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-lg font-black">{{ editingId ? 'Edit category' : 'Add category' }}</h2>
      <form class="mt-5 space-y-4" @submit.prevent="saveCategory">
        <label class="block text-sm font-semibold">Name<input v-model.trim="form.name" required maxlength="255" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5"></label>
        <label class="block text-sm font-semibold">Description<textarea v-model.trim="form.description" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5"></textarea></label>
        <div class="flex gap-2"><button :disabled="saving" class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-50">{{ saving ? 'Saving…' : editingId ? 'Save changes' : 'Add category' }}</button><button v-if="editingId" type="button" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold" @click="resetForm">Cancel</button></div>
      </form>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-black">Categories <span class="font-medium text-slate-400">{{ categories.length }}</span></h2></div>
      <div v-if="loading" class="p-8 text-slate-500">Loading categories…</div>
      <div v-else-if="!categories.length" class="p-8 text-center text-slate-500">No categories yet.</div>
      <div v-else class="divide-y divide-slate-100">
        <article v-for="category in categories" :key="category.id" class="flex items-center justify-between gap-4 px-5 py-4">
          <div class="min-w-0"><p class="font-bold">{{ category.name }}</p><p class="mt-1 truncate text-sm text-slate-500">{{ category.description || 'No description' }}</p><p class="mt-1 text-xs text-slate-400">/{{ category.slug }}</p></div>
          <div class="flex shrink-0 gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold" @click="editCategory(category)">Edit</button><button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700" @click="removeCategory(category)">Remove</button></div>
        </article>
      </div>
    </section>
  </div>
</template>
