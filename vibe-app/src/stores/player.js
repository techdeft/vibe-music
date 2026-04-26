import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { Howl } from 'howler'
import { api } from '@/services/api'

export const usePlayerStore = defineStore('player', () => {
  // State
  const queue = ref([])          // Array of track objects
  const currentIndex = ref(-1)   // Index in queue
  const isPlaying = ref(false)
  const isShuffle = ref(false)
  const isRepeat = ref(false)    // 'none' | 'one' | 'all'
  const repeatMode = ref('none')
  const volume = ref(0.8)
  const progress = ref(0)        // 0-1
  const duration = ref(0)
  const currentTime = ref(0)
  const isLoading = ref(false)
  const showVideo = ref(false)
  const isFullPlayerOpen = ref(false)

  let howl = null
  let progressInterval = null
  const trackedStreams = new Set() // Track IDs played in this session to avoid double counting

  // Computed
  const currentTrack = computed(() => queue.value[currentIndex.value] || null)
  const hasNext = computed(() => currentIndex.value < queue.value.length - 1)
  const hasPrev = computed(() => currentIndex.value > 0)

  // Private helpers
  function clearHowl() {
    if (howl) {
      howl.unload()
      howl = null
    }
    clearInterval(progressInterval)
    progress.value = 0
    currentTime.value = 0
    duration.value = 0
  }

  function startProgressTracking() {
    clearInterval(progressInterval)
    progressInterval = setInterval(() => {
      if (howl && howl.playing()) {
        currentTime.value = howl.seek() || 0
        duration.value = howl.duration() || 0
        progress.value = duration.value > 0 ? currentTime.value / duration.value : 0
      }
    }, 500)
  }

  async function recordStream(trackId) {
    if (!trackId || trackedStreams.has(trackId)) return
    try {
      await api.trackStream(trackId)
      trackedStreams.add(trackId)
    } catch (e) {
      console.error('Failed to record stream:', e)
    }
  }

  function loadTrack(track) {
    clearHowl()
    isLoading.value = true

    if (!track?.audio_url) {
      isLoading.value = false
      return
    }

    howl = new Howl({
      src: [track.audio_url],
      html5: true,
      volume: volume.value,
      onload: () => {
        isLoading.value = false
        duration.value = howl.duration()
      },
      onplay: () => {
        isPlaying.value = true
        startProgressTracking()
        recordStream(track.id)
      },
      onpause: () => {
        isPlaying.value = false
        clearInterval(progressInterval)
      },
      onstop: () => {
        isPlaying.value = false
        clearInterval(progressInterval)
      },
      onend: () => {
        isPlaying.value = false
        clearInterval(progressInterval)
        handleTrackEnd()
      },
      onloaderror: () => {
        isLoading.value = false
        isPlaying.value = false
      },
    })
  }

  function handleTrackEnd() {
    if (repeatMode.value === 'one') {
      howl.seek(0)
      howl.play()
      return
    }
    if (hasNext.value) {
      next()
    } else if (repeatMode.value === 'all' && queue.value.length > 0) {
      currentIndex.value = 0
      loadTrack(currentTrack.value)
      howl.play()
    }
  }

  // Actions
  function playTrack(track, trackQueue = null) {
    if (trackQueue) {
      queue.value = trackQueue
      currentIndex.value = trackQueue.findIndex(t => t.id === track.id)
      if (currentIndex.value === -1) {
        queue.value = [track, ...trackQueue]
        currentIndex.value = 0
      }
    } else if (!queue.value.find(t => t.id === track.id)) {
      queue.value = [track]
      currentIndex.value = 0
    } else {
      currentIndex.value = queue.value.findIndex(t => t.id === track.id)
    }

    loadTrack(currentTrack.value)
    howl?.play()
  }

  function togglePlay() {
    if (!howl && currentTrack.value) {
      loadTrack(currentTrack.value)
      howl?.play()
      return
    }
    if (howl?.playing()) {
      howl.pause()
    } else {
      howl?.play()
    }
  }

  function next() {
    if (isShuffle.value && queue.value.length > 1) {
      let randIdx
      do { randIdx = Math.floor(Math.random() * queue.value.length) }
      while (randIdx === currentIndex.value)
      currentIndex.value = randIdx
    } else if (hasNext.value) {
      currentIndex.value++
    } else {
      return
    }
    loadTrack(currentTrack.value)
    howl?.play()
  }

  function prev() {
    // If more than 3 seconds in, restart current track
    if (currentTime.value > 3 && howl) {
      howl.seek(0)
      return
    }
    if (hasPrev.value) {
      currentIndex.value--
      loadTrack(currentTrack.value)
      howl?.play()
    }
  }

  function seek(fraction) {
    if (howl && duration.value > 0) {
      howl.seek(fraction * duration.value)
      progress.value = fraction
    }
  }

  function setVolume(val) {
    volume.value = val
    if (howl) howl.volume(val)
  }

  function toggleShuffle() {
    isShuffle.value = !isShuffle.value
  }

  function toggleRepeat() {
    const modes = ['none', 'all', 'one']
    const idx = modes.indexOf(repeatMode.value)
    repeatMode.value = modes[(idx + 1) % modes.length]
  }

  function addToQueue(track) {
    if (!queue.value.find(t => t.id === track.id)) {
      queue.value.push(track)
    }
  }

  function toggleVideo() {
    showVideo.value = !showVideo.value
  }

  function toggleFullPlayer() {
    isFullPlayerOpen.value = !isFullPlayerOpen.value
  }

  function formatTime(secs) {
    const m = Math.floor(secs / 60)
    const s = Math.floor(secs % 60)
    return `${m}:${s.toString().padStart(2, '0')}`
  }

  return {
    queue, currentIndex, currentTrack, isPlaying, isShuffle, repeatMode,
    volume, progress, duration, currentTime, isLoading, hasNext, hasPrev,
    showVideo, isFullPlayerOpen,
    playTrack, togglePlay, next, prev, seek, setVolume, toggleShuffle,
    toggleRepeat, addToQueue, formatTime, toggleVideo, toggleFullPlayer,
  }
})
