import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import Tooltip from 'primevue/tooltip'

import './assets/theme.css'
import './assets/main.css'
import 'primeicons/primeicons.css'

import App from './App.vue'
import router from './router/index.js'
import { useAuthStore } from './stores/authStore.js'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(PrimeVue, {
  theme: 'none'
})
app.use(ToastService)
app.use(ConfirmationService)
app.directive('tooltip', Tooltip)

const authStore = useAuthStore()
if (authStore.token) {
  authStore.fetchUser()
}

app.mount('#app')