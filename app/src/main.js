import Vue from 'vue'
import App from './App.vue'
import router from '@/boot/router'
import axios from 'axios'
import { apiClient } from '@/boot/api'
import store from '@/boot/store'

const token = localStorage.getItem('auth_token')
if (token) {
  store.commit('auth/SET_TOKEN', token)
  apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

new Vue({
  router,
  store,
  axios,
  render: h => h(App)
}).$mount('#app')
