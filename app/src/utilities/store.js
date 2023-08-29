import Vue from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import { pascalCase } from '@/utilities/filters'

export const makeDefaultGetters = function(properties) {
  const defaultGetters = {}

  properties.forEach(property => {
    defaultGetters[property] = state => state[property]
  })

  return defaultGetters
}

export const makeDefaultMutations = function(properties, defaultState = {}) {
  const defaultMutations = {}

  properties.forEach(property => {
    const setPropertyMutationName = 'set' + pascalCase(property)
    defaultMutations[setPropertyMutationName] = (state, value) => Vue.set(state, property, value)

    const resetPropertyMutationName = 're' + setPropertyMutationName
    defaultMutations[resetPropertyMutationName] = state => Vue.set(state, property, cloneDeep(defaultState[property]))
  })

  return defaultMutations
}

export const setState = (state, defaultState) => {
  const defaultKeys = Object.keys(defaultState)

  // unset non-default values
  Object.keys(state)
    .filter(key => !defaultKeys.includes(key))
    .forEach(key => {
      Vue.delete(state, key)
    })

  // set default values
  Object.keys(defaultState).forEach(key => {
    Vue.set(state, key, defaultState[key])
  })
}
