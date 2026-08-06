<template>
  <div class="view-feedback">
    <h2>⭐ Manage Feedback</h2>

    <!-- Feedback Cards -->
    <div v-if="Array.isArray(feedback) && feedback.length" class="feedback-cards">
      <div v-for="item in feedback" :key="item.id" class="feedback-card">
        <!-- Card Header -->
        <div class="card-header">
          <div class="user-info">
            <div class="avatar">
              {{ item.user?.name?.charAt(0).toUpperCase() || '?' }}
            </div>
            <div>
              <strong>{{ item.user?.name || 'Unknown User' }}</strong>
              <div class="product-name">{{ item.product?.name || 'Unknown Product' }}</div>
            </div>
          </div>
          <div class="rating">
            <span v-for="star in 5" :key="star" :class="['star', star <= item.rating ? 'filled' : 'empty']">★</span>
            <span class="rating-value">{{ item.rating }}/5</span>
          </div>
        </div>

        <!-- Comment -->
        <div class="comment">"{{ item.comment }}"</div>

        <!-- Meta -->
        <div class="meta">
          <small>Posted: {{ new Date(item.created_at).toLocaleString() }}</small>
          <div class="status">
            <span v-if="item.approved" class="approved">✅ Approved</span>
            <span v-else class="pending">🟡 Pending</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="actions">
          <button v-if="!item.approved" @click="approveFeedback(item.id)" class="btn-approve">✔ Approve</button>
          <button @click="showDeleteConfirm(item.id)" class="btn-delete">🗑 Delete</button>
        </div>

        <!-- Inline Delete Confirmation -->
        <div v-if="deleteConfirmId === item.id" class="delete-confirm">
          <span>Are you sure you want to delete this feedback?</span>
          <div class="confirm-buttons">
            <button @click="deleteFeedback(item.id)" class="btn-yes">Yes</button>
            <button @click="cancelDelete()" class="btn-no">No</button>
          </div>
        </div>

        <!-- Inline Message -->
        <div v-if="messages[item.id]" class="inline-message">
          {{ messages[item.id] }}
        </div>
      </div>
    </div>

    <!-- No Feedback -->
    <p v-else class="no-feedback">No feedback found.</p>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.total > pagination.per_page">
      <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)" class="pagination-btn">← Prev</button>
      <span class="pagination-info">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)" class="pagination-btn">Next →</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ViewFeedback',
  data() {
    return {
      feedback: [],
      pagination: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
      loading: false,
      messages: {},          // Stores messages per feedback card
      deleteConfirmId: null, // ID of feedback currently showing delete confirmation
    }
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token')
      return { Authorization: `Bearer ${token}` }
    },

    async fetchFeedback(page = 1) {
      this.loading = true
      try {
        const res = await axios.get(`/api/admin/feedback?page=${page}`, {
          headers: this.getAuthHeaders(),
        })
        const data = res.data || {}
        this.feedback = Array.isArray(data.data) ? data.data : []
        this.pagination = {
          current_page: data.current_page || 1,
          last_page: data.last_page || 1,
          per_page: data.per_page || 20,
          total: data.total || 0,
        }
      } catch (err) {
        console.error('Failed to load feedback:', err)
        // Optionally set a general message here
      } finally {
        this.loading = false
      }
    },

    setMessage(id, text) {
  // Add message
  this.messages[id] = text;

  // Remove message after 4 seconds
  setTimeout(() => {
    delete this.messages[id];
  }, 4000);
},


    showDeleteConfirm(id) {
      this.deleteConfirmId = id
    },

    cancelDelete() {
      this.deleteConfirmId = null
    },

  async deleteFeedback(id) {
  try {
    await axios.delete(`/api/admin/feedback/${id}`, {
      headers: this.getAuthHeaders(),
    });

    // Show message first
    this.setMessage(id, 'Feedback deleted successfully.');

    // Remove deleted item locally without refetching
    this.feedback = this.feedback.filter(f => f.id !== id);

    // Close confirmation box
    this.deleteConfirmId = null;
  } catch (err) {
    console.error('Failed to delete feedback:', err);
    this.setMessage(id, 'Failed to delete feedback.');
    this.deleteConfirmId = null;
  }
},

    async approveFeedback(id) {
      try {
        await axios.post(`/api/admin/feedbacks/${id}/approve`, null, {
          headers: this.getAuthHeaders(),
        })
        this.setMessage(id, 'Feedback approved successfully.')
        this.fetchFeedback(this.pagination.current_page)
      } catch (err) {
        console.error('Failed to approve feedback:', err)
        this.setMessage(id, 'Failed to approve feedback.')
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchFeedback(page)
      }
    },
  },
  mounted() {
    this.fetchFeedback()
  },
}
</script>

