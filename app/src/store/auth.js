import { apiClient } from '@/boot/api'
import endpoints from "@/routes/endpoints";
import { makeDefaultGetters } from '@/utilities/store'

const defaultState = () => ({
  token: null,
})

const state = defaultState()

const actions = {
  async login({ commit }, payload) {
    try {
      const { token } = await apiClient.post(endpoints.login, payload)

      commit('SET_TOKEN', token)

      return true
    } catch (error) {
      console.error('API error (login): ', error)
      return false
    }
  },

  async logout({ commit }) {
    try {
      const { success } = await apiClient.post(endpoints.logout)

      commit('UNSET_TOKEN')

      return success
    } catch (error) {
      console.error('API error (logout): ', error)
      return false
    }

  },
}

const mutations = {
  SET_TOKEN (state, token) {
    state.token = token
    localStorage.setItem('auth_token', token)
  },

  UNSET_TOKEN (state) {
    state.token = null
    localStorage.removeItem('auth_token')
  },
}

const properties = Object.keys(defaultState())

const defaultGetters = makeDefaultGetters(properties)

const getters = {
  ...defaultGetters,
  isAuth: (state) => { return !!state.token }
}

export default {
  state,
  mutations,
  actions,
  getters,
  namespaced: true,
}
