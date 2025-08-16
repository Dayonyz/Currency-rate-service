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
      :fields="fields"
      :api-mode="false"
      :data="rates"
    />
    <div style="padding-top:10px">
      <div class="field">
        <label for="page"> Page </label>
        <select
          name="page"
          @change.prevent="selectPage($event)"
          :value="page"
        >
          <option
            v-for="pageN in pagesCount"
            :key="pageN"
            :value="pageN"
          >{{ pageN }}</option>
        </select>
        <label for="per-page"> Per Page </label>
        <select
          name="per-page"
          @change.prevent="selectPageSize($event)"
          :value="pageSize"
        >
          <option
            v-for="perPageValue in perPageItems"
            :key="perPageValue"
            :value="perPageValue"
          >{{ perPageValue }}</option>
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
          perPageItems: [20, 30, 50, 100],
          fields: [
              {
                  name: 'currency.iso_code',
                  title: 'Currency'
              },
              {
                  name: 'base_currency.iso_code',
                  title: 'Base currency'
              },
              {
                  name: 'rate',
                  title: 'Rate',
                  titleClass: 'text-center',
                  dataClass: 'text-right'
              },
              {
                  name: 'actual_at',
                  title: 'Actual at'
              }
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