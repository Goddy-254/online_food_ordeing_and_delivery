import axios from 'axios'

const apiHost = import.meta.env.VITE_API_HOST

const api = axios.create({
    baseURL: apiHost
        ? `https://${apiHost}/api`
        : import.meta.env.VITE_API_URL,
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
    }
})

// Sanctum personal access tokens must be sent on every protected API request.
// Read from localStorage at request time so login/logout changes apply at once.
api.interceptors.request.use((config) => {
    // Admin and customer API calls use separate Sanctum sessions.
    const isAdminRequest = config.url?.startsWith('/admin/')
    const token = localStorage.getItem(isAdminRequest ? 'admin_token' : 'token')

    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    } else {
        delete config.headers.Authorization
    }

    return config
})

export default api