<style scoped>
.view-feedback {
  padding: 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
  color: #1b5e20;
  margin-bottom: 1.5rem;
  font-size: 1.4rem;
  font-weight: 600;
}

/* Feedback Cards */
.feedback-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.feedback-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.08);
  border: 1px solid #e8f5e8;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.feedback-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(46, 125, 50, 0.12);
}

/* Card Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.75rem;
  gap: 0.75rem;
}

.user-info {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
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

.product-name {
  font-size: 0.85rem;
  color: #4caf50;
  font-weight: 500;
}

/* Rating */
.rating {
  text-align: right;
  font-size: 0.9rem;
}

.star {
  color: #ddd;
  font-size: 1.1rem;
  margin-right: 2px;
}

.star.filled {
  color: #ffab00;
}

.rating-value {
  display: block;
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 2px;
}

/* Comment */
.comment {
  font-style: italic;
  color: #374151;
  margin-bottom: 0.75rem;
  line-height: 1.5;
  font-size: 0.95rem;
  padding: 0.5rem;
  background: #f8faf7;
  border-left: 3px solid #4caf50;
  border-radius: 4px;
}

/* Meta */
.meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.status {
  font-weight: 600;
}

.status .approved { color: #16a34a; }
.status .pending { color: #fb8c00; }

/* Actions */
.actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.btn-delete {
  background-color: #dc2626;
  border: none;
  color: white;
  padding: 0.4rem 0.7rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background-color 0.2s ease;
}

.btn-delete:hover { background-color: #b91c1c; }

.btn-approve {
  background-color: #16a34a;
  border: none;
  color: white;
  padding: 0.4rem 0.7rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background-color 0.2s ease;
}

.btn-approve:hover { background-color: #15803d; }

/* No Feedback */
.no-feedback {
  text-align: center;
  color: #6b7280;
  font-style: italic;
  padding: 2rem;
  background: #f8faf7;
  border-radius: 12px;
  border: 1px dashed #4caf50;
}

/* Pagination */
.pagination {
  margin-top: 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.pagination-btn {
  background-color: #2e7d32;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s ease;
}

.pagination-btn[disabled] {
  background-color: #a5d6a7;
  cursor: not-allowed;
  opacity: 0.6;
}

.pagination-info {
  font-size: 0.9rem;
  color: #2d372f;
  font-weight: 500;
}

/* Inline Message */
.inline-message {
  margin-top: 0.5rem;
  padding: 0.4rem 0.6rem;
  font-size: 0.85rem;
  background: #eef7ee;
  border-left: 3px solid #4caf50;
  color: #2e7d32;
  border-radius: 4px;
}

/* Delete Confirmation Box */
.delete-confirm {
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

/* Responsive */
@media (max-width: 480px) {
  .feedback-cards { grid-template-columns: 1fr; gap: 0.75rem; }
  .card-header, .meta { flex-direction: column; align-items: stretch; }
  .rating { text-align: left; margin-top: 0.5rem; }
  .actions { justify-content: flex-end; }
  .pagination { gap: 0.75rem; }
  .pagination-btn, .pagination-info { font-size: 0.85rem; padding: 0.4rem 0.8rem; }
}
</style>
