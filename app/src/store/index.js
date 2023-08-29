import auth from '@/store/auth'
import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from 'vuex-persistedstate'

Vue.use(Vuex)

const persistedStateOptions = {
  reducer: value => value.auth.token
    ? { auth: value.auth }
    : {},
}

const store = new Vuex.Store({
  modules: {
    auth
  },
  plugins: [createPersistedState(persistedStateOptions)]
})

export default store