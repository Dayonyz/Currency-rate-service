import routes from '@/routes/index'
import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '@/boot/store'


Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
})

router.beforeEach((to, from, next) => {

  if (!from.name && to.meta.requiresAuth && !store.state.auth.token) {
    next({ name: 'auth' })
    return
  }

  if (!from.name && to.name === 'auth' && store.state.auth.token) {
    next({ name: 'dashboard' })
    return
  }

  if (from.name && to.name && to.requiresAuth && to.params.token) {
    next()
    return
  }

  next()
})

export default router
