<template>
  <div 
    class="track-row" 
    :class="{ playing: isCurrentTrack, active }" 
    :style="gridStyle"
    @dblclick="play" 
    @mouseenter="hovering = true" 
    @mouseleave="hovering = false"
  >
    <!-- Number / Play indicator -->
    <div class="col-num" v-if="!hideNumber">
      <span v-if="!hovering && !isCurrentTrack" class="track-num"></span>
      <button v-else class="play-icon-btn" @click="play">
        <span class="material-symbols-outlined filled">{{ isCurrentTrack && player.isPlaying ? 'pause' : 'play_arrow' }}</span>
      </button>
      <span v-if="isCurrentTrack && !hovering" class="playing-indicator">
        <span class="bar" v-for="i in 3" :key="i"></span>
      </span>
    </div>

    <!-- Cover + Title -->
    <div class="col-title">
      <div class="cover-container" v-if="showCover">
        <img :src="track.cover || ''" class="mini-cover" @error="$event.target.style.display='none'" />
        <div class="cover-overlay" v-if="hideNumber && (hovering || isCurrentTrack)">
          <button class="play-icon-btn overlay-play" @click="play">
            <span class="material-symbols-outlined filled">{{ isCurrentTrack && player.isPlaying ? 'pause' : 'play_arrow' }}</span>
          </button>
        </div>
        <span v-if="hideNumber && isCurrentTrack && !hovering" class="playing-indicator overlay-indicator">
          <span class="bar" v-for="i in 3" :key="i"></span>
        </span>
      </div>
      <div class="title-meta">
        <p class="title" :class="{ 'text-red': isCurrentTrack }">{{ track.title }}</p>
        <p class="subtitle">
          <template v-if="track.artists && track.artists.length">
            <template v-for="(artist, index) in track.artists" :key="artist.id">
              <RouterLink :to="`/artist/${artist.id}`" class="sub-link" @click.stop>
                {{ artist.name }}
              </RouterLink><span v-if="index < track.artists.length - 1">, </span>
            </template>
          </template>
          <template v-else>
            <RouterLink v-if="track.artist_id" :to="`/artist/${track.artist_id}`" class="sub-link" @click.stop>
              {{ track.artist_name }}
            </RouterLink>
            <span v-else>{{ track.artist_name || 'Unknown' }}</span>
          </template>
        </p>
      </div>
    </div>

    <!-- Album -->
    <div class="col-album" v-if="showAlbum">
      <RouterLink v-if="track.album_id" :to="`/album/${track.album_id}`" class="sub-link" @click.stop>
        {{ track.album_title }}
      </RouterLink>
      <span v-else class="muted">—</span>
    </div>

    <!-- Streams -->
    <div class="col-streams">{{ formatStreams(track.streams) }}</div>

    <!-- Duration -->
    <div class="col-dur">{{ track.duration || '—' }}</div>

    <!-- Actions -->
    <div class="col-actions">
      <button 
        v-if="auth.isLoggedIn"
        class="action-btn like-btn" 
        :class="{ liked: isLiked }"
        @click.stop="auth.toggleLike(track.id)"
        title="Like Song"
      >
        <span class="material-symbols-outlined">{{ isLiked ? 'favorite' : 'favorite' }}</span>
      </button>

      <div class="more-menu-wrap" @click.stop>
        <button class="action-btn more-btn" @click="showDropdown = !showDropdown" title="More options">
          <span class="material-symbols-outlined">more_horiz</span>
        </button>
        
        <div v-if="showDropdown" class="dropdown-menu">
          <button class="dropdown-item" @click="handleAddToPlaylist">
            <span class="material-symbols-outlined">add</span>
            Add to playlist
          </button>
          <button class="dropdown-item" @click="handleDownload">
            <span class="material-symbols-outlined">download</span>
            Download
          </button>
          <button class="dropdown-item" @click="handleShare">
            <span class="material-symbols-outlined">share</span>
            Share
          </button>
        </div>
      </div>
    </div>

    <AddToPlaylistModal 
      :is-open="showPlaylistModal" 
      :track-id="track.id" 
      @close="showPlaylistModal = false" 
    />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'
import { api } from '@/services/api'
import AddToPlaylistModal from './AddToPlaylistModal.vue'

const props = defineProps({
  track: { type: Object, required: true },
  number: { type: Number, default: 1 },
  queue: { type: Array, default: null },
  showCover: { type: Boolean, default: false },
  showAlbum: { type: Boolean, default: true },
  hideNumber: { type: Boolean, default: false },
})

const player = usePlayerStore()
const auth = useAuthStore()
const hovering = ref(false)
const showPlaylistModal = ref(false)
const showDropdown = ref(false)

const isLiked = computed(() => auth.isTrackLiked(props.track.id))
const isCurrentTrack = computed(() => player.currentTrack?.id === props.track.id)
const active = computed(() => isCurrentTrack.value)

const gridStyle = computed(() => {
  const isMobile = window.innerWidth < 768
  let cols = []
  
  if (!props.hideNumber) cols.push('48px')
  cols.push('1fr') // Title
  
  if (isMobile) {
    cols.push('80px') // Reduced for Like + More
  } else {
    if (props.showAlbum) cols.push('1fr')
    cols.push('100px') // Streams
    cols.push('60px') // Duration
    cols.push('80px') // Actions
  }
  
  return { gridTemplateColumns: cols.join(' ') }
})

function closeDropdown(e) {
  if (showDropdown.value) showDropdown.value = false
}

