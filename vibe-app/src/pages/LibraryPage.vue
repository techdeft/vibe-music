<template>
  <div class="library-page">
    <div class="page-header">
      <h1 class="page-title">Your Library</h1>
    </div>

    <!-- Playlists section -->
    <div v-if="auth.isLoggedIn" class="library-section">
      <div class="section-header">
        <h2 class="section-title">Playlists</h2>
      </div>
      
      <div v-if="playlistStore.playlists.length" class="playlists-grid">
        <RouterLink 
          v-for="pl in playlistStore.playlists" 
          :key="pl.id" 
          :to="`/playlist/${pl.id}`" 
          class="playlist-card"
        >
          <div class="pl-cover-wrap">
            <img v-if="pl.cover" :src="pl.cover" :alt="pl.name" class="pl-cover" />
            <div v-else class="pl-cover-empty">
              <span class="material-symbols-outlined">playlist_play</span>
            </div>
            <div class="pl-overlay">
              <div class="pl-play-btn">
                <span class="material-symbols-outlined filled">play_arrow</span>
              </div>
            </div>
          </div>
          <p class="pl-name">{{ pl.name }}</p>
          <p class="pl-meta">By {{ pl.author_name }}</p>
        </RouterLink>
      </div>
      
      <div v-else class="empty-playlists">
        <p>No playlists created yet.</p>
      </div>
    </div>

    <!-- Queue section -->
    <div class="library-section">
      <h2 class="section-title">Current Queue</h2>
      <div v-if="player.queue.length" class="queue-list">
        <div class="tracks-header-row">
          <span class="th th-num">#</span>
          <span class="th">Title</span>
          <span class="th">Album</span>
          <span class="th th-streams">Plays</span>
          <span class="th th-dur"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
          <span class="th th-action"></span>
        </div>
        <div class="divider"></div>
        <TrackRow
          v-for="(track, i) in player.queue"
          :key="track.id"
          :track="track"
          :number="i + 1"
          :queue="player.queue"
          show-cover
        />
      </div>

      <div v-else class="empty-state">
        <span class="material-symbols-outlined" style="font-size:64px;color:#333">library_music</span>
        <h2>Your queue is empty</h2>
        <p>Start playing music from Discovery to build your queue here.</p>
        <RouterLink to="/" class="btn-discover">
          <span class="material-symbols-outlined">explore</span> Go to Discovery
        </RouterLink>
      </div>
    </div>

    <!-- Auth prompt for non-logged in users -->
    <div v-if="!auth.isLoggedIn" class="auth-upsell">
      <div class="upsell-content">
        <h3>Save everything you love</h3>
        <p>Create playlists, follow artists, and more by logging in.</p>
        <div class="upsell-actions">
          <RouterLink to="/login" class="btn-login">Log In</RouterLink>
          <RouterLink to="/register" class="btn-signup">Sign Up Free</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'
import { usePlaylistStore } from '@/stores/playlists'
import TrackRow from '@/components/TrackRow.vue'

const player = usePlayerStore()
const auth = useAuthStore()
const playlistStore = usePlaylistStore()
</script>

<style scoped>
.library-page { padding: 32px; min-height: 100%; }

.page-header { margin-bottom: 40px; }

.page-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 32px;
  font-weight: 900;
  color: #fff;
}

.library-section { margin-bottom: 48px; }

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.section-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #fff;
}

/* Playlists Grid */
.playlists-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 24px;
}

.playlist-card {
  text-decoration: none;
  background: #181818;
  padding: 16px;
  border-radius: 8px;
  transition: background 0.3s;
}

.playlist-card:hover { background: #282828; }

.pl-cover-wrap {
  position: relative;
  aspect-ratio: 1;
  border-radius: 4px;
  overflow: hidden;
  background: #2a2a2a;
  margin-bottom: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.pl-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.pl-cover-empty {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
}

.pl-cover-empty .material-symbols-outlined { font-size: 64px; }

.pl-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.playlist-card:hover .pl-overlay { opacity: 1; }

.pl-play-btn {
  width: 48px;
  height: 48px;
  background: #FF0000;
  border-radius: 50%;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(0,0,0,0.4);
  transform: translateY(8px);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.playlist-card:hover .pl-play-btn { transform: translateY(0); }
.pl-play-btn .material-symbols-outlined { font-size: 28px; }

.pl-name {
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pl-meta {
  font-size: 12px;
  color: #888;
}

.empty-playlists {
  padding: 40px;
  background: #121212;
  border-radius: 8px;
  text-align: center;
  color: #666;
}

/* Queue List */
.tracks-header-row {
  display: grid;
  grid-template-columns: 48px 1fr 1fr 100px 60px 110px;
  padding: 0 16px;
  margin-bottom: 6px;
}

.th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
.th-num { text-align: center; }
.th-streams { text-align: right; padding-right: 24px; }
.th-dur { text-align: right; }

.divider { height: 1px; background: #1A1A1A; margin-bottom: 8px; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 80px 0;
  text-align: center;
}

.empty-state h2 { font-size: 22px; color: #555; }
.empty-state p { color: #444; max-width: 340px; font-size: 14px; }

.btn-discover {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #FF0000;
  color: #fff;
  text-decoration: none;
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 14px;
  margin-top: 8px;
  transition: all 0.15s;
}

.btn-discover:hover { background: #cc0000; transform: scale(1.04); }

/* Auth Upsell */
.auth-upsell {
  margin-top: 60px;
  background: linear-gradient(90deg, #181818 0%, #282828 100%);
  border-radius: 12px;
  padding: 48px;
  text-align: center;
}

.upsell-content h3 {
  font-family: 'Spline Sans', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 12px;
}

.upsell-content p {
  color: #aaa;
  margin-bottom: 32px;
}

.upsell-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
}

.btn-login {
  background: #fff;
  color: #000;
  text-decoration: none;
  font-weight: 700;
  padding: 14px 40px;
  border-radius: 30px;
  transition: transform 0.15s;
}

.btn-signup {
  background: transparent;
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  padding: 14px 40px;
  border: 1px solid #555;
  border-radius: 30px;
  transition: all 0.15s;
}

.btn-login:hover, .btn-signup:hover { transform: scale(1.04); }
.btn-signup:hover { border-color: #fff; }
</style>
