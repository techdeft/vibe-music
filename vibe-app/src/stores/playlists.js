import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/services/api'

export const usePlaylistStore = defineStore('playlists', () => {
  const playlists = ref([])
  const loading = ref(false)

  async function fetchPlaylists() {
    loading.value = true
    try {
      playlists.value = await api.getPlaylists()
    } catch {
      playlists.value = []
    } finally {
      loading.value = false
    }
  }

  async function createPlaylist(name, description = '', isPublic = false) {
    const playlist = await api.createPlaylist(name, description, isPublic)
    playlists.value.unshift(playlist)
    return playlist
  }

  async function deletePlaylist(id) {
    await api.deletePlaylist(id)
    playlists.value = playlists.value.filter(p => p.id !== id)
  }

  async function addTrack(playlistId, trackId) {
    const updated = await api.addTrackToPlaylist(playlistId, trackId)
    const idx = playlists.value.findIndex(p => p.id === playlistId)
    if (idx !== -1) playlists.value[idx] = updated
    return updated
  }

  async function removeTrack(playlistId, trackId) {
    const updated = await api.removeTrackFromPlaylist(playlistId, trackId)
    const idx = playlists.value.findIndex(p => p.id === playlistId)
    if (idx !== -1) playlists.value[idx] = updated
    return updated
  }

  return { playlists, loading, fetchPlaylists, createPlaylist, deletePlaylist, addTrack, removeTrack }
})
