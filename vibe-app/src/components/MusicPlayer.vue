<template>
  <footer class="player-bar" v-if="player.currentTrack || true">
    <!-- Track Info -->
    <div class="track-info">
      <div class="track-cover-wrap">
        <img
          v-if="player.currentTrack"
          :src="player.currentTrack.cover || ''"
          :alt="player.currentTrack.title"
          class="track-cover"
          @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%231A1A1A%22/><text x=%2250%25%22 y=%2255%25%22 text-anchor=%22middle%22 fill=%22%23333%22 font-size=%2240%22>♪</text></svg>'"
        />
        <div v-else class="track-cover-empty">
          <span class="material-symbols-outlined">music_note</span>
        </div>
      </div>

      <div class="track-meta" v-if="player.currentTrack">
        <p class="track-title">{{ player.currentTrack.title }}</p>
        <p class="track-artist">
          <RouterLink
            v-if="player.currentTrack.artist_id"
            :to="`/artist/${player.currentTrack.artist_id}`"
            class="artist-link"
          >{{ player.currentTrack.artist_name }}</RouterLink>
          <span v-else>{{ player.currentTrack.artist_name || 'Unknown' }}</span>
        </p>
      </div>
      <button 
        v-if="player.currentTrack && auth.isLoggedIn" 
        class="player-like-btn" 
        :class="{ liked: isLiked }"
        @click="auth.toggleLike(player.currentTrack.id)"
      >
        <span class="material-symbols-outlined">{{ isLiked ? 'favorite' : 'favorite' }}</span>
      </button>
      <div class="track-meta" v-else-if="!player.currentTrack">
        <p class="track-title" style="color:#444">Nothing playing</p>
      </div>
    </div>

    <!-- Playback Controls -->
    <div class="controls">
      <div class="control-buttons">
        <button class="ctrl-btn" :class="{ active: player.isShuffle }" @click="player.toggleShuffle()" title="Shuffle">
          <span class="material-symbols-outlined">shuffle</span>
        </button>
        <button class="ctrl-btn" @click="player.prev()" :disabled="!player.hasPrev" title="Previous">
          <span class="material-symbols-outlined">skip_previous</span>
        </button>
        <button class="play-btn" @click="player.togglePlay()" :disabled="!player.currentTrack">
          <span class="material-symbols-outlined filled" v-if="player.isLoading">hourglass_empty</span>
          <span class="material-symbols-outlined filled" v-else>{{ player.isPlaying ? 'pause_circle' : 'play_circle' }}</span>
        </button>
        <button class="ctrl-btn" @click="player.next()" :disabled="!player.hasNext" title="Next">
          <span class="material-symbols-outlined">skip_next</span>
        </button>
        <button
          class="ctrl-btn"
          :class="{ active: player.repeatMode !== 'none' }"
          @click="player.toggleRepeat()"
          :title="`Repeat: ${player.repeatMode}`"
        >
          <span class="material-symbols-outlined">{{ player.repeatMode === 'one' ? 'repeat_one' : 'repeat' }}</span>
        </button>
      </div>

      <!-- Progress Bar -->
      <div class="progress-row">
        <span class="time-label">{{ player.formatTime(player.currentTime) }}</span>
        <div class="progress-track" @click="handleSeek">
          <div class="progress-fill" :style="{ width: `${player.progress * 100}%` }"></div>
          <div class="progress-thumb" :style="{ left: `${player.progress * 100}%` }"></div>
        </div>
        <span class="time-label">{{ player.formatTime(player.duration) }}</span>
      </div>
    </div>

    <!-- Volume & Extra -->
    <div class="extras">
      <button class="ctrl-btn-sm" title="Add to queue">
        <span class="material-symbols-outlined">queue_music</span>
      </button>
      <button v-if="player.currentTrack" class="ctrl-btn-sm" @click="handleDownload" title="Download">
        <span class="material-symbols-outlined">download</span>
      </button>
      <button v-if="player.currentTrack" class="ctrl-btn-sm" @click="handleShare" title="Share">
        <span class="material-symbols-outlined">share</span>
      </button>
      <div class="volume-control">
        <span class="material-symbols-outlined" style="font-size:18px;color:#888">{{ player.volume === 0 ? 'volume_off' : 'volume_up' }}</span>
        <input
          type="range"
          class="volume-slider"
          min="0" max="1" step="0.01"
          :value="player.volume"
          @input="player.setVolume(parseFloat($event.target.value))"
        />
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'
import { api } from '@/services/api'

const player = usePlayerStore()
const auth = useAuthStore()

const isLiked = computed(() => {
  if (!player.currentTrack) return false
  return auth.isTrackLiked(player.currentTrack.id)
})

