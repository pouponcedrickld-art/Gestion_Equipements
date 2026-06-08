import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'
<<<<<<< HEAD
<<<<<<< HEAD
import 'primeicons/primeicons.css'
=======
import ToastService from 'primevue/toastservice'
>>>>>>> 98ee844810a275af78d066b328a028ab99ac6202
=======
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import Tooltip from 'primevue/tooltip'

// Import styles
import './assets/main.css'
import 'primeicons/primeicons.css'
>>>>>>> ffa46c85f50ce66431a07232052669c1f18451a6

import App from './App.vue'
import router from './router/index.js'
import { useAuthStore } from './stores/authStore.js'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(PrimeVue, {
  theme: {
    preset: Aura,
    options: {
      darkModeSelector: '.p-dark'
    }
  }
})
app.use(ToastService)
<<<<<<< HEAD
=======
app.use(ConfirmationService)
app.directive('tooltip', Tooltip)
>>>>>>> ffa46c85f50ce66431a07232052669c1f18451a6

// Fetch user on app startup if token exists (AFTER pinia is installed!
const authStore = useAuthStore()
if (authStore.token) {
  authStore.fetchUser()
}

app.mount('#app')
