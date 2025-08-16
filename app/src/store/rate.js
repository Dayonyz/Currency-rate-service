import { apiClient } from '@/boot/api'
import endpoints from "@/routes/endpoints";
import { makeDefaultGetters } from '@/utilities/store'
import { CURRENCY } from "@/enums/currencies";

const defaultState = () => ({
  currentRate: null,
  rates: [],
  page: 1,
  pageSize: 20,
  pagesCount: 1,
  itemsCount: null
})

const state = defaultState()

const actions = {
  async fetchCurrentRate({ commit }) {
    try {
      const rate = await apiClient.get(endpoints.rate(CURRENCY.EUR, CURRENCY.USD))

      commit('SET_CURRENT_RATE', rate)

      return true

    } catch (error) {
      console.error('API error (fetch current rate): ', error)
      return false
    }
  },

  async fetchOnLoad({ commit, state }) {
    try {
      const [rate, list] = await Promise.all([
        apiClient.get(endpoints.rate(CURRENCY.EUR, CURRENCY.USD)),
        apiClient.get(endpoints.rates(CURRENCY.EUR, CURRENCY.USD, state.pageSize, state.page))
      ])

      const { rates, pagesCount, itemsCount} = list.data

      commit('SET_CURRENT_RATE', rate.data)
      commit('SET_RATES', rates)
      commit('SET_PAGES_COUNT', pagesCount)
      commit('SET_ITEMS_COUNT', itemsCount)

      return true
    } catch (error) {
      console.error('API error (fetch on Dashboard load): ', error)
      return false
    }
  },
  async changePage({ commit, dispatch }, page) {
    commit('SET_PAGE', page)
    await dispatch('fetchOnLoad')
  },

  async changePageSize({ commit, dispatch }, pageSize) {
    commit('SET_PAGE', 1)
    commit('SET_PAGE_SIZE', pageSize)
    await dispatch('fetchOnLoad')
  },
}

const mutations = {
  SET_CURRENT_RATE (state, rate) {
    state.currentRate = rate
  },

  SET_RATES (state, rates) {
    state.rates = rates
  },

  SET_PAGE (state, page) {
    state.page = page > state.pagesCount ? state.pagesCount : page
  },

  SET_PAGE_SIZE (state, size) {
    state.pageSize = size
  },

  SET_PAGES_COUNT (state, count) {
    state.pagesCount = count
  },

  SET_ITEMS_COUNT (state, count) {
    state.itemsCount = count
  }
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
