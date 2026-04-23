<template>
  <div class="home-page">
    <!-- Hero -->
    <div class="hero" v-if="discovery.featured_artists?.length">
      <div class="hero-bg" :style="heroBg"></div>
      <div class="hero-content">
        <p class="hero-tag">Featured Artist</p>
        <h1 class="hero-name">{{ heroArtist.name }}</h1>
        <p class="hero-listeners">{{ formatListeners(heroArtist.monthly_listeners) }} monthly listeners</p>
        <div class="hero-actions">
          <button class="btn-play-hero" @click="playFirstTrack">
            <span class="material-symbols-outlined filled">play_arrow</span> Play
          </button>
          <RouterLink :to="`/artist/${heroArtist.id}`" class="btn-view">View Artist</RouterLink>
        </div>
      </div>
    </div>

    <div class="content-area">
      <!-- Featured Albums -->
      <section v-if="discovery.featured_albums?.length" class="section">
        <div class="section-header">
          <h2 class="section-title">Featured Albums</h2>
        </div>
        <div class="card-scroll">
          <RouterLink
            v-for="album in discovery.featured_albums"
            :key="album.id"
            :to="`/album/${album.id}`"
            class="album-card"
          >
            <div class="album-cover-wrap">
              <img :src="album.cover || ''" :alt="album.title" class="album-cover"
                @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22><rect fill=%22%231A1A1A%22 width=%221%22 height=%221%22/></svg>'" />
              <div class="album-overlay">
                <button class="album-play" @click.prevent="playAlbum(album)">
                  <span class="material-symbols-outlined filled">play_arrow</span>
                </button>
              </div>
            </div>
            <p class="album-title">{{ album.title }}</p>
            <p class="album-artist">{{ album.artist_name }}</p>
          </RouterLink>
        </div>
      </section>

      <!-- Recent Tracks -->
      <section v-if="discovery.recent_tracks?.length" class="section">
        <div class="section-header">
          <h2 class="section-title">Recently Added</h2>
        </div>
        <div class="tracks-header-row">
          <span class="th th-num">#</span>
          <span class="th">Title</span>
          <span class="th">Album</span>
          <span class="th th-dur">
            <span class="material-symbols-outlined" style="font-size:16px">schedule</span>
          </span>
        </div>
        <div class="divider"></div>
        <TrackRow
          v-for="(track, i) in discovery.recent_tracks"
          :key="track.id"
          :track="track"
          :number="i + 1"
          :queue="discovery.recent_tracks"
          show-cover
        />
      </section>

      <!-- Browse by Genre -->
      <section v-if="discovery.genres?.length" class="section">
        <div class="section-header">
          <h2 class="section-title">Browse by Genre</h2>
        </div>
        <div class="genre-grid">
          <RouterLink
            v-for="genre in discovery.genres"
            :key="genre.id"
            :to="`/genre/${genre.slug}`"
            class="genre-card"
            :style="{ background: genreColor(genre.slug) }"
          >
            <p class="genre-name">{{ genre.name }}</p>
            <p class="genre-count">{{ genre.count }} tracks</p>
          </RouterLink>
        </div>
      </section>

      <!-- Featured Artists -->
      <section v-if="discovery.featured_artists?.length > 1" class="section">
        <div class="section-header">
          <h2 class="section-title">Featured Artists</h2>
        </div>
        <div class="artists-row">
          <RouterLink
            v-for="artist in discovery.featured_artists"
            :key="artist.id"
            :to="`/artist/${artist.id}`"
            class="artist-card"
          >
            <img :src="artist.image || ''" :alt="artist.name" class="artist-img"
              @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22><rect fill=%22%231A1A1A%22 width=%221%22 height=%221%22/></svg>'" />
            <p class="artist-name">{{ artist.name }}</p>
            <p class="artist-meta">{{ formatListeners(artist.monthly_listeners) }} listeners</p>
          </RouterLink>
        </div>
      </section>

      <!-- Empty state -->
      <div v-if="!loading && isEmpty" class="empty-state">
        <span class="material-symbols-outlined" style="font-size:64px;color:#333">library_music</span>
        <h2>No music yet</h2>
        <p>Add artists, albums, and tracks from the WordPress admin dashboard to get started.</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-grid">
        <div v-for="i in 8" :key="i" class="skeleton-card"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '@/services/api'
import { usePlayerStore } from '@/stores/player'
import TrackRow from '@/components/TrackRow.vue'

const player = usePlayerStore()
const discovery = ref({})
const loading = ref(true)

const heroArtist = computed(() => discovery.value.featured_artists?.[0] || {})
const heroBg = computed(() => heroArtist.value.image
  ? { backgroundImage: `url(${heroArtist.value.image})` }
  : { background: 'linear-gradient(135deg, #1a0000 0%, #0A0A0A 100%)' }
)
const isEmpty = computed(() =>
  !discovery.value.featured_albums?.length &&
  !discovery.value.recent_tracks?.length &&
  !discovery.value.featured_artists?.length
)

