<template>
  <div class="search-page">
    <!-- Search Bar -->
    <div class="search-header">
      <div class="search-bar">
        <span class="material-symbols-outlined search-icon">search</span>
        <input
          ref="inputRef"
          v-model="query"
          @input="onInput"
          class="search-input"
          placeholder="What do you want to listen to?"
          type="text"
          autocomplete="off"
        />
        <button v-if="query" @click="clear" class="clear-btn">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
    </div>

    <!-- Results -->
    <div class="results" v-if="query && !loading">
      <div v-if="hasResults">
        <!-- Artists -->
        <section v-if="results.artists?.length" class="section">
          <h2 class="section-title">Artists</h2>
          <div class="artists-row">
            <RouterLink v-for="a in results.artists" :key="a.id" :to="`/artist/${a.id}`" class="artist-card">
              <img :src="a.image || ''" class="artist-img"
                @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22><rect fill=%22%221A1A1A%22 width=%221%22 height=%221%22/></svg>'" />
              <p class="artist-name">{{ a.name }}</p>
              <p class="artist-type">Artist</p>
            </RouterLink>
          </div>
        </section>

        <!-- Albums -->
        <section v-if="results.albums?.length" class="section">
          <h2 class="section-title">Albums</h2>
          <div class="albums-row">
            <RouterLink v-for="a in results.albums" :key="a.id" :to="`/album/${a.id}`" class="album-card">
              <img :src="a.cover || ''" class="album-img"
                @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22><rect fill=%22%221A1A1A%22 width=%221%22 height=%221%22/></svg>'" />
              <p class="album-name">{{ a.title }}</p>
              <p class="album-meta">{{ a.artist_name }} • Album</p>
            </RouterLink>
          </div>
        </section>

        <!-- Tracks -->
        <section v-if="results.tracks?.length" class="section">
          <h2 class="section-title">Tracks</h2>
          <TrackRow
            v-for="(t, i) in results.tracks"
            :key="t.id"
            :track="t"
            :number="i + 1"
            :queue="results.tracks"
            show-cover
          />
        </section>
      </div>

      <div v-else class="no-results">
        <span class="material-symbols-outlined" style="font-size:48px;color:#333">search_off</span>
        <p>No results for "<strong>{{ query }}</strong>"</p>
        <p class="hint">Try different keywords or check the spelling.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="page-loading">
      <div class="spinner"></div>
    </div>

    <!-- Browse Genres (empty state) -->
    <div v-else class="browse-section">
      <h2 class="section-title">Browse Genres</h2>
      <div class="genre-grid">
        <RouterLink
          v-for="genre in genres"
          :key="genre.id"
          :to="`/genre/${genre.slug}`"
          class="genre-card"
          :style="{ background: genreColor(genre.slug) }"
        >
          <p class="genre-name">{{ genre.name }}</p>
          <p class="genre-count">{{ genre.count }} tracks</p>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '@/services/api'
import TrackRow from '@/components/TrackRow.vue'

const query = ref('')
const results = ref({})
const genres = ref([])
const loading = ref(false)
let debounceTimer = null
const inputRef = ref(null)

const hasResults = computed(() =>
  results.value.tracks?.length || results.value.albums?.length || results.value.artists?.length
)

onMounted(async () => {
  inputRef.value?.focus()
  try {
    genres.value = await api.genres()
  } catch (e) {}
})

function onInput() {
  clearTimeout(debounceTimer)
  if (!query.value.trim()) { results.value = {}; return }
  debounceTimer = setTimeout(doSearch, 350)
}

async function doSearch() {
  loading.value = true
  try {
    results.value = await api.search(query.value.trim())
  } catch (e) {
    results.value = {}
  } finally {
    loading.value = false
  }
}

function clear() {
  query.value = ''
  results.value = {}
  inputRef.value?.focus()
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
.search-page { padding: 24px 32px 48px; min-height: 100%; }

.search-header { margin-bottom: 32px; }

.search-bar {
  position: relative;
  max-width: 560px;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  font-size: 22px;
  pointer-events: none;
}

.search-input {
  width: 100%;
  background: #1A1A1A;
  border: 2px solid #2a2a2a;
  border-radius: 30px;
  padding: 14px 48px 14px 52px;
  color: #fff;
  font-size: 16px;
  font-family: 'Inter', sans-serif;
  outline: none;
  transition: border-color 0.15s;
}

.search-input:focus { border-color: #FF0000; }
.search-input::placeholder { color: #555; }

.clear-btn {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  transition: color 0.15s;
}
.clear-btn:hover { color: #fff; }
.clear-btn .material-symbols-outlined { font-size: 20px; }

.section { margin-bottom: 36px; }

.section-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 14px;
}

.artists-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 16px;
}

.artist-card { text-decoration: none; text-align: center; }

.artist-img {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 50%;
  object-fit: cover;
  background: #1A1A1A;
  transition: transform 0.2s;
}
.artist-card:hover .artist-img { transform: scale(1.05); }

.artist-name { font-size: 13px; font-weight: 600; color: #fff; margin-top: 8px; }
.artist-type { font-size: 11px; color: #888; }

.albums-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 16px;
}

.album-card { text-decoration: none; }

.album-img {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 8px;
  object-fit: cover;
  background: #1A1A1A;
  transition: transform 0.2s;
}
.album-card:hover .album-img { transform: scale(1.04); }

.album-name { font-size: 13px; font-weight: 600; color: #fff; margin-top: 8px; }
.album-meta { font-size: 11px; color: #888; margin-top: 2px; }

.no-results, .browse-section { }

.no-results {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 60px 0;
  color: #555;
  text-align: center;
}
.no-results strong { color: #fff; }
.hint { font-size: 13px; color: #444; }

.genre-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
}

.genre-card {
  padding: 24px 18px;
  border-radius: 10px;
  text-decoration: none;
  min-height: 110px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  transition: transform 0.15s;
}
.genre-card:hover { transform: scale(1.03); }

.genre-name { font-family: 'Spline Sans', sans-serif; font-size: 18px; font-weight: 800; color: #fff; }
.genre-count { font-size: 12px; color: rgba(255,255,255,0.6); margin-top: 4px; }

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
