<template>
  <div class="login-page">
    <h2 class="auth-title">Log in to VIBE</h2>
    
    <form @submit.prevent="handleLogin" class="auth-form">
      <div v-if="auth.error" class="error-alert">
        {{ auth.error }}
      </div>

      <div class="form-group">
        <label for="username">Email or username</label>
        <input 
          type="text" 
          id="username" 
          v-model="username" 
          placeholder="Email or username" 
          required
          autofocus
        />
      </div>

      <div class="form-group">
        <div class="label-row">
          <label for="password">Password</label>
          <RouterLink to="/forgot-password" class="forgot-link">Forgot password?</RouterLink>
        </div>
        <div class="password-input-wrap">
          <input 
            :type="showPassword ? 'text' : 'password'" 
            id="password" 
            v-model="password" 
            placeholder="Password" 
            required
          />
          <button type="button" @click="showPassword = !showPassword" class="toggle-password">
            <span class="material-symbols-outlined">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit" :disabled="auth.loading">
        <span v-if="auth.loading" class="spinner-sm"></span>
        <span v-else>Log In</span>
      </button>
    </form>

    <div class="auth-footer">
      <p>Don't have an account? <RouterLink to="/register">Sign up for VIBE</RouterLink></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const username = ref('')
const password = ref('')
const showPassword = ref(false)

async function handleLogin() {
  const success = await auth.login(username.value, password.value)
  if (success) {
    router.push('/')
  }
}
</script>

<style scoped>
.auth-title {
  font-family: 'Spline Sans', sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #fff;
  text-align: center;
  margin-bottom: 32px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.error-alert {
  background: rgba(255, 0, 0, 0.1);
  border-left: 3px solid #FF0000;
  color: #FF7777;
  padding: 12px 16px;
  font-size: 14px;
  border-radius: 4px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

label {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}

input {
  background: #1A1A1A;
  border: 1px solid #2a2a2a;
  border-radius: 8px;
  padding: 12px 16px;
  color: #fff;
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  transition: all 0.15s;
}

input:focus {
  outline: none;
  border-color: #FF0000;
  box-shadow: 0 0 0 4px rgba(255, 0, 0, 0.1);
}

.password-input-wrap {
  position: relative;
}

.password-input-wrap input {
  width: 100%;
  padding-right: 48px;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #888;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 4px;
}

.toggle-password:hover { color: #fff; }

.forgot-link {
  font-size: 13px;
  color: #888;
  text-decoration: none;
}

.forgot-link:hover { color: #FF0000; text-decoration: underline; }

.btn-submit {
  background: #FF0000;
  color: #fff;
  border: none;
  border-radius: 30px;
  padding: 14px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  margin-top: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-submit:hover {
  background: #cc0000;
  transform: translateY(-1px);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.auth-footer {
  margin-top: 32px;
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid #1A1A1A;
}

.auth-footer p {
  font-size: 14px;
  color: #888;
}

.auth-footer a {
  color: #fff;
  font-weight: 600;
  text-decoration: none;
}

.auth-footer a:hover {
  color: #FF0000;
  text-decoration: underline;
}

.spinner-sm {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
