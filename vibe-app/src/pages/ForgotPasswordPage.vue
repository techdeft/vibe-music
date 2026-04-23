<template>
  <div class="forgot-password-page">
    <h2 class="auth-title">Password Reset</h2>
    <p class="auth-desc">Enter your email address and we'll send you a link to reset your password.</p>
    
    <form v-if="!success" @submit.prevent="handleSubmit" class="auth-form">
      <div v-if="auth.error" class="error-alert">
        {{ auth.error }}
      </div>

      <div class="form-group">
        <label for="email">Email address</label>
        <input 
          type="email" 
          id="email" 
          v-model="email" 
          placeholder="Enter your email" 
          required
          autofocus
        />
      </div>

      <button type="submit" class="btn-submit" :disabled="auth.loading">
        <span v-if="auth.loading" class="spinner-sm"></span>
        <span v-else>Send Link</span>
      </button>
    </form>

    <div v-else class="success-message">
      <div class="success-icon">
        <span class="material-symbols-outlined">mark_email_read</span>
      </div>
      <h3>Email Sent</h3>
      <p>If an account exists for {{ email }}, you will receive an email with instructions shortly.</p>
      <RouterLink to="/login" class="btn-submit">Back to Login</RouterLink>
    </div>

    <div class="auth-footer" v-if="!success">
      <p><RouterLink to="/login">Back to Login</RouterLink></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const email = ref('')
const success = ref(false)

async function handleSubmit() {
  const res = await auth.forgotPassword(email.value)
  if (res) {
    success.value = true
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
  margin-bottom: 12px;
}

.auth-desc {
  font-size: 14px;
  color: #888;
  text-align: center;
  margin-bottom: 32px;
  line-height: 1.5;
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
  text-decoration: none;
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

.success-message {
  text-align: center;
}

.success-icon {
  width: 64px;
  height: 64px;
  background: rgba(255, 0, 0, 0.1);
  color: #FF0000;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
}

.success-icon .material-symbols-outlined {
  font-size: 32px;
}

.success-message h3 {
  font-family: 'Spline Sans', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 12px;
}

.success-message p {
  font-size: 14px;
  color: #888;
  margin-bottom: 32px;
  line-height: 1.6;
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
