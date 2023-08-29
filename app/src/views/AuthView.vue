<template>
  <div class="auth">
    <h1>Please, auth to continue:</h1>
    <div class="field"><label for="email">Email</label><input v-model="email" id="email" type="text" name="email"></div>
    <div class="field"><label for="password">Password</label><input v-model="password" id="password" type="text" name="password"></div>
    <div class="field"><input type="button" @click="login({ email: email, password: password})" value="Submit"></div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
export default {
  name: 'AuthView',
  data () {
    return {
      email: '',
      password: ''
    }
  },
  methods: {
    ...mapActions({
      login: 'auth/login'
    })
  }
}
</script>

<style scoped>
.auth {}
input {
  border-style: solid;
  border-width: 1px;
}
.field{
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
