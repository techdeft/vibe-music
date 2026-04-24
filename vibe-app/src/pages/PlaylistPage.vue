<template>
  <div v-if="playlist" class="playlist-page">
    <!-- Header -->
    <div class="playlist-header" :style="headerBg">
      <div class="header-overlay"></div>
      <div class="header-content">
        <div class="playlist-cover-wrap">
          <img v-if="playlist.cover" :src="playlist.cover" :alt="playlist.name" class="playlist-cover" />
          <div v-else class="playlist-cover-empty">
            <span class="material-symbols-outlined">playlist_play</span>
          </div>
        </div>
        <div class="playlist-info">
          <p class="type-badge">{{ playlist.public ? 'Public' : 'Private' }} Playlist</p>
          <h1 class="playlist-name">{{ playlist.name }}</h1>
          <div class="playlist-meta">
            <span class="author">{{ playlist.author_name }}</span>
            <span v-if="playlist.tracks?.length">• {{ playlist.tracks.length }} songs</span>
            <span v-if="totalDuration">• {{ totalDuration }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="action-bar">
      <button class="btn-play" @click="playAll" :disabled="!playlist.tracks?.length">
        <span class="material-symbols-outlined filled">play_arrow</span>
      </button>
      <div class="more-actions" v-if="isOwner">
        <button @click="handleDelete" class="btn-delete" title="Delete Playlist">
          <span class="material-symbols-outlined">delete</span>
        </button>
      </div>
    </div>

    <!-- Track List -->
    <div class="tracks-section" v-if="playlist.tracks?.length">
      <div class="tracks-header-row">
        <span class="th th-num">#</span>
        <span class="th">Title</span>
        <span class="th mobile-hide">Album</span>
        <span class="th th-dur mobile-hide"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
        <span class="th"></span>
      </div>
      <div class="divider"></div>

      <div class="tracks-list">
        <div v-for="(track, i) in playlist.tracks" :key="track.id" class="track-row-wrap">
          <TrackRow
            :track="track"
            :number="i + 1"
            :queue="playlist.tracks"
            show-cover
          />
          <button v-if="isOwner" @click="handleRemoveTrack(track.id)" class="remove-track-btn" title="Remove from playlist">
            <span class="material-symbols-outlined">remove_circle_outline</span>
          </button>
        </div>
      </div>
    </div>

    <div v-else class="empty-tracks">
      <span class="material-symbols-outlined" style="font-size:64px;color:#333">music_off</span>
      <h2>It's a bit empty here</h2>
      <p>Find more music and add it to your playlist.</p>
      <RouterLink to="/" class="btn-discover">Go to Discovery</RouterLink>
    </div>
  </div>

  <div v-else-if="loading" class="page-loading">
    <div class="spinner"></div>
  </div>

  <div v-else class="empty-state">
    <span class="material-symbols-outlined" style="font-size:48px;color:#333">playlist_add_check</span>
    <p>Playlist not found</p>
    <RouterLink to="/" class="back-link">← Back to Home</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/services/api'
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'
import { usePlaylistStore } from '@/stores/playlists'
import TrackRow from '@/components/TrackRow.vue'

const route = useRoute()
const router = useRouter()
const player = usePlayerStore()
const auth = useAuthStore()
const playlistStore = usePlaylistStore()

const playlist = ref(null)
const loading = ref(true)

const isOwner = computed(() => 
  playlist.value && auth.user && playlist.value.author_id === auth.user.id
)

const headerBg = computed(() => {
  return { background: 'linear-gradient(180deg, #2a2a2a 0%, #0A0A0A 100%)' }
})

const totalDuration = computed(() => {
  if (!playlist.value?.tracks?.length) return ''
  let total = 0
  for (const t of playlist.value.tracks) {
    if (t.duration) {
      const [m, s] = t.duration.split(':').map(Number)
      total += (m || 0) * 60 + (s || 0)
    }
  }
  if (!total) return ''
  const h = Math.floor(total / 3600)
  const m = Math.floor((total % 3600) / 60)
  return h ? `${h} hr ${m} min` : `${m} min`
})

async function load() {
  loading.value = true
  playlist.value = null
  try {
    playlist.value = await api.getPlaylist(route.params.id)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function playAll() {
  const tracks = playlist.value?.tracks
  if (tracks?.length) player.playTrack(tracks[0], tracks)
}

async function handleDelete() {
  if (confirm('Are you sure you want to delete this playlist?')) {
    await playlistStore.deletePlaylist(playlist.value.id)
    router.push('/library')
  }
}

async function handleRemoveTrack(trackId) {
  const updated = await playlistStore.removeTrack(playlist.value.id, trackId)
  playlist.value = updated
}

onMounted(load)
watch(() => route.params.id, load)
</script>

<style scoped>
.playlist-page { min-height: 100%; }

.playlist-header {
  position: relative;
  padding-top: 60px;
}

.header-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, #0A0A0A 100%);
}

.header-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  gap: 24px;
  padding: 20px 32px 32px;
}

.playlist-cover-wrap {
  width: 232px;
  height: 232px;
  flex-shrink: 0;
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 16px 48px rgba(0,0,0,0.6);
}

.playlist-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.playlist-cover-empty {
  width: 100%;
  height: 100%;
  background: #282828;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
}

.playlist-cover-empty .material-symbols-outlined {
  font-size: 80px;
}

.playlist-info { flex: 1; }

.type-badge {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  color: #fff;
  margin-bottom: 8px;
}

.playlist-name {
  font-family: 'Spline Sans', sans-serif;
  font-size: 64px;
  font-weight: 900;
  color: #fff;
  line-height: 1.1;
  letter-spacing: -2px;
  margin-bottom: 12px;
}

.playlist-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #fff;
}