onMounted(async () => {
  try {
    discovery.value = await api.discovery()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

function playFirstTrack() {
  const tracks = discovery.value.recent_tracks
  if (tracks?.length) player.playTrack(tracks[0], tracks)
}

function playAlbum(album) {
  // Quick play - will load album tracks from store
  api.album(album.id).then(data => {
    if (data.tracks?.length) player.playTrack(data.tracks[0], data.tracks)
  })
}

function formatListeners(n) {
  if (!n) return ''
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000) return `${(n / 1_000).toFixed(0)}K`
  return n.toLocaleString()
}

const GENRE_COLORS = [
  'linear-gradient(135deg,#6B0000,#1a0000)',
  'linear-gradient(135deg,#003d5b,#001a26)',
  'linear-gradient(135deg,#1a3a00,#080f00)',
  'linear-gradient(135deg,#3d0045,#1a0020)',
  'linear-gradient(135deg,#3d2800,#1a1000)',
  'linear-gradient(135deg,#003d2a,#001a12)',
]

function genreColor(slug) {
  let hash = 0
  for (const c of slug) hash = (hash << 5) - hash + c.charCodeAt(0)
  return GENRE_COLORS[Math.abs(hash) % GENRE_COLORS.length]
}
</script>

<style scoped>
.home-page { min-height: 100%; }

/* Hero */
.hero {
  position: relative;
  height: 340px;
  overflow: hidden;
  display: flex;
  align-items: flex-end;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center top;
  filter: brightness(0.4);
  transform: scale(1.05);
}

.hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, #0A0A0A 0%, transparent 60%);
}

.hero-content {
  position: relative;
  z-index: 1;
  padding: 32px 32px 40px;
}

.hero-tag {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: #FF0000;
  margin-bottom: 8px;
}

.hero-name {
  font-family: 'Spline Sans', sans-serif;
  font-size: 52px;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  letter-spacing: -1.5px;
  margin-bottom: 6px;
}

.hero-listeners {
  color: #aaa;
  font-size: 14px;
  margin-bottom: 20px;
}

.hero-actions { display: flex; gap: 12px; align-items: center; }

.btn-play-hero {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #FF0000;
  color: #fff;
  border: none;
  padding: 12px 28px;
  border-radius: 30px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  box-shadow: 0 0 24px rgba(255,0,0,0.4);
}

.btn-play-hero:hover { background: #cc0000; transform: scale(1.04); }
.btn-play-hero .material-symbols-outlined { font-size: 22px; }

.btn-view {
  color: #fff;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  border: 1.5px solid rgba(255,255,255,0.3);
  padding: 11px 24px;
  border-radius: 30px;
  transition: all 0.15s;
}
.btn-view:hover { border-color: #fff; background: rgba(255,255,255,0.08); }

/* Content */
.content-area { padding: 0 24px 40px; }

.section { margin-top: 40px; }

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.section-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: #fff;
}

/* Album cards */
.card-scroll {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
}

.album-card {
  text-decoration: none;
  color: inherit;
}

.album-cover-wrap {
  position: relative;
  aspect-ratio: 1;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 10px;
  background: #1A1A1A;
}

.album-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.album-card:hover .album-cover { transform: scale(1.05); }

.album-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.album-card:hover .album-overlay { opacity: 1; }

.album-play {
  width: 48px;
  height: 48px;
  background: #FF0000;
  border: none;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(255,0,0,0.5);
  transform: translateY(6px);
  transition: transform 0.2s;
}

.album-card:hover .album-play { transform: translateY(0); }
.album-play .material-symbols-outlined { font-size: 26px; }

.album-title {
  font-size: 13px;
  font-weight: 600;
  color: #e5e2e1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.album-artist {
  font-size: 12px;
  color: #888;
  margin-top: 2px;
}

/* Track list header */
.tracks-header-row {
  display: grid;
  grid-template-columns: 48px 1fr 1fr 60px;
  padding: 0 16px;
  margin-bottom: 6px;
}

.th {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #666;
}

.th-num { text-align: center; }
.th-dur { text-align: right; }

.divider {
  height: 1px;
  background: #1A1A1A;
  margin-bottom: 8px;
}

/* Genre grid */
.genre-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}

.genre-card {
  padding: 20px 16px;
  border-radius: 10px;
  text-decoration: none;
  position: relative;
  overflow: hidden;
  transition: transform 0.15s, box-shadow 0.15s;
  min-height: 100px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.genre-card:hover { transform: scale(1.03); box-shadow: 0 8px 24px rgba(0,0,0,0.4); }

.genre-name {
  font-family: 'Spline Sans', sans-serif;
  font-size: 16px;
  font-weight: 800;
  color: #fff;
  line-height: 1.2;
}

.genre-count {
  font-size: 11px;
  color: rgba(255,255,255,0.6);
  margin-top: 4px;
}

/* Artists */
.artists-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 16px;
}

.artist-card {
  text-decoration: none;
  text-align: center;
}

.artist-img {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 50%;
  object-fit: cover;
  background: #1A1A1A;
  transition: transform 0.2s, box-shadow 0.2s;
}

.artist-card:hover .artist-img {
  transform: scale(1.05);
  box-shadow: 0 8px 24px rgba(255,0,0,0.3);
}

.artist-name {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  margin-top: 10px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.artist-meta { font-size: 12px; color: #888; margin-top: 2px; }

/* Empty */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 80px 0;
  text-align: center;
}

.empty-state h2 { font-size: 22px; color: #555; }
.empty-state p { color: #444; max-width: 360px; }

/* Loading skeletons */
.loading-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
  margin-top: 40px;
}

.skeleton-card {
  aspect-ratio: 1;
  border-radius: 10px;
  background: linear-gradient(90deg, #1A1A1A 25%, #222 50%, #1A1A1A 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
