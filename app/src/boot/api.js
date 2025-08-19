import axios from 'axios'
import store from '@/boot/store'

export const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_ROOT_URL,
  timeout: 60000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

apiClient.interceptors.request.use(config => {
  let token = store.state.auth.token
  if (!token && typeof window !== 'undefined') {
    token = localStorage.getItem('auth_token')
  }

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
}, error => Promise.reject(error))

apiClient.interceptors.response.use(
  response => response.data ? response.data : response,
  error => Promise.reject(error)
)

export default { apiClient }
