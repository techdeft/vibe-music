<template>
  <div v-if="album" class="album-page">
    <!-- Header -->
    <div class="album-header" :style="headerBg">
      <div class="header-overlay"></div>
      <div class="header-content">
        <div class="album-cover-wrap">
          <img :src="album.cover || ''" :alt="album.title" class="album-cover"
            @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22><rect fill=%22%221A1A1A%22 width=%221%22 height=%221%22/></svg>'" />
        </div>
        <div class="album-info">
          <p class="type-badge">Album</p>
          <h1 class="album-title">{{ album.title }}</h1>
          <div class="album-meta">
            <RouterLink v-if="album.artist_id" :to="`/artist/${album.artist_id}`" class="artist-link">
              {{ album.artist_name }}
            </RouterLink>
            <span v-if="album.release_date">• {{ album.release_date.split('-')[0] }}</span>
            <span v-if="album.tracks?.length">• {{ album.tracks.length }} songs</span>
            <span v-if="totalDuration">• {{ totalDuration }}</span>
          </div>
          <div class="genre-tags" v-if="album.genres?.length">
            <RouterLink
              v-for="g in album.genres"
              :key="g.id"
              :to="`/genre/${g.slug}`"
              class="genre-tag"
            >{{ g.name }}</RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="action-bar">
      <button class="btn-play" @click="playAll" :disabled="!album.tracks?.length">
        <span class="material-symbols-outlined filled">play_arrow</span>
      </button>
      <button class="btn-shuffle" @click="playShuffle" :disabled="!album.tracks?.length">
        <span class="material-symbols-outlined">shuffle</span>
      </button>
    </div>

    <!-- Track List -->
    <div class="tracks-section" v-if="album.tracks?.length">
      <div class="tracks-header-row">
        <span class="th th-num">#</span>
        <span class="th">Title</span>
        <span class="th th-streams">Plays</span>
        <span class="th th-dur"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
      </div>
      <div class="divider"></div>

      <div class="tracks-list">
        <TrackRow
          v-for="(track, i) in album.tracks"
          :key="track.id"
          :track="track"
          :number="track.track_number || i + 1"
          :queue="album.tracks"
          :show-album="false"
        />
      </div>
    </div>

    <div v-else class="empty-tracks">
      <span class="material-symbols-outlined" style="font-size:40px;color:#333">music_off</span>
      <p>No tracks in this album yet.</p>
    </div>
  </div>

  <div v-else-if="loading" class="page-loading">
    <div class="spinner"></div>
  </div>

  <div v-else class="empty-state">
    <span class="material-symbols-outlined" style="font-size:48px;color:#333">album</span>
    <p>Album not found</p>
    <RouterLink to="/" class="back-link">← Back to Discovery</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/services/api'
import { usePlayerStore } from '@/stores/player'
import TrackRow from '@/components/TrackRow.vue'

const route = useRoute()
const player = usePlayerStore()
const album = ref(null)
const loading = ref(true)

const headerBg = computed(() =>
  album.value?.cover
    ? { backgroundImage: `url(${album.value.cover})` }
    : { background: 'linear-gradient(135deg, #1a0000, #0A0A0A)' }
)

const totalDuration = computed(() => {
  if (!album.value?.tracks?.length) return ''
  let total = 0
  for (const t of album.value.tracks) {
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
  album.value = null
  try {
    album.value = await api.album(route.params.id)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function playAll() {
  const tracks = album.value?.tracks
  if (tracks?.length) player.playTrack(tracks[0], tracks)
}

function playShuffle() {
  const tracks = album.value?.tracks
  if (!tracks?.length) return
  player.toggleShuffle()
  const idx = Math.floor(Math.random() * tracks.length)
  player.playTrack(tracks[idx], tracks)
}

onMounted(load)
watch(() => route.params.id, load)
</script>

<style scoped>
.album-page { min-height: 100%; }

.album-header {
  position: relative;
  background-size: cover;
  background-position: center;
  padding-top: 32px;
}

.header-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, #0A0A0A 100%);
  backdrop-filter: blur(40px);
}

.header-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  gap: 24px;
  padding: 20px 32px 32px;
}

.album-cover-wrap {
  width: 200px;
  height: 200px;
  flex-shrink: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 16px 48px rgba(0,0,0,0.6);
}

.album-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.album-info { flex: 1; }

.type-badge {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #FF0000;
  margin-bottom: 8px;
}

.album-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 40px;
  font-weight: 900;
  color: #fff;
  line-height: 1.1;
  letter-spacing: -0.5px;
  margin-bottom: 10px;
}

.album-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  font-size: 13px;
  color: #888;
}

.artist-link {
  color: #fff;
  font-weight: 600;
  text-decoration: none;
}
.artist-link:hover { text-decoration: underline; color: #FF0000; }

.genre-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }

.genre-tag {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border: 1px solid #2a2a2a;
  border-radius: 20px;
  color: #888;
  text-decoration: none;
  transition: all 0.15s;
}
.genre-tag:hover { border-color: #FF0000; color: #FF0000; }

.action-bar {
  display: flex;
  align-items: center;
  gap: 14px;
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
  box-shadow: 0 0 20px rgba(255,0,0,0.4);
  transition: all 0.15s;
}
.btn-play:hover { transform: scale(1.08); }
.btn-play:disabled { background: #333; box-shadow: none; cursor: default; }
.btn-play .material-symbols-outlined { font-size: 30px; }

.btn-shuffle {
  width: 44px;
  height: 44px;
  background: transparent;
  border: 1.5px solid #2a2a2a;
  border-radius: 50%;
  color: #888;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.btn-shuffle:hover { color: #fff; border-color: #fff; }
.btn-shuffle .material-symbols-outlined { font-size: 20px; }

.tracks-section { padding: 0 24px 48px; }

.tracks-header-row {
  display: grid;
  grid-template-columns: 48px 1fr 100px 60px;
  padding: 0 16px;
  margin-bottom: 6px;
}

.th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
.th-num { text-align: center; }
.th-streams { text-align: right; padding-right: 24px; }
.th-dur { text-align: right; }

.divider { height: 1px; background: #1A1A1A; margin-bottom: 8px; }

.empty-tracks, .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 60px;
  color: #555;
}

.back-link { color: #FF0000; text-decoration: none; }

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
