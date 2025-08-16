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
  const isAuth = !!store.state.auth.token

  if (to.meta.requiresAuth && !isAuth) {
    return next({ name: 'auth' })
  }

  if (to.name === 'auth' && isAuth) {
    if (to.fullPath === from.fullPath) {
      return next(false)
    }

    return next({ name: 'dashboard' })
  }

  next()
})

export default router
