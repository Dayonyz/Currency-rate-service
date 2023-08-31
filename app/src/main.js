import Vue from 'vue'
import App from './App.vue'
import router from '@/boot/router'
import store from '@/boot/store'
import axios from 'axios'

new Vue({
  router,
  store,
  axios,
  render: h => h(App)
}).$mount('#app')
