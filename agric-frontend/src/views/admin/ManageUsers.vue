<template>
  <div class="manage-users">
    <h2 class="title">👥 Manage Users</h2>

    <!-- Users as Cards -->
    <div v-if="users.length" class="user-cards">
      <div v-for="user in users" :key="user.id" class="user-card">
        <!-- Header -->
        <div class="card-header">
          <div class="user-info">
            <div class="avatar">{{ user.name?.charAt(0).toUpperCase() || 'U' }}</div>
            <div>
              <strong>{{ user.name }}</strong>
              <small>{{ user.email }}</small>
            </div>
          </div>
          <span :class="['badge', user.role]">{{ user.role }}</span>
        </div>

        <!-- Footer Actions -->
        <div class="card-footer">
          <!-- Upgrade / Downgrade -->
          <button
            v-if="user.role === 'consumer' && userActionConfirmId !== user.id"
            @click="showUpgradeConfirm(user.id)"
            class="btn-upgrade"
          >
            🌱 Upgrade to Farmer
          </button>

          <button
            v-if="user.role === 'farmer' && userActionConfirmId !== user.id"
            @click="showDowngradeConfirm(user.id)"
            class="btn-downgrade"
          >
            🛒 Downgrade to Consumer
          </button>

          <!-- Stripe Onboarding Button (Farmers Only) -->
          <button
            v-if="user.role === 'farmer'"
            @click="connectStripe(user.id)"
            class="btn-connect-stripe"
          >
            💳 Connect Stripe
          </button>

          <!-- Delete -->
          <button
            v-if="userActionConfirmId !== user.id"
            class="btn-delete"
            @click="showDeleteConfirm(user.id)"
          >
            🗑️ Delete
          </button>
        </div>

        <!-- Inline Confirmation -->
        <div v-if="userActionConfirmId === user.id" class="action-confirm">
          <span>{{ userActionText }}</span>
          <div class="confirm-buttons">
            <button @click="performAction(user)" class="btn-yes">Yes</button>
            <button @click="cancelAction()" class="btn-no">No</button>
          </div>
        </div>

        <!-- Inline Message -->
        <div v-if="messages[user.id]" class="inline-message">
          {{ messages[user.id] }}
        </div>
      </div>
    </div>

    <!-- No Users -->
    <p v-else class="no-users">No users found.</p>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ManageUsers',
  data() {
    return {
      users: [],
      messages: {},
      userActionConfirmId: null,
      userActionType: null
    }
  },
  computed: {
    userActionText() {
      if (!this.userActionType) return ''
      switch (this.userActionType) {
        case 'upgrade': return 'Upgrade this user to Farmer?'
        case 'downgrade': return 'Downgrade this user to Consumer?'
        case 'delete': return 'Are you sure you want to delete this user?'
        default: return ''
      }
    }
  },
  async created() {
    await this.fetchUsers()
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem('token')
        const response = await axios.get('http://127.0.0.1:8000/api/admin/users', {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        })
        this.users = Array.isArray(response.data) ? response.data : []
      } catch (error) {
        console.error('Failed to fetch users:', error)
        this.users = []
      }
    },

    // ==== Show confirmation ====
    showUpgradeConfirm(userId) {
      this.userActionConfirmId = userId
      this.userActionType = 'upgrade'
    },
    showDowngradeConfirm(userId) {
      this.userActionConfirmId = userId
      this.userActionType = 'downgrade'
    },
    showDeleteConfirm(userId) {
      this.userActionConfirmId = userId
      this.userActionType = 'delete'
    },
    cancelAction() {
      this.userActionConfirmId = null
      this.userActionType = null
    },

    // ==== Perform action ====
    async performAction(user) {
      try {
        const token = localStorage.getItem('token')
        let url = ''
        switch (this.userActionType) {
          case 'upgrade':
            url = `http://127.0.0.1:8000/api/admin/users/${user.id}/upgrade`
            await axios.put(url, {}, { headers: { Authorization: `Bearer ${token}` } })
            this.setMessage(user.id, 'User upgraded to Farmer.')
            break
          case 'downgrade':
            url = `http://127.0.0.1:8000/api/admin/users/${user.id}/downgrade`
            await axios.put(url, {}, { headers: { Authorization: `Bearer ${token}` } })
            this.setMessage(user.id, 'User downgraded to Consumer.')
            break
          case 'delete':
            url = `http://127.0.0.1:8000/api/admin/users/${user.id}`
            await axios.delete(url, { headers: { Authorization: `Bearer ${token}` } })
            this.setMessage(user.id, 'User deleted successfully.')
            break
        }
        this.cancelAction()
        await this.fetchUsers()
      } catch (error) {
        console.error(`${this.userActionType} failed:`, error)
        this.setMessage(user.id, `Failed to ${this.userActionType} user.`)
        this.cancelAction()
      }
    },

    // ==== Stripe Connect ====
    async connectStripe(userId) {
      try {
        const token = localStorage.getItem('token')
        const response = await axios.post(
          `http://127.0.0.1:8000/api/admin/users/${userId}/stripe-connect`,
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        )
        window.open(response.data.url, '_blank')
        this.setMessage(userId, 'Redirecting to Stripe onboarding...')
      } catch (err) {
        console.error('Stripe connect error:', err)
        this.setMessage(userId, 'Failed to start Stripe onboarding.')
      }
    },

    // ==== Inline messages ====
    setMessage(userId, text) {
      this.messages[userId] = text
      setTimeout(() => {
        delete this.messages[userId]
      }, 4000)
    }
  }
}
</script>

