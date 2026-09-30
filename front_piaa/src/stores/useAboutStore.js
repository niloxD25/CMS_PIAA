import { defineStore } from 'pinia'

export const useAboutStore = defineStore('about', {
  state: () => ({
    data: null
  }),
  persist: true,

  actions: {
    setData(payload) {
      this.data = payload
    },

    clear() {
      this.data = null
    }
  }
})
