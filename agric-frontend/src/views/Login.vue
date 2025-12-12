<template>
  <div class="login-page">
    <div class="background-blur"></div>
    <div class="overlay">
      <div class="login-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to your account</p>

        <form @submit.prevent="login">
          <input v-model="email" type="email" placeholder="Email address" required />
          
          <div class="password-input-wrapper">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Password"
              required
            />
            <span class="toggle-password" @click="togglePasswordVisibility">
              {{ showPassword ? '🙈' : '👁️' }}
            </span>
          </div>

          <!-- Error Message -->
          <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

          <button type="submit">Login</button>

          <!-- Forgot Password -->
          <p class="forgot-password">
            <a href="#" @click.prevent="handleForgotPassword">Forgot password?</a>
          </p>

          <p class="switch-link">
            Don’t have an account?
            <router-link to="/register">Register here</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: '',
      errorMessage: '',
      showPassword: false, // 👈 controls password visibility
    };
  },
  methods: {
    async login() {
      try {
        this.errorMessage = '';

        const res = await axios.post('http://127.0.0.1:8000/api/login', {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem('token', res.data.token);
        localStorage.setItem('role', res.data.role);

        const role = res.data.role;
        if (role === 'admin') {
          this.$router.push('/admin/dashboard');
        } else if (role === 'farmer') {
          this.$router.push('/farmer/dashboard');
        } else {
          this.$router.push('/consumer/dashboard');
        }
      } catch (error) {
        if (error.response && error.response.status === 401) {
          this.errorMessage = 'Invalid email or password.';
        } else {
          this.errorMessage = 'Something went wrong. Please try again later.';
        }
      }
    },
    async handleForgotPassword() {
      if (!this.email) {
        this.errorMessage = 'Please enter your email to reset your password.';
        return;
      }

      try {
        this.errorMessage = '';
        await axios.post('http://127.0.0.1:8000/api/password/forgot', {
          email: this.email,
        });
        this.errorMessage = 'Password recovery email sent.';
      } catch (err) {
        this.errorMessage =
          'Failed to send recovery email. Make sure the email is correct.';
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
  },
};
</script>

<style scoped>
.error-msg {
  color: red;
  font-size: 0.9rem;
  margin-top: -10px;
  margin-bottom: -5px;
  text-align: left;
}

.forgot-password {
  text-align: right;
  font-size: 0.85rem;
  margin-top: -0.5rem;
}

.forgot-password a {
  color: #0da57a;
  text-decoration: underline;
  cursor: pointer;
}

.login-page {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  z-index: 0;
}

/* Background image with blur */
.background-blur {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: url('@/assets/hero.png') no-repeat center center;
  background-size: cover;
  filter: blur(1.2px);
  transform: scale(1.05);
  z-index: -2;
}

.overlay {
  background-color: rgba(0, 0, 0, 0.52);
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 2.5rem 2rem;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  color: #1e1e1e;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.96);
  text-align: center;
  z-index: 2;
}

.login-card h2 {
  margin-bottom: 0.5rem;
  font-size: 1.8rem;
}

.subtitle {
  font-size: 0.95rem;
  color: #555;
  margin-bottom: 1.5rem;
}

.login-card form {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: 100%;
  gap: 1rem;
}

input,
button {
  width: 100%;
  box-sizing: border-box;
}

input {
  padding: 12px;
  background: #f2f2f2;
  border: 1px solid #ccc;
  border-radius: 6px;
  color: #333;
  font-size: 1rem;
}

/* Password visibility toggle styles */
.password-input-wrapper {
  position: relative;
}

.password-input-wrapper input {
  padding-right: 40px;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 1.1rem;
  user-select: none;
  color: #777;
}

button {
  padding: 12px;
  background: rgb(13, 165, 122);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

button:hover {
  background: #36996b;
}

.switch-link {
  margin-top: 1rem;
  font-size: 0.9rem;
}

.switch-link a {
  color: rgb(13, 165, 122);
  text-decoration: none;
}
</style>