import axios from 'axios'
import store from '@/boot/store' // чтобы можно было брать токен из Vuex

export const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_ROOT_URL,
  timeout: 60000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Перехват запроса
apiClient.interceptors.request.use(config => {
  // Берем токен сначала из Vuex
  let token = store.state.auth.token
  // если в state нет токена (например, после перезагрузки) — из localStorage
  if (!token && typeof window !== 'undefined') {
    token = localStorage.getItem('auth_token')
  }

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
}, error => Promise.reject(error))

// Перехват ответа
apiClient.interceptors.response.use(
  response => response.data ? response.data : response,
  error => Promise.reject(error)
)

export default { apiClient }
