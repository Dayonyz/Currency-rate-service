import lodashCamelCase from 'lodash/camelCase'
import lodashLowerCase from 'lodash/lowerCase'
import lodashSnakeCase from 'lodash/snakeCase'
import lodashToUpper from 'lodash/toUpper'
import lodashUpperCase from 'lodash/upperCase'
import lodashUpperFirst from 'lodash/upperFirst'

/**
 * Convert a string to camel case.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function camelCase(s) {
  return lodashCamelCase(s)
}

/**
 * Convert a value to checkbox icon
 *
 * @param {Boolean} v value to be converted
 *
 * @returns {String} checkbox icon name
 */
export function checkBoxIcon(v) {
  return v ? 'check_box' : 'check_box_outline_blank'
}

/**
 * Add Elipses to a string
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function ellipsis(s) {
  return s + '...'
}

/**
 * Return the first letter of each word in a string, as separated by a space.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function initials(s) {
  return s
    .split(' ')
    .map(w => lodashToUpper(w[0]))
    .join(' ')
    .trim()
}

/**
 * Convert a string to lower case.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function lowerCase(s) {
  return lodashLowerCase(s)
}

/**
 * Remove all spaces from a string
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function noSpaces(s) {
  return s
    .split(' ')
    .join('')
    .trim()
}

/**
 * Surround a string with parentheses
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function parentheses(s) {
  return '(' + s + ')'
}

/**
 * Convert a string to pascal case.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function pascalCase(s) {
  return lodashUpperFirst(lodashCamelCase(s))
}

/**
 * Convert a string to a sentence.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function sentence(s) {
  return lodashUpperFirst(lodashLowerCase(s))
}

/**
 * Convert a string to snake case.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function snakeCase(s) {
  return lodashSnakeCase(s)
}

/**
 * Convert a string to a title.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function title(s) {
  return s
    .trim()
    .split(' ')
    .map(v => lodashUpperFirst(lodashLowerCase(v)))
    .join(' ')
}

/**
 * Convert a string to upperCase.
 *
 * @param {String} s string to be converted
 *
 * @returns {String} converted string
 */
export function upperCase(s) {
  return lodashUpperCase(s)
}

export function shortenHard(str, length, ellipsis = '...') {
  return str.length < length ? str : str.substr(0, length) + ellipsis;
}

export function shorten(str, maxLen, separator = ' ', ellipsis = '') {
  if (str.length <= maxLen) {
    return str
  }

  return str.substr(0, str.lastIndexOf(separator, maxLen)) + ellipsis;
}

export default {
  camelCase,
  checkBoxIcon,
  ellipsis,
  initials,
  lowerCase,
  noSpaces,
  parentheses,
  pascalCase,
  sentence,
  snakeCase,
  title,
  upperCase,
  shorten,
}