<style scoped>
.inline-message {
  margin-top: 0.5rem;
  padding: 0.4rem 0.6rem;
  font-size: 0.85rem;
  background: #eef7ee;
  border-left: 3px solid #4caf50;
  color: #2e7d32;
  border-radius: 4px;
}

.action-confirm {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: #fff3f3;
  border-left: 4px solid #dc2626;
  border-radius: 4px;
  font-size: 0.85rem;
  color: #b91c1c;
}
.confirm-buttons {
  margin-top: 0.3rem;
  display: flex;
  gap: 0.5rem;
}
.btn-yes {
  background-color: #dc2626;
  color: white;
  border: none;
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}
.btn-yes:hover { background-color: #b91c1c; }
.btn-no {
  background-color: #f3f4f6;
  color: #374151;
  border: none;
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}
.btn-no:hover { background-color: #e5e7eb; }

.manage-users {
  padding: 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.title {
  font-size: 1.5rem;
  color: #1b5e20;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

/* === User Cards === */
.user-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.user-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.08);
  border: 1px solid #e8f5e8;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(46, 125, 50, 0.12);
}

/* === Header === */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
  gap: 0.75rem;
}

.user-info {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex: 1;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.user-info small {
  font-size: 0.8rem;
  color: #6b7280;
  display: block;
}

/* === Badge === */
.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: white;
}

.badge.consumer {
  background: #3b82f6;
}

.badge.farmer {
  background: #059669;
}

/* === Footer (Actions) === */
.card-footer {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: flex-end;
}

.btn-upgrade,
.btn-downgrade {
  background: #059669;
  color: white;
  border: none;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-upgrade:hover,
.btn-downgrade:hover {
  background: #047857;
}

.btn-delete {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-delete:hover {
  background: #dc2626;
}

/* Stripe Connect Button */
.btn-connect-stripe {
  background-color: #6772e5;
  color: white;
  border: none;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
}
.btn-connect-stripe:hover {
  background-color: #5469d4;
}

/* === No Users === */
.no-users {
  text-align: center;
  color: #6b7280;
  font-style: italic;
  padding: 2rem;
  background: #f8faf7;
  border-radius: 12px;
  border: 1px dashed #4caf50;
}

/* === Responsive === */
@media (max-width: 480px) {
  .manage-users {
    padding: 0.75rem;
  }

  .title {
    font-size: 1.3rem;
    text-align: center;
  }

  .user-cards {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .card-header,
  .card-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .card-footer {
    justify-content: center;
  }

  .badge {
    align-self: flex-start;
  }
}
</style>