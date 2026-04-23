<template>
  <div class="auth-layout">
    <div class="auth-container">
      <div class="auth-header">
        <RouterLink to="/" class="auth-brand">{{ playerName }}</RouterLink>
      </div>
      <main class="auth-content">
        <RouterView v-slot="{ Component }">
          <Transition name="fade" mode="out-in">
            <component :is="Component" />
          </Transition>
        </RouterView>
      </main>
    </div>
  </div>
</template>

<script setup>
import { api } from '@/services/api'
const playerName = api.config.playerName || 'VIBE'
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  min-height: 100dvh;
  background-color: #0A0A0A;
  background-image: radial-gradient(circle at 50% 0%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.auth-container {
  width: 100%;
  max-width: 440px;
}

.auth-header {
  text-align: center;
  margin-bottom: 32px;
}

.auth-brand {
  font-family: 'Spline Sans', sans-serif;
  font-size: 42px;
  font-weight: 900;
  font-style: italic;
  color: #FF0000;
  letter-spacing: -2px;
  text-decoration: none;
  line-height: 1;
}

.auth-content {
  background: #121212;
  border: 1px solid #1A1A1A;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}
</style>
