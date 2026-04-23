<template>
  <div class="library-page">
    <div class="page-header">
      <h1 class="page-title">Your Library</h1>
    </div>

    <div v-if="player.queue.length" class="queue-section">
      <h2 class="section-title">Current Queue</h2>
      <div class="tracks-header-row">
        <span class="th th-num">#</span>
        <span class="th">Title</span>
        <span class="th">Album</span>
        <span class="th th-dur"><span class="material-symbols-outlined" style="font-size:16px">schedule</span></span>
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
      <h2>Your library is empty</h2>
      <p>Start playing music from Discovery to build your queue here.</p>
      <RouterLink to="/" class="btn-discover">
        <span class="material-symbols-outlined">explore</span> Go to Discovery
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { usePlayerStore } from '@/stores/player'
import TrackRow from '@/components/TrackRow.vue'

const player = usePlayerStore()
</script>

<style scoped>
.library-page { padding: 32px; min-height: 100%; }

.page-header { margin-bottom: 32px; }

.page-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 32px;
  font-weight: 900;
  color: #fff;
}

.section-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 14px;
}

.tracks-header-row {
  display: grid;
  grid-template-columns: 48px 1fr 1fr 60px;
  padding: 0 16px;
  margin-bottom: 6px;
}

.th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
.th-num { text-align: center; }
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
.btn-discover .material-symbols-outlined { font-size: 18px; }
</style>
