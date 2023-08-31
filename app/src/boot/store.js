import config from '@/store/index'
import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from 'vuex-persistedstate'

Vue.use(Vuex)

const persistedStateOptions = {
  reducer: value => value.auth.token
    ? { auth: value.auth }
    : {},
  storage: window.localStorage
}

const store = new Vuex.Store({ strict:true, ...config, plugins: [createPersistedState(persistedStateOptions)] })

export default store
