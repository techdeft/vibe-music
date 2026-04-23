<template>
  <div class="track-row" :class="{ playing: isCurrentTrack, active }" @dblclick="play" @mouseenter="hovering = true" @mouseleave="hovering = false">
    <!-- Number / Play indicator -->
    <div class="col-num">
      <span v-if="!hovering && !isCurrentTrack" class="track-num">{{ number }}</span>
      <button v-else class="play-icon-btn" @click="play">
        <span class="material-symbols-outlined filled">{{ isCurrentTrack && player.isPlaying ? 'pause' : 'play_arrow' }}</span>
      </button>
      <span v-if="isCurrentTrack && !hovering" class="playing-indicator">
        <span class="bar" v-for="i in 3" :key="i"></span>
      </span>
    </div>

    <!-- Cover + Title -->
    <div class="col-title">
      <img v-if="showCover" :src="track.cover || ''" class="mini-cover" @error="$event.target.style.display='none'" />
      <div>
        <p class="title" :class="{ 'text-red': isCurrentTrack }">{{ track.title }}</p>
        <p class="subtitle">
          <RouterLink v-if="track.artist_id" :to="`/artist/${track.artist_id}`" class="sub-link" @click.stop>
            {{ track.artist_name }}
          </RouterLink>
          <span v-else>{{ track.artist_name || 'Unknown' }}</span>
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

    <!-- Duration -->
    <div class="col-dur">{{ track.duration || '—' }}</div>

    <!-- Actions -->
    <div class="col-actions">
      <button v-if="auth.isLoggedIn" @click.stop="showPlaylistModal = true" class="action-btn" title="Add to playlist">
        <span class="material-symbols-outlined">playlist_add</span>
      </button>
    </div>

    <AddToPlaylistModal 
      :is-open="showPlaylistModal" 
      :track-id="track.id" 
      @close="showPlaylistModal = false" 
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'
import AddToPlaylistModal from './AddToPlaylistModal.vue'

const props = defineProps({
  track: { type: Object, required: true },
  number: { type: Number, default: 1 },
  queue: { type: Array, default: null },
  showCover: { type: Boolean, default: false },
  showAlbum: { type: Boolean, default: true },
})

const player = usePlayerStore()
const auth = useAuthStore()
const hovering = ref(false)
const showPlaylistModal = ref(false)

const isCurrentTrack = computed(() => player.currentTrack?.id === props.track.id)
const active = computed(() => isCurrentTrack.value)

function play() {
  if (isCurrentTrack.value) {
    player.togglePlay()
  } else {
    player.playTrack(props.track, props.queue)
  }
}
</script>

<style scoped>
.track-row {
  display: grid;
  grid-template-columns: 48px 1fr 1fr 60px 48px;
  align-items: center;
  height: 56px;
  padding: 0 16px;
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
}

.mini-cover {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  object-fit: cover;
  flex-shrink: 0;
}

.title {
  font-size: 14px;
  font-weight: 500;
  color: #e5e2e1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.text-red { color: #FF0000 !important; }

.subtitle {
  font-size: 12px;
  color: #888;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.col-album {
  font-size: 13px;
  color: #666;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 12px;
}

.col-dur {
  font-size: 13px;
  color: #666;
  text-align: right;
}

.col-actions {
  display: flex;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.15s;
}

.track-row:hover .col-actions {
  opacity: 1;
}

.action-btn {
  background: none;
  border: none;
  color: #888;
  cursor: pointer;
  padding: 8px;
  display: flex;
  align-items: center;
}

.action-btn:hover { color: #fff; }

.sub-link {
  color: inherit;
  text-decoration: none;
}
.sub-link:hover { color: #FF0000; text-decoration: underline; }

.muted { color: #444; }
</style>
