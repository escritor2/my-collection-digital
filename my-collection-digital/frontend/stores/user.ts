import { defineStore } from 'pinia'
import type { User } from '~/types/global'

export const useUserStore = defineStore('user', {
  state: (): {
    user: User | null
    initialized: boolean
    token: string | null
  } => ({
    user: null,
    initialized: false,
    token: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.user && !!state.token,
    isAdmin: (state) => state.user?.level === 'admin',
    userLevel: (state) => state.user?.level || 1,
    userXp: (state) => state.user?.xp || 0,
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || ''
  },

  actions: {
    setUser(user: User) {
      this.user = user
      this.initialized = true
    },

    setToken(token: string) {
      this.token = token
      if (typeof window !== 'undefined') {
        localStorage.setItem('auth_token', token)
      }
    },

    clear() {
      this.user = null
      this.token = null
      this.initialized = false
      if (typeof window !== 'undefined') {
        localStorage.removeItem('auth_token')
        document.cookie = 'XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'
      }
    },

    async fetchUser() {
      try {
        const { data } = await useFetch('/api/user', {
          headers: useRequestHeaders(['cookie'])
        })
        if (data.value) {
          this.setUser(data.value as User)
        }
      } catch (e) {
        this.clear()
      }
    }
  }
})
