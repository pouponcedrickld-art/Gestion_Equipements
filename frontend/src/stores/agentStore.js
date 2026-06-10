import { defineStore } from 'pinia'
import { ref } from 'vue'
import agentApi from '@/api/agentApi.js'

export const useAgentStore = defineStore('agent', () => {
  const agents = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchAgents = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await agentApi.index()
      agents.value = response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const createAgent = async (data) => {
    loading.value = true
    error.value = null
    try {
      const response = await agentApi.store(data)
      await fetchAgents()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateAgent = async (id, data) => {
    loading.value = true
    error.value = null
    try {
      const response = await agentApi.update(id, data)
      await fetchAgents()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteAgent = async (id) => {
    loading.value = true
    error.value = null
    try {
      await agentApi.destroy(id)
      await fetchAgents()
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const getAgentById = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await agentApi.show(id)
      return response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    agents,
    loading,
    error,
    fetchAgents,
    createAgent,
    updateAgent,
    deleteAgent,
    getAgentById
  }
})
