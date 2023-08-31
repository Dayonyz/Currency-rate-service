<template>
  <div class="auth">
    <h1>Please, auth to continue:</h1>
    <div class="field"><label for="email">Email</label><input v-model="email" id="email" type="text" name="email"><br></div>
    <div class="field"><label for="password">Password</label><input v-model="password" id="password" type="password" name="password"></div>
    <div class="field"><input type="button" @click="auth({ email: email, password: password})" value=" Login "></div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
export default {
  name: 'AuthView',
  data () {
    return {
      email: '',
      password: ''
    }
  },
  computed: {
    ...mapGetters('auth', [
      'token'
    ])
  },

  methods: {
    ...mapActions('auth', [
      'login'
    ]),

    auth(credentials) {
      if (this.login(credentials)) {
        this.$router.push({ name: 'dashboard', params: { token: this.token }});
      }
    }
  }
}
</script>

<style scoped>
.auth {}
input {
  border-style: solid;
  border-width: 1px;
}
.field {
  padding-bottom: 5px;
  width: 300px;
  margin: 0 auto;
  clear:both;
  text-align:right;
  line-height:25px;
}
label {
  float: left;
  padding-right:10px;
}

</style>
