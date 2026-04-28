<template>
  <div class="artist-page" v-if="artist">
    <!-- Artist Header -->
    <div class="artist-hero" :style="heroBg">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <img :src="artist.image || ''" :alt="artist.name" class="artist-avatar"
          @error="$event.target.style.display='none'" />
        <div>
          <p class="verified-badge"><span class="material-symbols-outlined filled" style="font-size:16px;color:#1d9bf0">verified</span> Verified Artist</p>
          <h1 class="artist-name">{{ artist.name }}</h1>
          <p class="listeners" v-if="artist.monthly_listeners">{{ formatListeners(artist.monthly_listeners) }} monthly listeners</p>
        </div>
      </div>
    </div>

    <div class="content">
      <!-- Actions -->
      <div class="action-bar">
        <button class="btn-play" @click="playAll" :disabled="!artist.top_tracks?.length">
          <span class="material-symbols-outlined filled">play_arrow</span>
        </button>
        <div class="social-links">
          <a v-if="donationLink" :href="donationLink" target="_blank" class="social-btn gift-btn" title="Send a Gift">
            <span class="material-symbols-outlined">redeem</span> Gift the Artist
          </a>
          <a v-if="artist.spotify_url" :href="artist.spotify_url" target="_blank" class="social-btn" title="Spotify">
            <span class="material-symbols-outlined">open_in_new</span> Spotify
          </a>
          <a v-if="artist.instagram_url" :href="artist.instagram_url" target="_blank" class="social-btn" title="Instagram">
            <span class="material-symbols-outlined">open_in_new</span> Instagram
          </a>
        </div>
      </div>

      <!-- Bio -->
      <p v-if="artist.bio" class="bio">{{ artist.bio }}</p>

      <!-- Top Tracks -->
      <section v-if="artist.top_tracks?.length" class="section">
        <h2 class="section-title">Popular</h2>
        <div class="tracks-header-row">
          <span class="th th-num"></span>
          <span class="th">Title</span>
          <span class="th mobile-hide">Album</span>
          <span class="th th-streams mobile-hide">Plays</span>
          <span class="th th-dur mobile-hide"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
          <span class="th"></span>
        </div>
        <div class="divider"></div>
        <TrackRow
          v-for="(track, i) in artist.top_tracks"
          :key="track.id"
          :track="track"
          :number="i + 1"
          :queue="artist.top_tracks"
          show-cover
        />
      </section>

      <!-- Albums -->
      <section v-if="artist.albums?.length" class="section">
        <h2 class="section-title">Albums</h2>
        <div class="albums-grid">
          <RouterLink v-for="album in artist.albums" :key="album.id" :to="`/album/${album.id}`" class="album-card">
            <div class="album-cover-wrap">
              <img :src="album.cover || ''" :alt="album.title" class="album-cover"
                @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22><rect fill=%22%231A1A1A%22 width=%221%22 height=%221%22/></svg>'" />
            </div>
            <p class="album-title">{{ album.title }}</p>
            <p class="album-year">{{ album.release_date?.split('-')[0] }}</p>
          </RouterLink>
        </div>
      </section>
    </div>
  </div>

  <!-- Loading -->
  <div v-else-if="loading" class="page-loading">
    <div class="spinner"></div>
  </div>

  <!-- Error -->
  <div v-else class="empty-state">
    <span class="material-symbols-outlined" style="font-size:48px;color:#333">person_off</span>
    <p>Artist not found</p>
    <RouterLink to="/" class="back-link">← Back to Discovery</RouterLink>
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
const artist = ref(null)
const loading = ref(true)

const donationLink = api.config.donationLink || ''

const heroBg = computed(() =>
  artist.value?.image
    ? { backgroundImage: `url(${artist.value.image})` }
    : { background: 'linear-gradient(135deg, #1a0000, #0A0A0A)' }
)

async function load() {
  loading.value = true
  artist.value = null
  try {
    artist.value = await api.artist(route.params.id)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function playAll() {
  const tracks = artist.value?.top_tracks
  if (tracks?.length) player.playTrack(tracks[0], tracks)
}

function formatListeners(n) {
  if (!n) return ''
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000) return `${(n / 1_000).toFixed(0)}K`
  return n.toLocaleString()
}

import { computed } from 'vue'

onMounted(load)
watch(() => route.params.id, load)
</script>

<style scoped>
.artist-page { min-height: 100%; }

.artist-hero {
  position: relative;
  height: 320px;
  background-size: cover;
  background-position: center 30%;
  display: flex;
  align-items: flex-end;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, #0A0A0A 0%, rgba(0,0,0,0.5) 60%, transparent 100%);
}

.hero-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  gap: 24px;
  padding: 28px 32px 36px;
}

.artist-avatar {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 8px 24px rgba(0,0,0,0.5);
  flex-shrink: 0;
}

.verified-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #aaa;
  margin-bottom: 6px;
}

.artist-name {
  font-family: 'Spline Sans', sans-serif;
  font-size: 48px;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  letter-spacing: -1px;
}

.listeners { font-size: 14px; color: #aaa; margin-top: 6px; }

.content { padding: 0 32px 48px; }

.action-bar {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px 0;
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
  flex-shrink: 0;
}

.btn-play:hover { transform: scale(1.08); box-shadow: 0 0 28px rgba(255,0,0,0.6); }
.btn-play:disabled { background: #333; box-shadow: none; cursor: default; }
.btn-play .material-symbols-outlined { font-size: 30px; }

.social-links { display: flex; gap: 10px; }

.social-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #888;
  text-decoration: none;
  border: 1px solid #2a2a2a;
  padding: 6px 14px;
  border-radius: 20px;
  transition: all 0.15s;
}
.social-btn:hover { color: #fff; border-color: #fff; }
.social-btn .material-symbols-outlined { font-size: 14px; }

.gift-btn {
  background: linear-gradient(135deg, #FF0000 0%, #cc0000 100%);
  color: #fff !important;
  border: none !important;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(255, 0, 0, 0.2);
}
.gift-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 0, 0, 0.4);
}

.bio {
  color: #aaa;
  font-size: 14px;
  line-height: 1.7;
  max-width: 640px;
  margin-bottom: 32px;
}

.section { margin-top: 36px; }

.section-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 16px;
}

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

.albums-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
}

.album-card { text-decoration: none; color: inherit; }

.album-cover-wrap {
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  background: #1A1A1A;
  margin-bottom: 8px;
}

.album-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.album-card:hover .album-cover { transform: scale(1.05); }

.album-title { font-size: 13px; font-weight: 600; color: #e5e2e1; }
.album-year { font-size: 12px; color: #888; margin-top: 2px; }

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

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 80px;
  color: #555;
}

.back-link { color: #FF0000; text-decoration: none; font-size: 14px; }
</style>
