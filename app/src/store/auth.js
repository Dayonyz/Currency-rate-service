import { apiClient } from '@/boot/api'

const defaultState = () => ({
  token: localStorage.getItem('auth_token') || null
})

const state = defaultState()

const actions = {
  async login({ commit }, payload) {
    const { token } = await apiClient.post('/login', payload)
    commit('SET_TOKEN', token)
    return true
  },

  async logout({ commit }) {
    try {
      await apiClient.post('/logout')
    } catch (e) {
      console.warn('Logout error', e)
    } finally {
      commit('UNSET_TOKEN')
      commit('rate/RESET_STATE', null, { root: true })
    }
  }
}

const mutations = {
  SET_TOKEN (state, token) {
    state.token = token
    localStorage.setItem('auth_token', token)
  },
  UNSET_TOKEN (state) {
    state.token = null
    localStorage.removeItem('auth_token')
  }
}

const getters = {
  isAuth: state => !!state.token
}

export default {
  namespaced: true,
  state,
  actions,
  mutations,
  getters
}
