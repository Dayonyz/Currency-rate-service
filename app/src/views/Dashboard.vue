<template>
  <div>
    <h2>Dashboard</h2>
    <h3>
      <span>{{ currentRate.currency.iso_code }}/{{ currentRate.base_currency.iso_code }}&nbsp;&nbsp;&nbsp;</span>
      <span style="color: green">{{ currentRate.rate }}&nbsp;&nbsp;&nbsp;</span>
      <span style="color: #2a6496">{{ currentRate.actual_at }}&nbsp;&nbsp;&nbsp;</span>
    </h3>
    <vuetable
      v-if="currentRate"
      :fields="['currency.iso_code', 'base_currency.iso_code', 'rate', 'actual_at']"
      :api-mode="false"
      :data="rates"
    />
    <div style="padding-top:10px">
      <div class="field">
        <label for="page"> Page </label>
        <select name="page" @change.prevent="selectPage($event)">
          <option v-for="page in pagesCount" :key="page" :value="page">{{ page }}</option>
        </select>
        <label for="per-page"> Per Page </label>
        <select name="per-page" @change.prevent="selectPageSize($event)">
          <option
            v-for="value in perPageItems"
            :key="value"
            :value="value"
            :selected="value === pageSize"
          >{{ value }}</option>
        </select>
        <label for="total"> Total </label>
        <input disabled name="total" :value="itemsCount"/>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import Vuetable from 'vuetable-2'


export default {
  name: "DashBoard",
  components: {
    Vuetable,
  },
  data () {
    return {
      perPageItems: [
        5,
        10,
        20,
        30
      ]
    }
  },
  mounted() {
    this.fetchOnLoad()
  },
  computed: {
    ...mapGetters('rate', [
      'currentRate',
      'rates',
      'page',
      'pageSize',
      'pagesCount',
      'itemsCount'
    ])
  },
  methods: {
    ...mapActions('rate', [
      'fetchOnLoad',
      'changePage',
      'changePageSize'
    ]),
    selectPage(event) {
      this.changePage(event.target.value)
    },
    selectPageSize(event) {
      this.changePageSize(event.target.value)
    }
  }
}
</script>

<style>

div.vuetable-body-wrapper {
  width:98%;
  margin:1%;
}

table.vuetable {
  text-align:center;
  margin-left:auto;
  margin-right:auto;
  width:800px;
}
tr,td {
  text-align:left;
}

</style>