import Vue from 'vue'
import App from './App.vue'
import router from '@/boot/router'
import store from '@/store/index'
import axios from 'axios'
import vuetify from '@/boot/vuetify'

new Vue({
  router,
  store,
  axios,
  vuetify,
  render: h => h(App)
}).$mount('#app')
