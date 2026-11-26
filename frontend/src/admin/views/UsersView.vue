<script setup>
import { onMounted, ref } from 'vue'
import api from '@/services/api'
import PageHeading from '../components/PageHeading.vue'

const results = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
const drafts = ref({})
const search = ref('')
const loading = ref(false)
const savingId = ref(null)
const message = ref('')
const errorMessage = ref('')

async function loadUsers(page = 1) {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/users', { params: { page, search: search.value || undefined } })
    results.value = data
    drafts.value = Object.fromEntries(data.data.map((user) => [user.id, user.role]))
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not load users.'
  } finally {
    loading.value = false
  }
}

async function saveRole(user) {
  savingId.value = user.id
  message.value = ''
  errorMessage.value = ''
  try {
    const { data } = await api.patch(`/admin/users/${user.id}/role`, { role: drafts.value[user.id] })
    user.role = data.user.role
    message.value = `${user.name}'s role was updated.`
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Could not update this role.'
  } finally {
    savingId.value = null
  }
}

onMounted(() => loadUsers())
</script>

<template>
  <PageHeading title="Users & roles" description="Review accounts and control who can administer the store." />
  <div v-if="message" class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ message }}</div>
  <div v-if="errorMessage" role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</div>
  <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">Administrator access gives full control of orders, customers, menu items, and settings. Only promote people you trust.</div>
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <form class="flex gap-3 border-b border-slate-200 p-4" @submit.prevent="loadUsers(1)"><input v-model="search" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm" placeholder="Search name or email"><button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white">Search</button></form>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[650px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Account</th><th class="px-5 py-4">Joined</th><th class="px-5 py-4">Role</th><th class="px-5 py-4"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="user in results.data" :key="user.id">
            <td class="px-5 py-4"><p class="font-bold">{{ user.name }}</p><p class="mt-1 text-xs text-slate-500">{{ user.email }}</p></td>
            <td class="px-5 py-4 text-slate-500">{{ new Date(user.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-4"><select v-model="drafts[user.id]" class="rounded-lg border border-slate-300 px-3 py-2 capitalize"><option value="customer">Customer</option><option value="admin">Administrator</option></select></td>
            <td class="px-5 py-4 text-right"><button :disabled="savingId === user.id || drafts[user.id] === user.role" class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white disabled:opacity-40" @click="saveRole(user)">{{ savingId === user.id ? 'Saving…' : 'Save role' }}</button></td>
          </tr>
          <tr v-if="loading || !results.data.length"><td colspan="4" class="p-10 text-center text-slate-500">{{ loading ? 'Loading users…' : 'No users found.' }}</td></tr>
        </tbody>
      </table>
    </div>
    <footer class="flex items-center justify-between border-t border-slate-200 px-5 py-4 text-sm"><span class="text-slate-500">{{ results.total }} users · Page {{ results.current_page }} of {{ results.last_page }}</span><div class="flex gap-2"><button :disabled="loading || results.current_page <= 1" class="rounded-lg border px-3 py-2 font-semibold disabled:opacity-40" @click="loadUsers(results.current_page - 1)">Previous</button><button :disabled="loading || results.current_page >= results.last_page" class="rounded-lg border px-3 py-2 font-semibold disabled:opacity-40" @click="loadUsers(results.current_page + 1)">Next</button></div></footer>
  </section>
</template>