async function handleShare() {
  if (!player.currentTrack) return
  const shareData = {
    title: player.currentTrack.title,
    text: `Listen to ${player.currentTrack.title} by ${player.currentTrack.artist_name} on ${api.config.playerName}`,
    url: window.location.origin + window.location.pathname + `#/album/${player.currentTrack.album_id || ''}`
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
  const track = player.currentTrack
  if (!track || !track.audio_url) return
  
  try {
    const response = await fetch(track.audio_url)
    if (!response.ok) throw new Error('Network response was not ok')
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.style.display = 'none'
    a.href = url
    const safeTitle = track.title.replace(/[<>:"/\\|?*]/g, '')
    const safeArtist = track.artist_name.replace(/[<>:"/\\|?*]/g, '')
    a.download = `${safeArtist} - ${safeTitle}.mp3`
    document.body.appendChild(a)
    a.click()
    setTimeout(() => {
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
    }, 100)
  } catch (e) {
    console.error('Download failed:', e)
    window.open(track.audio_url, '_blank')
  }
}

function handleSeek(e) {
  const rect = e.currentTarget.getBoundingClientRect()
  const fraction = (e.clientX - rect.left) / rect.width
  player.seek(Math.max(0, Math.min(1, fraction)))
}
</script>

<style scoped>
.player-bar {
  grid-column: 1 / 3;
  grid-row: 2;
  height: 88px;
  background: #121212;
  border-top: 1px solid #1A1A1A;
  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  align-items: center;
  padding: 0 20px;
  gap: 16px;
  box-shadow: 0 -10px 30px rgba(255, 0, 0, 0.04);
}

.track-info { display: flex; align-items: center; gap: 12px; overflow: hidden; }
.track-cover-wrap { flex-shrink: 0; }
.track-cover, .track-cover-empty { width: 52px; height: 52px; border-radius: 6px; object-fit: cover; }
.track-cover-empty { background: #1A1A1A; display: flex; align-items: center; justify-content: center; color: #333; }
.track-cover-empty .material-symbols-outlined { font-size: 24px; }
.track-meta { overflow: hidden; }
.track-title { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.track-artist { font-size: 11px; color: #888; margin-top: 2px; }
.artist-link { color: #888; text-decoration: none; }
.artist-link:hover { color: #FF0000; text-decoration: underline; }

.player-like-btn {
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  transition: all 0.2s;
}
.player-like-btn:hover { color: #fff; transform: scale(1.1); }
.player-like-btn.liked {
  color: #FF0000;
  font-variation-settings: 'FILL' 1;
}

.controls { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.control-buttons { display: flex; align-items: center; gap: 16px; }
.ctrl-btn { background: none; border: none; color: #888; cursor: pointer; padding: 4px; display: flex; align-items: center; transition: color 0.15s, transform 0.1s; border-radius: 50%; }
.ctrl-btn:hover { color: #fff; transform: scale(1.1); }
.ctrl-btn:disabled { color: #333; cursor: default; transform: none; }
.ctrl-btn.active { color: #FF0000; }
.ctrl-btn .material-symbols-outlined { font-size: 20px; }

.play-btn { background: none; border: none; color: #FF0000; cursor: pointer; padding: 0; display: flex; align-items: center; transition: transform 0.15s, filter 0.15s; filter: drop-shadow(0 0 8px rgba(255,0,0,0.4)); }
.play-btn:hover { transform: scale(1.08); filter: drop-shadow(0 0 14px rgba(255,0,0,0.6)); }
.play-btn:disabled { color: #333; filter: none; cursor: default; }
.play-btn .material-symbols-outlined { font-size: 44px; }

.progress-row { display: flex; align-items: center; gap: 10px; width: 100%; max-width: 480px; }
.time-label { font-size: 11px; color: #666; font-variant-numeric: tabular-nums; min-width: 32px; text-align: center; }
.progress-track { flex: 1; height: 4px; background: #2a2a2a; border-radius: 4px; position: relative; cursor: pointer; transition: height 0.15s; }
.progress-track:hover { height: 6px; }
.progress-fill { position: absolute; left: 0; top: 0; height: 100%; background: #FF0000; border-radius: 4px; transition: width 0.5s linear; }
.progress-thumb { position: absolute; top: 50%; transform: translate(-50%, -50%); width: 12px; height: 12px; background: #fff; border-radius: 50%; opacity: 0; transition: opacity 0.15s; }
.progress-track:hover .progress-thumb { opacity: 1; }

.extras { display: flex; align-items: center; justify-content: flex-end; gap: 12px; }
.ctrl-btn-sm { background: none; border: none; color: #666; cursor: pointer; padding: 4px; display: flex; align-items: center; transition: color 0.15s; }
.ctrl-btn-sm:hover { color: #fff; }
.ctrl-btn-sm .material-symbols-outlined { font-size: 18px; }

.volume-control { display: flex; align-items: center; gap: 6px; }
.volume-slider { -webkit-appearance: none; width: 90px; height: 4px; background: #2a2a2a; border-radius: 4px; cursor: pointer; outline: none; }
.volume-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; background: #fff; border-radius: 50%; cursor: pointer; }
.volume-slider:hover { background: #3a3a3a; }

@media (max-width: 768px) {
  .player-bar {
    position: fixed;
    bottom: 74px;
    left: 8px;
    right: 8px;
    height: 64px;
    background: rgba(26, 26, 26, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    grid-template-columns: 1fr auto;
    padding: 0 12px;
    margin-bottom: 0;
    z-index: 1001;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  }
  .extras, .volume-control, .progress-row, .ctrl-btn:not(.play-btn) { display: none !important; }
  .control-buttons { gap: 12px; }
  .play-btn .material-symbols-outlined { font-size: 38px; }
  .track-cover, .track-cover-empty { width: 44px; height: 44px; }
  .controls { flex-direction: row; gap: 12px; }
}
</style>
