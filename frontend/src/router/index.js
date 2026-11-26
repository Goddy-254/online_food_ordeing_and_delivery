
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { customerArea: true },
    },

    {
      path: '/menu',
      name: 'menu',
      component: () => import('../views/menuView.vue'),
      meta: { customerArea: true },
    },
    {
      path: '/cart',
      name: 'cart',
      component: () => import('../views/CartView.vue'),
      meta: { customerArea: true },
    },


    {
      path: '/checkout',
      name: 'checkout',
      component: () => import('../views/CheckOutView.vue'),
      meta: { customerArea: true, requiresAuth: true },
    },

    {
      path: '/login',
      name: 'login',
      component: () => import('../views/Loginview.vue'),
      meta: { customerArea: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { customerArea: true },
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('../admin/views/AdminLoginView.vue'),
    },
    {
      path: '/admin',
      component: () => import('../admin/layouts/AdminLayout.vue'),
      meta: { requiresAdmin: true },
      children: [
        { path: '', name: 'admin-dashboard', component: () => import('../admin/views/DashboardView.vue') },
        { path: 'orders', name: 'admin-orders', component: () => import('../admin/views/OrdersView.vue') },
        { path: 'orders/:id', name: 'admin-order-details', component: () => import('../admin/views/OrderDetailsView.vue') },
        { path: 'menu', name: 'admin-menu', component: () => import('../admin/views/MenuItemsView.vue') },
        { path: 'categories', name: 'admin-categories', component: () => import('../admin/views/CategoriesView.vue') },
        { path: 'customers', name: 'admin-customers', component: () => import('../admin/views/CustomersView.vue') },
        { path: 'customers/:id', name: 'admin-customer-details', component: () => import('../admin/views/CustomerDetailsView.vue') },
        { path: 'users', name: 'admin-users', component: () => import('../admin/views/UsersView.vue') },
        { path: 'settings', name: 'admin-settings', component: () => import('../admin/views/SettingsView.vue') },
      ],
    },

  ],
})

// The role snapshot is only for navigation UX; Laravel middleware remains the security boundary.
function hasAdminSession() {
  try {
    return Boolean(localStorage.getItem('admin_token') && JSON.parse(localStorage.getItem('admin_user') || 'null')?.role === 'admin')
  } catch {
    return false
  }
}

router.beforeEach((to) => {
  const isAdmin = hasAdminSession()

  if (to.meta.requiresAdmin && !isAdmin) {
    return {
      name: 'admin-login',
      query: { redirect: to.fullPath },
    }
  }

  if (to.meta.customerArea && isAdmin) {
    return { name: 'admin-dashboard' }
  }

  if (to.name === 'admin-login' && isAdmin) {
    return { name: 'admin-dashboard' }
  }

  if (to.meta.requiresAuth && !localStorage.getItem('token')) {
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }
})

export default router
