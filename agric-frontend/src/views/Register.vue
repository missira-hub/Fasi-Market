<template>
  <div class="register-page">
    <div class="background-blur"></div>
    <div class="overlay">
      <div class="auth-form">
        <h2>Create Account</h2>

        <div v-if="errors.length" class="error-box">
          <ul>
            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
          </ul>
        </div>

        <form @submit.prevent="register">
          <input v-model="name" type="text" placeholder="Name" required />
          <input v-model="email" type="email" placeholder="Email" required />
          
          <!-- Password -->
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

          <!-- Confirm Password -->
          <div class="password-input-wrapper">
            <input
              v-model="password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Confirm Password"
              required
            />
            <span class="toggle-password" @click="toggleConfirmPasswordVisibility">
              {{ showConfirmPassword ? '🙈' : '👁️' }}
            </span>
          </div>

          <button type="submit">Register</button>
        </form>

        <p class="switch-link">
          Already have an account?
          <router-link to="/login">Login here</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      errors: [],
      showPassword: false,
      showConfirmPassword: false,
    };
  },
  methods: {
    async register() {
      this.errors = [];

      try {
        const response = await axios.post('http://127.0.0.1:8000/api/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });

        localStorage.setItem('token', response.data.token);
        this.$router.push('/login');
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = Object.values(error.response.data.errors).flat();
        } else if (error.response?.data?.message) {
          this.errors = [error.response.data.message];
        } else {
          this.errors = ['Registration failed due to an unknown error.'];
        }
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    toggleConfirmPasswordVisibility() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },
  },
};
</script>

<style scoped>
.register-page {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
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
  filter: blur(1.5px);
  transform: scale(1.2); /* Fixed: was '1.2px', should be unitless or % */
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

.auth-form {
  background: rgb(250, 249, 249);
  padding: 2.5rem;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  color: rgb(13, 165, 112);
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.56);
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

input,
button {
  width: 100%;
  box-sizing: border-box;
  border-radius: 5px;
  font-size: 1rem;
  padding: 12px;
  border: 1px solid #ccc;
  margin: 10px 0;
}

button {
  background: rgb(13, 165, 122);
  color: white;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: 0.3s;
  border-radius: 6px;
}

button:hover {
  background: #369f72;
}

.switch-link {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: rgb(14, 14, 14);
}

.switch-link a {
  color: rgb(13, 165, 122);
  text-decoration: none;
}

.error-box {
  background-color: #ffe5e5;
  color: rgb(0, 0, 0);
  border-radius: 5px;
  padding: 10px;
  margin-bottom: 1rem;
}

/* Password toggle styles */
.password-input-wrapper {
  position: relative;
  margin: 10px 0;
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
</style> 