<template>
  <div 
    class="track-card" 
    :class="{ playing: isCurrentTrack }" 
    @dblclick="play" 
  >
    <div class="cover-wrap">
      <img :src="track.cover || ''" class="cover" @error="$event.target.style.display='none'" />
      <div class="overlay" :class="{ 'always-show': isCurrentTrack }">
        <button class="play-btn" @click.stop="play">
          <span class="material-symbols-outlined filled">
            {{ isCurrentTrack && player.isPlaying ? 'pause' : 'play_arrow' }}
          </span>
        </button>
      </div>
    </div>
    <div class="info">
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
</template>

<script setup>
import { computed } from 'vue'
import { usePlayerStore } from '@/stores/player'

const props = defineProps({
  track: { type: Object, required: true },
  queue: { type: Array, default: null }
})

const player = usePlayerStore()

const isCurrentTrack = computed(() => player.currentTrack?.id === props.track.id)

function play() {
  if (isCurrentTrack.value) {
    player.togglePlay()
  } else {
    player.playTrack(props.track, props.queue)
  }
}
</script>

<style scoped>
.track-card {
  width: 160px;
  flex-shrink: 0;
  cursor: pointer;
  padding: 16px;
  border-radius: 8px;
  transition: background 0.3s ease;
  background: #181818;
}

.track-card:hover {
  background: #282828;
}

.cover-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 1;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 16px;
  background: #2a2a2a;
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.track-card:hover .cover {
  transform: scale(1.05);
}

.overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 12px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.track-card:hover .overlay, .overlay.always-show {
  opacity: 1;
}

.play-btn {
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
  box-shadow: 0 8px 16px rgba(0,0,0,0.4);
  transform: translateY(12px);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.track-card:hover .play-btn, .overlay.always-show .play-btn {
  transform: translateY(0);
  opacity: 1;
}

.play-btn:hover {
  transform: translateY(0) scale(1.08) !important;
  background: #cc0000;
}

.play-btn .material-symbols-outlined {
  font-size: 28px;
}

.info {
  display: flex;
  flex-direction: column;
}

.title {
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 6px;
}

.text-red {
  color: #FF0000 !important;
}

.subtitle {
  font-size: 13px;
  color: #a0a0a0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sub-link {
  color: inherit;
  text-decoration: none;
}

.sub-link:hover {
  text-decoration: underline;
  color: #fff;
}
</style>
