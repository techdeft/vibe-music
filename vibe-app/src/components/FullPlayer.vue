<template>
  <Transition name="slide-up">
    <div v-if="player.isFullPlayerOpen" class="full-player">
      <!-- Background Video/Canvas -->
      <div class="background-canvas">
        <iframe
          v-if="youtubeId"
          :src="`https://www.youtube.com/embed/${youtubeId}?autoplay=1&mute=1&controls=0&loop=1&playlist=${youtubeId}`"
          frameborder="0"
          allow="autoplay; encrypted-media"
          class="canvas-video"
        ></iframe>
        <video
          v-else-if="player.currentTrack?.video_url"
          :src="player.currentTrack.video_url"
          autoplay
          loop
          muted
          class="canvas-video"
        ></video>
        <div v-else class="canvas-image" :style="{ backgroundImage: `url(${player.currentTrack?.cover})` }"></div>
        <div class="overlay-gradient"></div>
      </div>

      <!-- Content -->
      <div class="full-player-content">
        <header class="full-player-header">
          <button @click="player.toggleFullPlayer()" class="close-btn">
            <span class="material-symbols-outlined">expand_more</span>
          </button>
          <div class="header-meta">
            <span class="playing-from">PLAYING FROM</span>
            <span class="album-name">{{ player.currentTrack?.album_title || 'Singles' }}</span>
          </div>
          <button class="more-btn">
            <span class="material-symbols-outlined">more_vert</span>
          </button>
        </header>

        <div class="main-view">
          <div class="track-display">
            <div v-if="!player.currentTrack?.video_url" class="track-art">
               <img :src="player.currentTrack?.cover" :alt="player.currentTrack?.title" />
            </div>
            <!-- If we want interactive video here instead of background, we could swap it -->
          </div>

          <div class="track-info-large">
            <div class="title-wrap">
              <h2>{{ player.currentTrack?.title }}</h2>
              <p>{{ player.currentTrack?.artists?.map(a => a.name).join(', ') || player.currentTrack?.artist_name }}</p>
            </div>
            <button class="like-btn" :class="{ active: isLiked }" @click="auth.toggleLike(player.currentTrack?.id)">
              <span class="material-symbols-outlined">{{ isLiked ? 'favorite' : 'favorite' }}</span>
            </button>
          </div>

          <!-- Controls -->
          <div class="playback-controls-large">
            <div class="progress-section">
              <div class="progress-bar-wrap" @click="handleSeek">
                <div class="progress-bar-bg"></div>
                <div class="progress-bar-fill" :style="{ width: `${player.progress * 100}%` }"></div>
              </div>
              <div class="time-row">
                <span>{{ player.formatTime(player.currentTime) }}</span>
                <span>{{ player.formatTime(player.duration) }}</span>
              </div>
            </div>

            <div class="buttons-row">
              <button class="side-btn" :class="{ active: player.isShuffle }" @click="player.toggleShuffle()">
                <span class="material-symbols-outlined">shuffle</span>
              </button>
              <button class="nav-btn" @click="player.prev()">
                <span class="material-symbols-outlined">skip_previous</span>
              </button>
              <button class="play-btn-large" @click="player.togglePlay()">
                <span class="material-symbols-outlined">{{ player.isPlaying ? 'pause_circle' : 'play_circle' }}</span>
              </button>
              <button class="nav-btn" @click="player.next()">
                <span class="material-symbols-outlined">skip_next</span>
              </button>
              <button class="side-btn" :class="{ active: player.repeatMode !== 'none' }" @click="player.toggleRepeat()">
                <span class="material-symbols-outlined">{{ player.repeatMode === 'one' ? 'repeat_one' : 'repeat' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'

const player = usePlayerStore()
const auth = useAuthStore()

const isLiked = computed(() => {
  if (!player.currentTrack) return false
  return auth.isTrackLiked(player.currentTrack.id)
})

const youtubeId = computed(() => {
  const url = player.currentTrack?.video_url
  if (!url) return null
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/
  const match = url.match(regExp)
  return (match && match[2].length === 11) ? match[2] : null
})

function handleSeek(e) {
  const rect = e.currentTarget.getBoundingClientRect()
  const fraction = (e.clientX - rect.left) / rect.width
  player.seek(Math.max(0, Math.min(1, fraction)))
}
</script>

<style scoped>
.full-player {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #000;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.background-canvas {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.canvas-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.6;
  filter: scale(1.1); /* Prevent black edges */
}

.canvas-image {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  filter: blur(40px) brightness(0.4);
  transform: scale(1.2);
}

.overlay-gradient {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.8) 100%);
}

.full-player-content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 20px 24px;
}

.full-player-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 64px;
}

.close-btn, .more-btn {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
}

.header-meta {
  text-align: center;
}

.playing-from {
  display: block;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1px;
  color: rgba(255,255,255,0.7);
}

.album-name {
  font-size: 13px;
  font-weight: 600;
}

.main-view {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 500px;
  margin: 0 auto;
  width: 100%;
}

.track-display {
  aspect-ratio: 1;
  width: 100%;
  margin-bottom: 40px;
}

.track-art img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}

.track-info-large {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;
}

.title-wrap h2 {
  font-size: 24px;
  font-weight: 700;
  margin: 0;
}

.title-wrap p {
  font-size: 16px;
  color: rgba(255,255,255,0.7);
  margin: 4px 0 0;
}

.like-btn {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
}

.like-btn.active {
  color: #FF0000;
  font-variation-settings: 'FILL' 1;
}

.progress-section {
  margin-bottom: 24px;
}

.progress-bar-wrap {
  height: 4px;
  background: rgba(255,255,255,0.2);
  border-radius: 2px;
  position: relative;
  cursor: pointer;
}

.progress-bar-fill {
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  background: #fff;
  border-radius: 2px;
}

.time-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: rgba(255,255,255,0.6);
  margin-top: 8px;
}

.buttons-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.side-btn {
  background: none;
  border: none;
  color: rgba(255,255,255,0.5);
  cursor: pointer;
}

.side-btn.active { color: #FF0000; }

.nav-btn {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
}

.nav-btn .material-symbols-outlined { font-size: 36px; }

.play-btn-large {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
}

.play-btn-large .material-symbols-outlined {
  font-size: 72px;
}

/* Animations */
.slide-up-enter-active, .slide-up-leave-active {
  transition: transform 0.4s cubic-bezier(0.3, 0, 0.2, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(100%);
}
</style>