onMounted(() => window.addEventListener('click', closeDropdown))
onUnmounted(() => window.removeEventListener('click', closeDropdown))

function play() {
  if (isCurrentTrack.value) {
    player.togglePlay()
  } else {
    player.playTrack(props.track, props.queue)
  }
}

function handleAddToPlaylist() {
  showPlaylistModal.value = true
  showDropdown.value = false
}

async function handleShare() {
  showDropdown.value = false
  const shareData = {
    title: props.track.title,
    text: `Listen to ${props.track.title} by ${props.track.artists?.map(a => a.name).join(', ') || props.track.artist_name} on ${api.config.playerName}`,
    url: window.location.origin + window.location.pathname + `#/album/${props.track.album_id || ''}`
  }
  
  try {
    if (navigator.share) {
      await navigator.share(shareData)
    } else {
      await navigator.clipboard.writeText(shareData.url)
      alert('Link copied to clipboard!')
    }
  } catch (e) {
    console.error('Share failed:', e)
  }
}

async function handleDownload() {
  showDropdown.value = false
  if (!props.track.audio_url) return
  
  try {
    const response = await fetch(props.track.audio_url)
    if (!response.ok) throw new Error('Network response was not ok')
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.style.display = 'none'
    a.href = url
    const safeTitle = props.track.title.replace(/[<>:"/\\|?*]/g, '')
    const artistNames = props.track.artists?.map(a => a.name).join(', ') || props.track.artist_name
    const safeArtist = artistNames.replace(/[<>:"/\\|?*]/g, '')
    a.download = `${safeArtist} - ${safeTitle}.mp3`
    
    document.body.appendChild(a)
    a.click()
    
    setTimeout(() => {
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
    }, 100)
  } catch (e) {
    console.error('Download failed:', e)
    window.open(props.track.audio_url, '_blank')
  }
}

function formatStreams(n) {
  if (!n) return '0'
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000) return `${(n / 1_000).toFixed(0)}K`
  return n.toLocaleString()
}
</script>

<style scoped>
.track-row {
  display: grid;
  align-items: center;
  height: 56px;
  /* padding: 0 16px; */
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.1s;
}

.track-row:hover { background: #1A1A1A; }
.track-row.playing { background: rgba(255, 0, 0, 0.04); }

.col-num {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  width: 32px;
}

.track-num {
  font-size: 14px;
  color: #666;
}

.play-icon-btn {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
}
.play-icon-btn .material-symbols-outlined { font-size: 20px; }

/* Playing bars animation */
.playing-indicator {
  display: flex;
  align-items: flex-end;
  gap: 2px;
  height: 16px;
}

.bar {
  display: block;
  width: 3px;
  background: #FF0000;
  border-radius: 2px;
  animation: equalizer 0.8s ease-in-out infinite;
}

.bar:nth-child(1) { height: 8px; animation-delay: 0s; }
.bar:nth-child(2) { height: 14px; animation-delay: 0.15s; }
.bar:nth-child(3) { height: 10px; animation-delay: 0.3s; }

@keyframes equalizer {
  0%, 100% { transform: scaleY(1); }
  50% { transform: scaleY(0.4); }
}

.col-title {
  display: flex;
  align-items: center;
  gap: 12px;
  overflow: hidden;
  min-width: 0;
}

.title-meta {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 0;
}

.mini-cover {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  object-fit: cover;
  flex-shrink: 0;
}

.title {
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cover-container {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 4px;
  overflow: hidden;
  flex-shrink: 0;
}

.cover-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.overlay-play { color: #fff; }
.overlay-play .material-symbols-outlined { font-size: 24px; }

.overlay-indicator {
  position: absolute;
  bottom: 4px;
  right: 4px;
}

.text-red { color: #FF0000 !important; }

.subtitle {
  font-size: 11px;
  color: #888;
  margin-top: 0px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.col-album {
  font-size: 13px;
  color: #666;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 12px;
}

.col-streams {
  font-size: 13px;
  color: #666;
  text-align: right;
  padding-right: 24px;
}

.col-dur {
  font-size: 13px;
  color: #666;
  text-align: right;
}

@media (max-width: 768px) {
  .col-album, .col-streams, .col-dur {
    display: none !important;
  }
}

.col-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  opacity: 0.4;
  transition: opacity 0.15s;
}

.track-row:hover .col-actions, .col-actions:focus-within {
  opacity: 1;
}

.more-menu-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  z-index: 1000;
  background: #282828;
  min-width: 180px;
  padding: 4px;
  border-radius: 4px;
  box-shadow: 0 16px 24px rgba(0,0,0,0.5);
  margin-top: 8px;
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: none;
  border: none;
  color: #eaeaea;
  font-size: 13px;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  border-radius: 2px;
  transition: background 0.1s;
}

.dropdown-item:hover {
  background: #3e3e3e;
  color: #fff;
}

.dropdown-item .material-symbols-outlined {
  font-size: 20px;
  color: #b3b3b3;
}

.action-btn {
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3px;
  border-radius: 50%;
  transition: all 0.2s;
}

.action-btn .material-symbols-outlined {
  font-size: 20px;
}

.action-btn:hover {
  color: #fff;
}

.like-btn.liked {
  color: #FF0000;
  font-variation-settings: 'FILL' 1;
}

.sub-link {
  color: inherit;
  text-decoration: none;
}
.sub-link:hover { color: #FF0000; text-decoration: underline; }

.muted { color: #444; }
</style>
