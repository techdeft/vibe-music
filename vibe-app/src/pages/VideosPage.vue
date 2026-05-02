<template>
  <div class="page-container">
    <header class="page-header">
      <h1 class="page-title">Videos</h1>
      <p class="page-subtitle">Watch the latest music videos and exclusive content.</p>
    </header>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading videos...</p>
    </div>
    
    <div v-else-if="error" class="error-state">
      <span class="material-symbols-outlined">error</span>
      <p>{{ error }}</p>
      <button @click="loadVideos" class="retry-btn">Try Again</button>
    </div>
    
    <div v-else-if="videos.length === 0" class="empty-state">
      <span class="material-symbols-outlined">videocam_off</span>
      <p>No videos available yet.</p>
    </div>
    
    <div v-else class="video-grid">
      <RouterLink 
        v-for="video in videos" 
        :key="video.id" 
        :to="`/video/${video.id}`" 
        class="video-card"
      >
        <div class="video-thumbnail-wrap">
          <img :src="video.cover || 'https://via.placeholder.com/640x360?text=No+Thumbnail'" :alt="video.title" class="video-thumbnail" />
          <div class="video-overlay">
            <span class="material-symbols-outlined play-icon">play_circle</span>
          </div>
          <div v-if="video.duration" class="video-duration">{{ video.duration }}</div>
        </div>
        <div class="video-info">
          <h3 class="video-title">{{ video.title }}</h3>
        </div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'

const videos = ref([])
const loading = ref(true)
const error = ref('')

async function loadVideos() {
  loading.value = true
  error.value = ''
  try {
    videos.value = await api.videos()
  } catch (err) {
    error.value = err.message || 'Failed to load videos'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadVideos()
})
</script>

<style scoped>
.page-container {
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.page-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 42px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 8px;
}

.page-subtitle {
  color: #888;
  font-size: 16px;
}

.loading-state, .error-state, .empty-state {
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

.error-state .material-symbols-outlined,
.empty-state .material-symbols-outlined {
  font-size: 48px;
  margin-bottom: 16px;
  color: #444;
}

.retry-btn {
  margin-top: 16px;
  background: #FF0000;
  color: #fff;
  border: none;
  padding: 8px 24px;
  border-radius: 20px;
  font-weight: 600;
  cursor: pointer;
}

.video-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.video-card {
  text-decoration: none;
  display: block;
  transition: transform 0.2s;
}

.video-card:hover {
  transform: translateY(-4px);
}

.video-card:hover .video-thumbnail {
  transform: scale(1.05);
}

.video-card:hover .video-overlay {
  opacity: 1;
}

.video-thumbnail-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  border-radius: 12px;
  overflow: hidden;
  background: #1a1a1a;
  margin-bottom: 12px;
}

.video-thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.video-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.play-icon {
  font-size: 64px;
  color: #fff;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));
}

.video-duration {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: rgba(0,0,0,0.8);
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.video-info {
  padding: 0 4px;
}

.video-title {
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 768px) {
  .page-container { padding: 20px; }
  .page-title { font-size: 32px; }
  .video-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
}
</style>
