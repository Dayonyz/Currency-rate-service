export default {
  login: `/login`,
  logout: `/logout`,
  rate: (currency, base) => `/currency/rate/${currency}/${base}`,
  rates: (currency, base, perPage = null, page= 1) => `/currency/rates/${currency}/${base}${perPage ? '/' + perPage + '/' + page : ''}`,
}
