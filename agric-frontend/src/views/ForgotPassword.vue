<template>
  <div class="reset-page">
    <div class="reset-card">
      <h2>Reset Password</h2>
      <p>Enter your new password below.</p>

      <form @submit.prevent="resetPassword">
        <input v-model="token" type="hidden" />
        <input v-model="email" type="email" placeholder="Email" required readonly />
        <input v-model="password" type="password" placeholder="New password" required />
        <input v-model="password_confirmation" type="password" placeholder="Confirm password" required />

        <p v-if="error" class="error">{{ error }}</p>
        <p v-if="success" class="success">{{ success }}</p>

        <button type="submit" :disabled="loading">Reset Password</button>
        <router-link to="/login" class="back-link">Back to Login</router-link>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      token: this.$route.query.token,
      email: this.$route.query.email,
      password: '',
      password_confirmation: '',
      error: null,
      success: null,
      loading: false,
    };
  },
  mounted() {
    if (!this.token || !this.email) {
      this.error = 'Invalid or missing reset link.';
    }
  },
  methods: {
    async resetPassword() {
      if (this.password !== this.password_confirmation) {
        this.error = 'Passwords do not match.';
        return;
      }

      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        await axios.post('http://127.0.0.1:8000/api/password/reset', {
          token: this.token,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });

        this.success = 'Your password has been reset! Redirecting to login...';
        setTimeout(() => {
          this.$router.push('/login');
        }, 2000);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to reset password.';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.reset-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #f5f5f5;
}

.reset-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.reset-card h2 {
  margin-bottom: 0.5rem;
}

.reset-card p {
  color: #666;
  margin-bottom: 1.5rem;
}

input {
  width: 100%;
  padding: 10px;
  margin-bottom: 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
}

button {
  width: 100%;
  padding: 10px;
  background: #0da57a;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.error {
  color: red;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.success {
  color: green;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.back-link {
  display: block;
  margin-top: 1rem;
  text-align: center;
  color: #0da57a;
  text-decoration: none;
}
</style>