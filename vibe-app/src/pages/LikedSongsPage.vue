<template>
  <div class="liked-songs-page">
    <!-- Header -->
    <div class="header">
      <div class="header-overlay"></div>
      <div class="header-content">
        <div class="icon-wrap">
          <span class="material-symbols-outlined">favorite</span>
        </div>
        <div class="info">
          <p class="type-badge">Playlist</p>
          <h1 class="page-title">Liked Songs</h1>
          <div class="meta">
            <span class="author">{{ auth.user?.display_name }}</span>
            <span v-if="tracks.length">• {{ tracks.length }} songs</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="action-bar">
      <button class="btn-play" @click="playAll" :disabled="!tracks.length">
        <span class="material-symbols-outlined filled">play_arrow</span>
      </button>
    </div>

    <!-- Track List -->
    <div class="tracks-section" v-if="tracks.length">
      <div class="tracks-header-row">
        <span class="th th-num"></span>
        <span class="th">Title</span>
        <span class="th mobile-hide">Album</span>
        <span class="th th-streams mobile-hide">Plays</span>
        <span class="th th-dur mobile-hide"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
        <span class="th"></span>
      </div>
      <div class="divider"></div>

      <div class="tracks-list">
        <TrackRow
          v-for="(track, i) in tracks"
          :key="track.id"
          :track="track"
          :number="i + 1"
          :queue="tracks"
          show-cover
        />
      </div>
    </div>

    <div v-else-if="!loading" class="empty-state">
      <span class="material-symbols-outlined" style="font-size:64px;color:#333">favorite</span>
      <h2>Songs you like will appear here</h2>
      <p>Save songs by tapping the heart icon.</p>
      <RouterLink to="/" class="btn-discover">Find songs to like</RouterLink>
    </div>

    <div v-if="loading" class="page-loading">
      <div class="spinner"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { usePlayerStore } from '@/stores/player'
import TrackRow from '@/components/TrackRow.vue'

const auth = useAuthStore()
const player = usePlayerStore()

const tracks = ref([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    tracks.value = await api.getLikedSongs()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function playAll() {
  if (tracks.value.length) {
    player.playTrack(tracks.value[0], tracks.value)
  }
}

onMounted(load)
</script>

<style scoped>
.liked-songs-page { min-height: 100%; }

.header {
  position: relative;
  padding-top: 60px;
  background: linear-gradient(180deg, #5038a0 0%, #0A0A0A 100%);
}

.header-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, #0A0A0A 100%);
}

.header-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  gap: 24px;
  padding: 20px 32px 32px;
}

.icon-wrap {
  width: 232px;
  height: 232px;
  background: linear-gradient(135deg, #450af5 0%, #c4efd9 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  box-shadow: 0 16px 48px rgba(0,0,0,0.6);
  color: #fff;
}

.icon-wrap .material-symbols-outlined {
  font-size: 96px;
  font-variation-settings: 'FILL' 1;
}

.info { flex: 1; }

.type-badge {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  color: #fff;
  margin-bottom: 8px;
}

.page-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 72px;
  font-weight: 900;
  color: #fff;
  line-height: 1.1;
  letter-spacing: -3px;
  margin-bottom: 12px;
}

.meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #fff;
}

.author { font-weight: 700; }

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

.tracks-section { padding: 0 24px 48px; }

.tracks-header-row {
  display: grid;
  grid-template-columns: 48px 1fr 1fr 100px 60px 110px;
  padding: 0 16px;
  margin-bottom: 6px;
}

.th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
.th-num { text-align: center; }
.th-streams { text-align: right; padding-right: 24px; }
.th-dur { text-align: right; }

.divider { height: 1px; background: #1A1A1A; margin-bottom: 8px; }

.empty-state {
  text-align: center;
  padding: 60px 0;
}

.empty-state h2 {
  font-family: 'Spline Sans', sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #fff;
  margin: 16px 0 8px;
}

.empty-state p {
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
  height: 40vh;
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
