<template>
  <div id="app">
    <nav>
      <router-link to="/">Home</router-link><span>&nbsp;</span>
      <router-link v-if="!isAuth" to="/auth">Auth</router-link><span v-if="!isAuth">&nbsp;</span>
      <router-link v-if="isAuth" to="/dashboard">Dashboard</router-link><span>&nbsp;</span>
      <a @click.prevent="exit" href="#" v-if="isAuth">Logout</a>
    </nav>
    <router-view/>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
export default {
  name: 'App',
  computed: {
    ...mapGetters('auth', [
      'isAuth',
    ])
  },
  methods: {
    ...mapActions('auth', [
      'logout'
    ]),

      async exit() {
          await this.logout()
          if (this.$route.name !== 'home') {
              this.$router.push({ name: 'home' })
          }
      }
  }
}
</script>

<style lang="scss">
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

nav {
  padding: 30px;

  a {
    font-weight: bold;
    color: #2c3e50;

    &.router-link-exact-active {
      color: #42b983;
    }
  }
}
</style>