.author {
  font-weight: 700;
}

.action-bar {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 24px 32px;
}

.btn-play {
  width: 56px;
  height: 56px;
  background: #FF0000;
  border: none;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(255,0,0,0.3);
  transition: all 0.15s;
}

.btn-play:hover { transform: scale(1.08); }
.btn-play:disabled { background: #333; box-shadow: none; cursor: default; }
.btn-play .material-symbols-outlined { font-size: 30px; }

.btn-delete {
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 8px;
  display: flex;
  align-items: center;
  transition: color 0.15s;
}

.btn-delete:hover { color: #FF0000; }

.tracks-section { padding: 0 24px 48px; }

.tracks-header-row {
  display: grid;
  grid-template-columns: 48px 1fr 1fr 60px 110px;
  padding: 0 16px;
  margin-bottom: 6px;
}

.th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
.th-num { text-align: center; }
.th-dur { text-align: right; }

.divider { height: 1px; background: #1A1A1A; margin-bottom: 8px; }

.track-row-wrap {
  display: grid;
  grid-template-columns: 1fr 48px;
  align-items: center;
}

@media (max-width: 768px) {
  .track-row-wrap {
    grid-template-columns: 1fr 32px;
  }
}

.remove-track-btn {
  background: none;
  border: none;
  color: #444;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s;
}

.remove-track-btn:hover { color: #FF0000; }

.empty-tracks {
  text-align: center;
  padding: 60px 0;
}

.empty-tracks h2 {
  font-family: 'Spline Sans', sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #fff;
  margin: 16px 0 8px;
}

.empty-tracks p {
  color: #888;
  margin-bottom: 24px;
}

.btn-discover {
  display: inline-block;
  background: #fff;
  color: #000;
  text-decoration: none;
  font-weight: 700;
  padding: 12px 32px;
  border-radius: 30px;
  transition: transform 0.15s;
}

.btn-discover:hover { transform: scale(1.04); }

.page-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60vh;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #1A1A1A;
  border-top-color: #FF0000;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
