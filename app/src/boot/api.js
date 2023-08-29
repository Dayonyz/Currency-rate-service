import axios from 'axios'

const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_ROOT_URL,
  withCredentials: false,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 60 * 1000,
})

apiClient.interceptors.request.use(
  (config) => {
    const token =
      typeof window !== 'undefined'
        ? localStorage.getItem('auth._token.local')
        : null
    if (token) {
      config.headers.Authorization = token
    }
    return config
  },
  function (error) {
    return Promise.reject(error)
  }
)

apiClient.interceptors.response.use(
  function (response) {
    return response
  },
  function (error) {
    if (error.constructor.name === 'Cancel') {
      return
    }

    const errors = (((error || {}).response || {}).data || {}).errors || null

    if (errors) {
      return Promise.reject(errors)
    }

    let message = (((error || {}).response || {}).data || {}).message || error
    const seeCode =
      (((error || {}).response || {}).data || {}).see_code || false
    const code = (((error || {}).response || {}).data || {}).status_code || 400

    if (seeCode) {
      message = { message, code }
    }
    return Promise.reject(message)
  }
)

export default { apiClient }