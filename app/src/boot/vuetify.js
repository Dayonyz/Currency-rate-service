import Vue from 'vue'
import Vuetify from 'vuetify/lib'

export default new Vuetify({
  theme: {
    themes: {
      light: {
        primary: '#004E77',
        secondary: '#1AA8C0',
        accent: '#b51c1c',
        error: '#b51c1c',
        info: '#1AA8C0',
        success: '#46864a',
        warning: '#e7b10f',
      },
    },
  },

  icons: {
    iconfont: [ 'mdiSvg'] // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4' || 'faSvg'
  }
})

Vue.use(Vuetify)
