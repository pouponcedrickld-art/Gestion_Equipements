import api from './axiosConfig.js'

export default {
    login: (credentials) => api.post('/login', credentials),
    verify2FA: (data) => api.post('/2fa/verify', data),
    logout: () => api.post('/logout'),
    me: () => api.get('/me'),
    refresh: () => api.post('/refresh'),
}
