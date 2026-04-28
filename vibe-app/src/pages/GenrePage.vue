<template>
  <div class="genre-page">
    <div class="genre-header" :style="{ background: genreColor(route.params.slug) }">
      <div class="header-overlay"></div>
      <div class="header-content">
        <p class="type-label">Genre</p>
        <h1 class="genre-name">{{ data?.genre?.name || route.params.slug }}</h1>
        <p class="track-count" v-if="data?.tracks?.length">{{ data.tracks.length }} tracks</p>
      </div>
    </div>

    <div class="content">
      <div class="action-bar" v-if="data?.tracks?.length">
        <button class="btn-play" @click="playAll">
          <span class="material-symbols-outlined filled">play_arrow</span>
        </button>
        <button class="btn-shuffle" @click="playShuffle">
          <span class="material-symbols-outlined">shuffle</span>
        </button>
      </div>

      <div v-if="data?.tracks?.length">
        <div class="tracks-header-row">
          <span class="th th-num"></span>
          <span class="th">Title</span>
          <span class="th mobile-hide">Album</span>
          <span class="th th-dur mobile-hide"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
          <span class="th"></span>
        </div>
        <div class="divider"></div>
        <TrackRow
          v-for="(t, i) in data.tracks"
          :key="t.id"
          :track="t"
          :number="i + 1"
          :queue="data.tracks"
          show-cover
        />
      </div>

      <div v-else-if="!loading" class="empty-state">
        <span class="material-symbols-outlined" style="font-size:40px;color:#333">music_off</span>
        <p>No tracks in this genre yet.</p>
      </div>

      <div v-if="loading" class="page-loading"><div class="spinner"></div></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/services/api'
import { usePlayerStore } from '@/stores/player'
import TrackRow from '@/components/TrackRow.vue'

const route = useRoute()
const player = usePlayerStore()
const data = ref(null)
const loading = ref(true)

async function load() {
  loading.value = true
  data.value = null
  try {
    data.value = await api.genreTracks(route.params.slug)
  } catch (e) {}
  finally { loading.value = false }
}

function playAll() {
  if (data.value?.tracks?.length) player.playTrack(data.value.tracks[0], data.value.tracks)
}

function playShuffle() {
  const tracks = data.value?.tracks
  if (!tracks?.length) return
  const idx = Math.floor(Math.random() * tracks.length)
  player.playTrack(tracks[idx], tracks)
}

const GENRE_COLORS = [
  'linear-gradient(135deg,#6B0000,#1a0000)',
  'linear-gradient(135deg,#003d5b,#001a26)',
  'linear-gradient(135deg,#1a3a00,#080f00)',
  'linear-gradient(135deg,#3d0045,#1a0020)',
  'linear-gradient(135deg,#3d2800,#1a1000)',
  'linear-gradient(135deg,#003d2a,#001a12)',
]

function genreColor(slug = '') {
  let hash = 0
  for (const c of slug) hash = (hash << 5) - hash + c.charCodeAt(0)
  return GENRE_COLORS[Math.abs(hash) % GENRE_COLORS.length]
}

onMounted(load)
watch(() => route.params.slug, load)
</script>

<style scoped>
.genre-page { min-height: 100%; }

.genre-header {
  position: relative;
  padding: 48px 32px 36px;
}

.header-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 50%, #0A0A0A 100%);
}

.header-content { position: relative; z-index: 1; }

.type-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.6); margin-bottom: 8px; }

.genre-name {
  font-family: 'Spline Sans', sans-serif;
  font-size: 52px;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  letter-spacing: -1px;
}

.track-count { font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 8px; }

.content { padding: 0 24px 48px; }

.action-bar { display: flex; gap: 12px; padding: 20px 0 24px; }

.btn-play {
  width: 52px; height: 52px;
  background: #FF0000;
  border: none; border-radius: 50%;
  color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 18px rgba(255,0,0,0.4);
  transition: all 0.15s;
}
.btn-play:hover { transform: scale(1.08); }
.btn-play .material-symbols-outlined { font-size: 28px; }

.btn-shuffle {
  width: 44px; height: 44px;
  background: transparent;
  border: 1.5px solid #2a2a2a; border-radius: 50%;
  color: #888; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.15s;
}
.btn-shuffle:hover { color: #fff; border-color: #fff; }
.btn-shuffle .material-symbols-outlined { font-size: 20px; }

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

.empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px; color: #555; }

.page-loading { display: flex; align-items: center; justify-content: center; height: 40vh; }

.spinner {
  width: 36px; height: 36px;
  border: 3px solid #1A1A1A;
  border-top-color: #FF0000;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
