import camelCase from 'lodash/camelCase'
import isInteger from 'lodash/isInteger'
import isNumber from 'lodash/isNumber'
import snakeCase from 'lodash/snakeCase'

const changeCaseFunctions = { camelCase, snakeCase }

export const ACCEPTED_CASES = Object.freeze(['camel', 'snake'])

/**
 * Maps an object's values to an array.
 *
 * @param {Object} object object to be arrayified
 *
 * @returns {Array}
 */
export const arrayify = object => Object.keys(object).map(id => object[id])

/**
 * Camelise object data properties (recursively).
 *
 * @param {Mixed} obj object to be camelised
 * @param {Boolean} recursive decides if camelisation should be applied recursively
 *
 * @returns {Object}
 */
export function camelise(obj, recursive = true) {
  return casify(obj, 'camel', recursive)
}

/**
 * Change the case pattern of object keys (recursively).
 *
 * @param {Object} obj object to be operated on
 * @param {String} targetCase the desired target case
 * @param {Boolean} recursive decides if case change should be applied recursively
 *
 * @returns {Object}
 */
export function casify(obj, targetCase = 'snake', recursive = true) {
  if (!obj || !(Array.isArray(obj) || typeof obj === 'object')) {
    return obj
  }

  if (!ACCEPTED_CASES.includes(targetCase)) {
    throw 'casify was provided with an invalid case - accepted cases include: ' +
    String(ACCEPTED_CASES)
  }

  const changeCaseFunction = targetCase + 'Case'
  const isArray = Array.isArray(obj)
  const keys = isArray ? obj : Object.keys(obj)
  let changed = isArray ? [] : {}

  for (let i in keys) {
    const key = isArray ? i : changeCaseFunctions[changeCaseFunction](keys[i])
    let val = isArray ? obj[i] : obj[keys[i]]

    if (recursive && (Array.isArray(val) || typeof val === 'object')) {
      changed[key] = casify(val, targetCase)
    } else {
      changed[key] = val
    }
  }
  return changed
}

/**
 * Handle a file download in the browser from response data.
 *
 * @param {Object} payload payload
 * @param {String} payload.data data for download
 * @param {String} payload.filename filename (default: 'file.txt')
 *
 * @returns {Void}
 */
export function downloadDirectToBrowser(payload) {
  if (!payload) {
    throw 'downloadDirectToBrowser requires payload'
  }

  const { data, filename = 'file.txt' } = payload
  const link = document.createElement('a')
  link.href = window.URL.createObjectURL(new Blob([data]))
  link.download = filename
  link.click()
}

/**
 * Return an array filled with all the integers between 2 numbers.
 *
 * @param {Number} x1 First number
 * @param {Number} x2 Second number
 * @param {Boolean} inclusive The range should be considered inclusive (default: true)
 * @param {Boolean} ascending The returned array should be in ascending order (default: true)
 *
 * @returns {Array} Array containing the integers between x1 and x2
 */

export function generateIntermediateIntegers(x1, x2, inclusive = true, ascending = true) {
  if (!isNumber(x1) || !isNumber(x2)) {
    throw 'generateIntermediateIntegers requires a range'
  }

  if (x1 === x2) {
    return inclusive ? [x1, x2] : []
  }

  const min = x1 < x2 ? x1 : x2
  const max = x1 > x2 ? x1 : x2
  const intermediates = []
  const dist = max - min

  for (let i = 1; i < dist; i++) {
    intermediates.push(min + i)
  }

  const res = []

  if (inclusive && isInteger(min)) {
    res.push(min)
  }

  intermediates.forEach(i => res.push(i))

  if (inclusive && isInteger(max)) {
    res.push(max)
  }

  return ascending ? res : res.reverse()
}

/**
 * Maps an array of objects to an object with key referencing a shared object property.
 *
 * Important: Key must be a unique field or values will be overwritten
 *
 * @param {Array} arr array to be objectified
 * @param {String} key object property to be used as key (default: 'id')
 * @param {Boolean} strict if true, throw error when key is missing
 *
 * @returns {Object}
 */
export function objectify(arr, key = 'id', strict = false) {
  return arr.reduce((a, v) => {
    if (Object.prototype.hasOwnProperty.call(v, key)) {
      a[v[key]] = v
    }
    else if (strict) {
      throw 'Cannot objectify with missing keys in strict mode'
    }

    return a
  }, {})
}

/**
 * Returns a random integer within a given range
 *
 * @param {Number} max maximum integer value to return (required)
 * @param {Number} min minimum integer value to return (default: 0)
 *
 * @returns {Number} A random integer within the given range
 */
export const randomInt = (max, min = 0) => Math.floor(Math.random() * (max - min)) + min

/**
 * Returns an object with properties declared in the array, assigned the value passed
 *
 * @param {Array} arr array of keys
 * @param {Mixed} val value to assign to each key
 *
 * @returns {Object} An object based on the array and values passed.
 */
export const reduceArray = (arr, val) =>
  arr.reduce((a, v) => {
    a[v] = val
    return a
  }, {})

/**
 * Remove prefix from a string.
 *
 * @param {String} str string to be altered
 * @param {String} pre prefix to be removed from string
 *
 * @returns {String} Altered string
 */
export function removePrefix(str, pre) {
  return str
    .split(pre)
    .slice(1)
    .join('')
}

/**
 * Snakify object data properties (recursively).
 *
 * @param {Object} obj object to be snakified
 * @param {Boolean} recursive decides if snakification should be applied recursively
 *
 * @returns {Object}
 */
export function snakify(obj, recursive = true) {
  return casify(obj, 'snake', recursive)
}
