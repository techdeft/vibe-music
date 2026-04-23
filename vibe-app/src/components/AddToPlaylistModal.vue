<template>
  <Teleport to="body">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Add to Playlist</h3>
          <button @click="close" class="close-btn">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div class="playlists-scroll">
          <div v-if="playlistStore.loading" class="loading">Loading playlists...</div>
          <div v-else-if="playlistStore.playlists.length === 0" class="empty">
            <p>You don't have any playlists yet.</p>
          </div>
          <div v-else class="playlists-list">
            <button 
              v-for="pl in playlistStore.playlists" 
              :key="pl.id" 
              @click="handleAddToPlaylist(pl.id)"
              class="playlist-option"
            >
              <div class="pl-icon">
                <span class="material-symbols-outlined">playlist_play</span>
              </div>
              <div class="pl-info">
                <p class="pl-name">{{ pl.name }}</p>
                <p class="pl-count">{{ pl.track_count }} songs</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { usePlaylistStore } from '@/stores/playlists'

const props = defineProps({
  isOpen: Boolean,
  trackId: Number
})

const emit = defineEmits(['close'])
const playlistStore = usePlaylistStore()

async function handleAddToPlaylist(playlistId) {
  await playlistStore.addTrack(playlistId, props.trackId)
  emit('close')
  // Optional: show a toast notification
}

function close() {
  emit('close')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(8px);
}

.modal-content {
  background: #181818;
  border: 1px solid #282828;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5);
}

.modal-header {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #282828;
}

.modal-header h3 {
  font-family: 'Spline Sans', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #fff;
}

.close-btn {
  background: none;
  border: none;
  color: #888;
  cursor: pointer;
  padding: 4px;
}

.close-btn:hover { color: #fff; }

.playlists-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
}

.playlists-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.playlist-option {
  width: 100%;
  background: none;
  border: none;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  text-align: left;
  transition: background 0.15s;
}

.playlist-option:hover { background: rgba(255, 255, 255, 0.05); }

.pl-icon {
  width: 48px;
  height: 48px;
  background: #282828;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
}

.pl-info { flex: 1; }

.pl-name {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  margin-bottom: 2px;
}

.pl-count {
  font-size: 12px;
  color: #888;
}

.loading, .empty {
  padding: 40px;
  text-align: center;
  color: #666;
  font-size: 14px;
}
</style>
