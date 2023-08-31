import axios from 'axios'

export const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_ROOT_URL,
  withCredentials: false,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    mode: 'cors'
  },
  timeout: 60 * 1000,
})

apiClient.interceptors.request.use(
  (config) => {
    const token =
      typeof window !== 'undefined'
        ? localStorage.getItem('auth_token')
        : null
    if (token) {
      config.headers.Authorization = 'Bearer ' + token
    }
    return config
  },
  function (error) {
    return Promise.reject(error)
  }
)

apiClient.interceptors.response.use(
  response => {
    if (response.data) {
      return response.data
    }

    return response
  },
  error => {
    if (!error.response) {
      console.log("Please check your internet connection.");
    }

    return Promise.reject(error)
  }
)

export default { apiClient }