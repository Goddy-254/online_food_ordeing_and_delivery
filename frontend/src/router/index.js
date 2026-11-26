
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },

    {
      path: '/menu',
      name: 'menu',
      component: () => import('../views/menuView.vue'),
    },
    {
      path: '/cart',
      name: 'cart',
      component: () => import('../views/CartView.vue'),
    },


    {
      path: '/checkout',
      name: 'checkout',
      component: () => import('../views/CheckOutView.vue'),
      meta: { requiresAuth: true },
    },

    {
      path: '/login',
      name: 'login',
      component: () => import('../views/Loginview.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
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

router.beforeEach((to) => {
  if (to.meta.requiresAdmin && !localStorage.getItem('admin_token')) {
    return {
      name: 'admin-login',
      query: { redirect: to.fullPath },
    }
  }

  if (to.name === 'admin-login' && localStorage.getItem('admin_token')) {
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
