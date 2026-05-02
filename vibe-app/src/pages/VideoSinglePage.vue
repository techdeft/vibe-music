<template>
  <div class="video-page-wrapper">
    <!-- Immersive Background -->
    <div class="video-ambient-bg" v-if="video?.cover" :style="{ backgroundImage: `url(${video.cover})` }"></div>
    <div class="ambient-overlay"></div>

    <div class="page-container">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading experience...</p>
      </div>
      
      <div v-else-if="error" class="error-state">
        <span class="material-symbols-outlined">error</span>
        <p>{{ error }}</p>
        <RouterLink to="/videos" class="back-btn">Return to Videos</RouterLink>
      </div>
      
      <div v-else-if="video" class="video-content">
        <!-- Top Navigation -->
        <nav class="video-nav">
          <RouterLink to="/videos" class="back-pill">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Back to Videos</span>
          </RouterLink>
        </nav>

        <!-- Cinematic Player Area -->
        <div class="player-stage">
          <div class="player-glow-wrap">
            <div class="player-container">
              <iframe 
                v-if="isYoutube" 
                :src="youtubeEmbedUrl" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen
                class="video-player">
              </iframe>
              <video 
                v-else 
                :src="video.video_url" 
                controls 
                class="video-player"
                :poster="video.cover">
              </video>
            </div>
            <!-- Decorative Glow based on cover -->
            <div class="player-glow" v-if="video.cover" :style="{ backgroundImage: `url(${video.cover})` }"></div>
          </div>
        </div>

        <!-- Video Info -->
        <div class="video-details-section">
          <div class="video-header-row">
            <h1 class="video-title">{{ video.title }}</h1>
            <div class="video-actions" v-if="video.duration">
              <span class="duration-badge"><span class="material-symbols-outlined">schedule</span> {{ video.duration }}</span>
            </div>
          </div>
          
          <div class="video-meta">
            <div class="meta-item">
              <span class="material-symbols-outlined">smart_display</span>
              <span>Official Music Video</span>
            </div>
            <div class="meta-item" v-if="video.featured">
              <span class="material-symbols-outlined" style="color: #FF0000;">star</span>
              <span style="color: #FF0000;">Featured</span>
            </div>
          </div>

          <div v-if="video.description" class="video-description-box">
            <div class="desc-content" v-html="video.description"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/services/api'

const route = useRoute()
const video = ref(null)
const loading = ref(true)
const error = ref('')

const isYoutube = computed(() => {
  if (!video.value?.video_url) return false
  const url = video.value.video_url.toLowerCase()
  return url.includes('youtube.com') || url.includes('youtu.be')
})

const youtubeEmbedUrl = computed(() => {
  if (!video.value?.video_url) return ''
  let videoId = ''
  try {
    const url = new URL(video.value.video_url)
    if (url.hostname.includes('youtu.be')) {
      videoId = url.pathname.slice(1)
    } else {
      videoId = url.searchParams.get('v')
    }
  } catch (e) {
    // Ignore invalid URL errors
  }
  return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1` : ''
})

async function loadVideo() {
  loading.value = true
  error.value = ''
  try {
    video.value = await api.video(route.params.id)
  } catch (err) {
    error.value = err.message || 'Failed to load video'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadVideo()
})
</script>

<style scoped>
.video-page-wrapper {
  position: relative;
  min-height: 100%;
  width: 100%;
  overflow-x: hidden;
}

/* Ambient Background */
.video-ambient-bg {
  position: absolute;
  top: -10%;
  left: -10%;
  width: 120%;
  height: 80vh;
  background-size: cover;
  background-position: center;
  filter: blur(100px) saturate(1.5) opacity(0.3);
  z-index: 0;
  pointer-events: none;
}

.ambient-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(18,18,18,0.2) 0%, rgba(18,18,18,1) 60vh);
  z-index: 1;
  pointer-events: none;
}

.page-container {
  position: relative;
  z-index: 2;
  padding: 30px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Loading & Error */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100px 0;
  color: #888;
  text-align: center;
}

.loading-state .spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255,255,255,0.1);
  border-top-color: #FF0000;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin { 100% { transform: rotate(360deg); } }

/* Navigation */
.video-nav {
  margin-bottom: 30px;
}

.back-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.05);
  color: #fff;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.05);
  transition: all 0.3s ease;
}

.back-pill:hover {
  background: rgba(255,255,255,0.1);
  transform: translateX(-4px);
  border-color: rgba(255,255,255,0.15);
}

.back-pill .material-symbols-outlined {
  font-size: 20px;
}

/* Cinematic Player Stage */
.player-stage {
  width: 100%;
  margin-bottom: 40px;
  display: flex;
  justify-content: center;
}

.player-glow-wrap {
  position: relative;
  width: 100%;
  max-width: 1000px;
}

.player-glow {
  position: absolute;
  top: 10%;
  left: 5%;
  width: 90%;
  height: 80%;
  background-size: cover;
  background-position: center;
  filter: blur(50px) saturate(2);
  opacity: 0.5;
  z-index: -1;
  transition: opacity 0.5s ease;
}

.player-container {
  width: 100%;
  aspect-ratio: 16 / 9;
  background: #000;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0,0,0,0.6);
  border: 1px solid rgba(255,255,255,0.05);
  position: relative;
  z-index: 2;
}

.video-player {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

/* Video Details */
.video-details-section {
  max-width: 1000px;
  margin: 0 auto;
}

.video-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  margin-bottom: 12px;
}

.video-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 38px;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.5px;
  line-height: 1.2;
  margin: 0;
}

.duration-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255,0,0,0.1);
  color: #FF0000;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
}

.duration-badge .material-symbols-outlined {
  font-size: 18px;
}

.video-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #888;
  font-size: 14px;
  font-weight: 500;
}

.meta-item .material-symbols-outlined {
  font-size: 18px;
}

.video-description-box {
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 16px;
  padding: 24px;
}

.desc-content {
  color: #bbb;
  line-height: 1.7;
  font-size: 15px;
  white-space: pre-wrap;
}

@media (max-width: 768px) {
  .page-container { padding: 20px; }
  .video-header-row { flex-direction: column; }
  .video-title { font-size: 28px; }
  .player-container { border-radius: 8px; }
  .video-description-box { padding: 16px; }
}
</style>
