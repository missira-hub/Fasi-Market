<template>
  <div class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <button class="close-btn" @click="close">×</button>
      <h2>Edit Profile</h2>

      <form @submit.prevent="updateProfile" enctype="multipart/form-data">
        <label>
          Name:
          <input v-model="form.name" type="text" required />
        </label>

        <label>
          Email:
          <input v-model="form.email" type="email" required />
        </label>

        <label>
          New Password:
          <input v-model="form.password" type="password" placeholder="Leave blank to keep current" />
        </label>

        <label>
          Confirm Password:
          <input v-model="form.password_confirmation" type="password" placeholder="Confirm new password" />
        </label>

       <label>
  Change Avatar:
  <input type="file" @change="handleAvatarChange" accept="image/*" />
</label>

<div v-if="imagePreview" class="avatar-preview">
  <img :src="imagePreview" alt="Avatar preview" />
</div>


        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Updating...' : 'Update Profile' }}
        </button>

        <div v-if="successMessage" class="success">{{ successMessage }}</div>
        <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close', 'updated'])

const form = ref({
  name: '',
  email: '',
  avatar: null,
  password: '',
  password_confirmation: ''
})

const imagePreview = ref('')
const isLoading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

onMounted(async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('No token found')

    const response = await axios.get('http://127.0.0.1:8000/api/user/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })

    const user = response.data
    form.value.name = user.name || ''
    form.value.email = user.email || ''

    // Use full URL if avatar exists
    if (user.avatar_url) {
      imagePreview.value = user.avatar_url.startsWith('http')
        ? user.avatar_url
        : `http://127.0.0.1:8000/storage/${user.avatar_url}`
    } else {
      imagePreview.value = ''
    }

  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message
  }
})


const handleAvatarChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.value.avatar = file // Store the file for submission
    imagePreview.value = URL.createObjectURL(file) // Create a local preview URL
  }
}

const updateProfile = async () => {
  if (isLoading.value) return
  isLoading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('No token found')

    const formData = new FormData()
    formData.append('_method', 'PUT') // Laravel spoof method

    // Append name and email only if they are non-empty
    if (form.value.name) formData.append('name', form.value.name)
    if (form.value.email) formData.append('email', form.value.email)

    if (form.value.password) {
      formData.append('password', form.value.password)
      formData.append('password_confirmation', form.value.password_confirmation)
    }

    if (form.value.avatar) {
      formData.append('avatar', form.value.avatar)
    }

    const response = await axios.post(
      'http://127.0.0.1:8000/api/user/profile',
      formData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    successMessage.value = 'Profile updated successfully!'
    emit('updated', {
      name: response.data.name,
      email: response.data.email,
      avatar_url: response.data.avatar_url
    })

    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (err) {
    console.error(err)
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    isLoading.value = false
  }
}

const close = () => {
  emit('close')
}
</script>


<style scoped>
.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  position: relative;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0; /* ← Fix: was "bottom: 10" */
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
  z-index: 9998;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem; /* ← Allows safe margin on small screens */
  overflow-y: auto; /* ← Ensures overlay can scroll if needed */
}

/* Scrollable Modal Content */
.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh; /* ← Prevents overflow beyond viewport */
  overflow-y: auto; /* ← Makes content scrollable */
  position: relative;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  box-sizing: border-box; /* ← Ensures padding is included in height */
}

/* Keep close button visible at the top */
.close-btn {
  position: sticky;
  top: 0;
  right: 0;
  z-index: 10;
  background: white;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
  color: #666;
  border: 1px solid #eee;
  margin-left: auto;
  margin-bottom: 1rem;
}

h2 {
  margin-top: 0;
  color: #333;
  font-size: 1.5rem;
}

form label {
  display: block;
  margin-bottom: 1rem;
  font-weight: 500;
  color: #444;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"] {
  width: 100%;
  padding: 0.5rem;
  margin-top: 0.25rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

button[type="submit"] {
  background-color: #4CAF50;
  color: white;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  width: 100%;
  margin-top: 1rem;
}

button[type="submit"]:hover {
  background-color: #45a049;
}

button[type="submit"]:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.avatar-preview {
  margin-top: 1rem;
  text-align: center;
}

.avatar-preview img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #eee;
}
.success {
  color: #28a745;
  margin-top: 1rem;
  text-align: center;
}

.error {
  color: #dc3545;
  margin-top: 1rem;
  text-align: center;
}
</style>