<template>
  <div class="manage-products">
    <h2 class="title">📦 Manage Products</h2>

    <!-- General message -->
    <div v-if="messages._general" class="inline-message general">
      {{ messages._general }}
    </div>

    <!-- Product Cards -->
    <div v-if="products.length" class="product-cards">
      <div v-for="product in products" :key="product.id" class="product-card">

        <!-- Header -->
        <div class="card-header">
          <div class="image-wrapper">
            <img
              v-if="product.image"
              :src="`http://127.0.0.1:8000/storage/${product.image}`"
              :alt="product.name"
              @error="useFallbackImage"
            />
            <div v-else class="placeholder-image">🖼️ No Image</div>
          </div>

          <div class="info">
            <h3>{{ product.name }}</h3>
            <p class="price">₺{{ formatPrice(product.price) }}</p>
            <p class="stock">Stock: {{ product.stock }}</p>
          </div>
        </div>

        <!-- Farmer Info -->
        <div class="farmer-info">
          <strong>Farmer</strong>
          {{ product.user?.name || 'Unknown' }}
          <small>{{ product.user?.email || 'No email' }}</small>
        </div>

        <!-- Actions -->
        <div class="card-footer">
          <button class="btn-edit" @click="openEditModal(product)">✏️ Edit</button>
          <button
            v-if="deleteConfirmId !== product.id"
            class="btn-remove"
            @click="showDeleteConfirm(product.id)"
          >
            ❌ Remove
          </button>
        </div>

        <!-- Delete confirmation -->
        <div v-if="deleteConfirmId === product.id" class="delete-confirm">
          Are you sure?
          <div class="confirm-buttons">
            <button class="btn-yes" @click="removeProduct(product.id)">Yes</button>
            <button class="btn-no" @click="cancelDelete">No</button>
          </div>
        </div>

        <!-- Inline message -->
        <div v-if="messages[product.id]" class="inline-message">
          {{ messages[product.id] }}
        </div>
      </div>
    </div>

    <p v-else class="no-products">No products found.</p>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="pagination">
      <button :disabled="pagination.current_page === 1"
              @click="changePage(pagination.current_page - 1)">
        ← Prev
      </button>

      <span>Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>

      <button :disabled="pagination.current_page === pagination.last_page"
              @click="changePage(pagination.current_page + 1)">
        Next →
      </button>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal">
        <h3>Edit Product</h3>

        <form @submit.prevent="submitEdit">
          <label>
            Name
            <input v-model="editForm.name" required />
          </label>

          <label>
            Price
            <input type="number" v-model.number="editForm.price" min="0" step="0.01" required />
          </label>

          <label>
            Description
            <textarea v-model="editForm.description"></textarea>
          </label>

          <label>
            Stock
            <input type="number" v-model.number="editForm.stock" min="0" required />
          </label>

          <label>
            Category ID
            <input type="number" v-model.number="editForm.category_id" min="1" required />
          </label>

          <label>
            Image
            <input type="file" accept="image/*" @change="handleImageUpload" />
          </label>

          <div class="modal-actions">
            <button class="btn-save" type="submit">💾 Save</button>
            <button class="btn-cancel" type="button" @click="closeEditModal">❌ Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminManageProducts',

  data() {
    return {
      products: [],
      pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      showEditModal: false,
      deleteConfirmId: null,
      messages: {},
      editForm: {
        id: null,
        name: '',
        price: 0,
        description: '',
        stock: 0,
        category_id: null,
        image: null
      }
    }
  },

  mounted() {
    this.fetchProducts()
  },

  methods: {
    formatPrice(price) {
      return Number(price || 0).toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    async fetchProducts(page = 1) {
      try {
        const token = localStorage.getItem('token')
        const res = await axios.get(
          `http://127.0.0.1:8000/api/admin/products?page=${page}`,
          { headers: { Authorization: `Bearer ${token}` } }
        )

        this.products = res.data.data
        this.pagination = res.data
      } catch {
        this.setMessage(null, 'Failed to load products.')
      }
    },

    setMessage(id, text) {
      if (id) this.messages[id] = text
      else this.messages._general = text

      setTimeout(() => {
        if (id) delete this.messages[id]
        else delete this.messages._general
      }, 4000)
    },

    openEditModal(product) {
      this.editForm = {
        id: product.id,
        name: product.name,
        price: Number(product.price),
        description: product.description ?? '',
        stock: Number(product.stock),
        category_id: product.category_id,
        image: null
      }
      this.showEditModal = true
    },

    closeEditModal() {
      this.showEditModal = false
    },

    handleImageUpload(e) {
      this.editForm.image = e.target.files[0] || null
    },

    async submitEdit() {
      try {
        const token = localStorage.getItem('token')
        const formData = new FormData()

        formData.append('name', this.editForm.name)
        formData.append('price', this.editForm.price)
        formData.append('description', this.editForm.description)
        formData.append('stock', this.editForm.stock)
        formData.append('category_id', this.editForm.category_id)

        if (this.editForm.image) {
          formData.append('image', this.editForm.image)
        }

        await axios.post(
          `http://127.0.0.1:8000/api/admin/products/${this.editForm.id}?_method=PUT`,
          formData,
          { headers: { Authorization: `Bearer ${token}` } }
        )

        this.setMessage(this.editForm.id, 'Product updated successfully.')
        this.closeEditModal()
        this.fetchProducts(this.pagination.current_page)

      } catch (error) {
        alert(JSON.stringify(error.response?.data?.errors || error.message, null, 2))
      }
    },

    async removeProduct(id) {
      try {
        const token = localStorage.getItem('token')
        await axios.delete(
          `http://127.0.0.1:8000/api/admin/products/${id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        )

        this.setMessage(id, 'Product removed.')
        this.deleteConfirmId = null
        this.fetchProducts(this.pagination.current_page)

      } catch {
        this.setMessage(id, 'Failed to remove product.')
      }
    },

    showDeleteConfirm(id) {
      this.deleteConfirmId = id
    },

    cancelDelete() {
      this.deleteConfirmId = null
    },

    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchProducts(page)
      }
    },

    useFallbackImage(e) {
      e.target.src = 'https://via.placeholder.com/150?text=No+Image'
    }
  }
}
</script>


<style scoped>
/* ===== Inline messages ===== */
.inline-message {
  margin-top: 0.5rem;
  padding: 0.4rem 0.6rem;
  font-size: 0.85rem;
  background: #eef7ee;
  border-left: 3px solid #4caf50;
  color: #2e7d32;
  border-radius: 4px;
}
.inline-message.general { text-align: center; }

/* ===== Delete confirmation ===== */
.delete-confirm {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: #fff3f3;
  border-left: 4px solid #dc2626;
  border-radius: 4px;
  font-size: 0.85rem;
  color: #b91c1c;
}
.confirm-buttons { margin-top: 0.3rem; display: flex; gap: 0.5rem; }
.btn-yes { background-color: #dc2626; color:white; border:none; padding:0.3rem 0.6rem; border-radius:4px; cursor:pointer; font-size:0.8rem; }
.btn-yes:hover { background-color: #b91c1c; }
.btn-no { background-color: #f3f4f6; color:#374151; border:none; padding:0.3rem 0.6rem; border-radius:4px; cursor:pointer; font-size:0.8rem; }
.btn-no:hover { background-color: #e5e7eb; }

.manage-products {
  padding: 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.title {
  font-size: 1.5rem;
  color: #1b5e20;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

/* === Product Cards === */
.product-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.product-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.08);
  border: 1px solid #e8f5e8;
  transition: transform 0.2s ease;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(46, 125, 50, 0.12);
}

/* === Header === */
.card-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.image-wrapper {
  width: 60px;
  height: 60px;
  overflow: hidden;
  border-radius: 8px;
  flex-shrink: 0;
}

.image-wrapper img,
.placeholder-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.placeholder-image {
  background: #f1f8e9;
  color: #677a9fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
}

.info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  color: #1b5e20;
}

.price {
  font-weight: 700;
  color: #1b5e20;
  margin: 0;
  font-size: 1.1rem;
}

.stock {
  margin: 0;
  font-size: 0.85rem;
  color: #6b7280;
}

/* === Farmer Info === */
.farmer-info {
  margin-bottom: 0.75rem;
  padding: 0.5rem;
  background: #1e644d75;
  border-radius: 8px;
  font-size: 0.9rem;
}

.farmer-info strong {
  display: block;
  margin-bottom: 0.25rem;
    margin-Left: 0.25rem;
  color: #2e7d32;
}

.farmer-info small {
  color: #2e7d32;
  font-size: 0.8rem;
    margin-bottom: 0.25rem;
    margin-Left: 0.5rem;

}

/* === Footer === */
.card-footer {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.btn-edit {
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-edit:hover {
  background: #1e40af;
}

.btn-remove {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-remove:hover {
  background: #dc2626;
}

/* === No Products === */
.no-products {
  text-align: center;
  color: #6b7280;
  font-style: italic;
  padding: 2rem;
  background: #f8faf7;
  border-radius: 12px;
  border: 1px dashed #4caf50;
}

/* === Pagination === */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.pagination button {
  background-color: #2e7d32;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
}

.pagination button[disabled] {
  background-color: #a5d6a7;
  cursor: not-allowed;
  opacity: 0.6;
}

/* === Modal === */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal h3 {
  margin-top: 0;
  color: #1b5e20;
}

.modal label {
  display: block;
  margin-bottom: 0.75rem;
  font-weight: 500;
}

.modal input,
.modal textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1e7dd;
  border-radius: 6px;
  font-size: 0.95rem;
  box-sizing: border-box;
}

.modal textarea {
  height: 80px;
  resize: vertical;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
}

.btn-save {
  background-color: #16a34a;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}

.btn-save:hover {
  background-color: #15803d;
}

.btn-cancel {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}

.btn-cancel:hover {
  background-color: #dc2626;
}

/* === Responsive === */
@media (max-width: 480px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .image-wrapper {
    width: 100%;
    height: 120px;
  }

  .info h3 {
    font-size: 1.2rem;
  }

  .price {
    font-size: 1.2rem;
  }

  .modal {
    width: 90%;
    padding: 1rem;
  }
}
.product-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
  min-height: 400px; /* ADD THIS */
  align-items: start; /* ADD THIS - prevents stretching */
}

.product-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.08);
  border: 1px solid #e8f5e8;
  transition: transform 0.2s ease;
  height: 100%; /* ADD THIS - makes all cards equal height */
  display: flex; /* ADD THIS */
  flex-direction: column; /* ADD THIS */
}
.card-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.75rem;
  flex-shrink: 0; /* ADD THIS - prevents shrinking */
}

.farmer-info {
  margin-bottom: 0.75rem;
  padding: 0.5rem;
  background: #1e644d75;
  border-radius: 8px;
  font-size: 0.9rem;
  flex-shrink: 0; /* ADD THIS */
}

.card-footer {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  margin-top: auto; /* ADD THIS - pushes footer to bottom */
  flex-shrink: 0; /* ADD THIS */
}
</style>