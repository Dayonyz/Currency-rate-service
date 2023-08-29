import api from '@/boot/api'
import endpoints from "@/routes/endpoints";
import { makeDefaultGetters } from '@/utilities/store'

const defaultState = () => ({
  token: '',
})

const state = defaultState()

const actions = {
  async login({ commit}, payload = {}) {
    try {
      const { token } = api.apiClient.post(endpoints.login, payload)

      if (!token) {
        return
      }

      commit('SET_TOKEN', token)

      return true
    } catch (error) {
      console.error('API error (login): ', error)
      return false
    }
  },

  async logout({ commit }) {
    try {
      await api.post(endpoints.logout)

      commit('UNSET_TOKEN')

      return true
    } catch (error) {
      console.error('API error (logout): ', error)
      return false
    }

  },
}

const mutations = {
  SET_TOKEN (state, token) {
    state.token = token
  },

  UNSET_TOKEN (state) {
    state.token = ''
  },
}

const properties = Object.keys(defaultState())

const defaultGetters = makeDefaultGetters(properties)

const getters = {
  ...defaultGetters,
}

export default {
  state,
  mutations,
  actions,
  getters,
  namespaced: true,
}
