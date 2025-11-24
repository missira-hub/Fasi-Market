<template>
  <div class="app-container">
    <!-- Sidebar (Responsive) -->
    <aside class="sidebar" :class="{ 'sidebar-hidden': sidebarHidden }">
      <h2>FASI-MARKET</h2>
      <nav>
        <ul>
          <li @click="switchSection('overview')" :class="{ active: section === 'overview' }">📊 Dashboard</li>
          <li @click="switchSection('listings')" :class="{ active: section === 'listings' }">🧺 My Listings</li>
          <li @click="switchSection('messages')" :class="{ active: section === 'messages' }"> <!-- Fixed: Added missing quote -->
            💬 Messages <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
          </li>
          <li @click="switchSection('feedback')" :class="{ active: section === 'feedback' }">⭐ Feedback</li>
          <li @click="switchSection('orders')" :class="{ active: section === 'orders' }">
            📦 Orders
          </li>
          <li @click="switchSection('sales')" :class="{ active: section === 'sales' }">📦 Sales History</li>
          <li class="logout" @click="handleLogout">
            <span>🚪 Logout</span>
          </li>
        </ul>
      </nav>
    </aside>
    <!-- Main Content Area -->
    <div class="main-wrapper" :style="{ marginLeft: sidebarHidden ? '0' : '250px' }">
      <!-- Dashboard Header (Sticky) -->
      <header class="dashboard-header">
        <!-- Add a hamburger menu button here if needed -->
        <button @click="toggleSidebar" class="hamburger-icon">☰</button>
        <div class="greeting">
          <h2>👋 Hello, {{ currentUser?.name || 'Farmer' }}</h2>
          <p>Welcome back to your dashboard</p>
        </div>
        <div class="user-profile" @click="openProfileModal" title="Click to update your profile">
          <img
            v-if="currentUser?.avatar_url"
            :src="currentUser.avatar_url"
            alt="Profile"
            class="profile-picture clickable"
          />
          <div v-else class="profile-picture placeholder">👤</div>
        </div>
      </header>
      <!-- Scrollable Main Content -->
      <main class="main-content">
        <ProfileModal
          v-if="profileModalOpen"
          @close="closeProfileModal"
          @updated="onProfileUpdated"
        />
        <!-- Dashboard Overview -->
        <section v-if="section === 'overview'" class="dashboard-overview">
          <!-- Stats Grid -->
          <div class="stats-grid">
            <div class="stat-card" @click="switchSection('listings')">
              <div class="stat-icon">📦</div>
              <div class="stat-number">{{ products.length }}</div>
              <div class="stat-label">Active Products</div>
            </div>
            <div class="stat-card" @click="switchSection('sales')">
              <div class="stat-icon">💰</div>
              <div class="stat-number">₺{{ calculateMonthlyRevenue() }}</div>
              <div class="stat-label">This Month</div>
            </div>
            <div class="stat-card" @click="switchSection('messages')">
              <div class="stat-icon">📬</div>
              <div class="stat-number">{{ unreadCount }}</div>
              <div class="stat-label">New Messages</div>
            </div>
            <div class="stat-card" @click="switchSection('feedback')">
              <div class="stat-icon">⭐</div>
              <div class="stat-number">{{ calculateAverageRating() }}</div>
              <div class="stat-label">Rating</div>
            </div>
          </div>
          <!-- Quick Actions -->
          <div class="content-section">
            <h3 class="section-title">Quick Actions</h3>
            <div class="quick-actions">
              <button class="action-btn" @click="switchSection('listings'); showForm = true;">
                <span>➕</span> Add Product
              </button>
              <button class="action-btn" @click="switchSection('sales')">
                <span>📋</span> View History
              </button>
              <button class="action-btn" @click="switchSection('listings')">
                <span>📊</span> Update listings
              </button>
              <button class="action-btn" @click="switchSection('feedback')">
                <span>📈</span> View Feedback
              </button>
            </div>
          </div>
          <!-- Recent Activity -->
          <div class="content-section">
            <h3 class="section-title">Recent Activity</h3>
            <div class="activity-list">
              <div v-for="sale in sales.slice(0, 5)" :key="sale.id" class="activity-item">
                <div class="activity-icon">💰</div>
                <div class="activity-content">
                  <p><strong>{{ sale.product?.name }}</strong> × {{ sale.quantity }}</p>
                  <span>{{ formatDate(sale.created_at) }}</span>
                </div>
                <div class="activity-value">₺{{ sale.total_price }}</div>
              </div>
              <div v-if="sales.length === 0" class="empty-activity">
                No recent activity
              </div>
            </div>
          </div>
        </section>

        <!-- Listings Section -->
        <section v-if="section === 'listings'" class="farmer-listings">
          <div class="header">
            <h2>🧺 My Product Listings</h2>
            <button @click="switchSection('listings'); showForm = !showForm;" class="btn-primary">
              {{ showForm ? (editMode ? 'Cancel Edit' : 'Cancel') : '➕ Add Product' }}
            </button>
          </div>
          <div class="filter-controls">
            <select v-model="selectedCategory" @change="filterProducts">
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <!-- Add/Edit Product Form -->
          <form v-if="showForm" @submit.prevent="submitProduct" enctype="multipart/form-data" class="form">
            <div class="form-row">
              <div class="form-group">
                <label for="productName">Product Name</label>
                <input
                  id="productName"
                  v-model="newProduct.name"
                  type="text"
                  placeholder="e.g. Organic Tomatoes"
                  required
                />
              </div>
              <div class="form-group">
                <label for="productPrice">Price (₺)</label>
                <input
                  id="productPrice"
                  v-model.number="newProduct.price"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="e.g. 12.99"
                  required
                />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="productQuantity">Quantity in Stock</label>
                <input
                  id="productQuantity"
                  v-model.number="newProduct.quantity"
                  type="number"
                  min="0"
                  placeholder="e.g. 100"
                  required
                />
              </div>
              <div class="form-group">
                <label for="productUnit">Unit of Measure</label>
                <select id="productUnit" v-model="newProduct.unit_id" required>
                  <option value="">Select a unit</option>
                  <option v-for="unit in units" :key="unit.id" :value="unit.id">
                    {{ unit.name }} ({{ unit.abbreviation }})
                  </option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="productCategory">Category</label>
              <select id="productCategory" v-model="newProduct.category_id" required>
                <option value="">Select a category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label for="productDescription">Description</label>
              <textarea
                id="productDescription"
                v-model="newProduct.description"
                rows="3"
                placeholder="Describe your product: freshness, origin, packaging, etc."
              ></textarea>
            </div>
            <div class="form-group">
              <label for="productImage">Product Image</label>
              <input
                id="productImage"
                type="file"
                @change="handleImageChange"
                accept="image/*"
                ref="fileInput"
              />
              <div v-if="imagePreview" class="image-preview">
                <img :src="imagePreview" alt="Image Preview" />
                <button type="button" @click="removeImagePreview">Remove</button>
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn-primary">
                {{ editMode ? 'Update Product' : 'Add Product' }}
              </button>
              <button v-if="editMode" type="button" @click="cancelEdit" class="btn-secondary">
                Cancel Edit
              </button>
            </div>
          </form>
          <!-- Product Grid -->
          <div v-if="filteredProducts.length === 0" class="empty-state">
            <p>No products found {{ selectedCategory ? 'in this category' : '' }}.</p>
          </div>
          <div v-else class="product-grid">
            <div v-for="product in filteredProducts" :key="product.id" class="product-card">
              <div class="product-image-container">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
                <div v-else class="image-placeholder">No Image</div>
              </div>
              <div class="product-info">
                <h3>{{ product.name }}</h3>
                <p>{{ product.description }}</p>
              </div>
              <div class="product-meta-beautified">
                <div class="meta-item">
                  <strong>₺{{ product.price }}</strong>
                  <span v-if="product.unit">/ {{ product.unit.abbreviation }}</span>
                </div>
                <div class="meta-item">
                  <strong>Qty:</strong> {{ product.quantity }}
                  <span v-if="product.unit"> {{ product.unit.abbreviation }}</span>
                </div>
                <div class="meta-item category-tag">
                  {{ getCategoryName(product.category_id) }}
                </div>
                <div class="meta-item" :class="{ 'out-of-stock': product.quantity === 0 }">
                  {{ product.quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                </div>
                <div class="product-actions">
                  <button @click="startEdit(product)" class="btn-secondary">✏️ Edit</button>
                  <button @click="deleteProduct(product.id)" class="btn-danger">🗑 Delete</button>
                </div>
              </div>
            </div>
          </div>
        </section>

   <!-- FARMER MESSAGING SECTION -->
<section v-if="section === 'messages'" class="messaging-section">
  <div class="messaging-container">
    <!-- Conversations Sidebar -->
    <div class="conversation-list">
      <div class="conversation-header">
        <h3>Messages</h3>
        <input
          v-model="conversationSearchQuery"
          type="text"
          placeholder="Search..."
          class="search-box"
        />
      </div>
      <div v-if="loadingConversations" class="loading">Loading conversations...</div>
      <ul v-else class="conversation-items">
        <li
          v-for="conv in filteredConversations"
          :key="conv.id"
          :class="['conversation', { active: currentConversation?.id === conv.id }]"
          @click="selectConversation(conv)"
        >
          <img :src="getConversationAvatar(conv)" class="avatar" />
          <div class="info">
            <div class="top">
              <strong>{{ getConversationTitle(conv) }}</strong>
              <small>{{ formatTime(conv.latest_message?.created_at) }}</small>
            </div>
            <p class="last">{{ getLastMessagePreview(conv) }}</p>
          </div>
          <span v-if="conv.unread_count > 0" class="badge">
            {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
          </span>
        </li>
      </ul>
    </div>
    <!-- Chat Window -->
    <div class="chat-area">
      <div v-if="!currentConversation" class="placeholder">
        <p>Select a conversation to start messaging</p>
      </div>
      <div v-else class="chat-window">
        <!-- Header -->
        <div class="chat-header">
          <img :src="getConversationAvatar(currentConversation)" class="avatar" />
          <div>
            <h4>{{ getConversationTitle(currentConversation) }}</h4>
            <small>Online</small>
          </div>
        </div>


<!-- Messages -->
<div class="messages" ref="messagesContainer">
  <div
    v-for="(dateGroup, dateIndex) in groupMessagesByDateAndSender(currentConversation.messages)"
    :key="dateIndex"
    class="date-group"
  >
    <!-- Date Header -->
    <div class="date-header">
      <span>{{ dateGroup.formattedDate }}</span>
    </div>
    
    <!-- Message Groups for this date -->
    <div
      v-for="(senderGroup, senderIndex) in dateGroup.senderGroups"
      :key="senderIndex"
      :class="['message-group', senderGroup.sender_id === userId ? 'sent-group' : 'received-group']"
    >
      <div class="avatar-container" v-if="senderGroup.sender_id !== userId">
        <img :src="senderGroup.avatar_url" class="avatar" />
      </div>
      <div class="messages-bubble">
        <div
          v-for="(msg, msgIndex) in senderGroup.messages"
          :key="msg.id"
          :class="['message', 'grouped-message', senderGroup.sender_id === userId ? 'sent' : 'received']"
        >
          <div class="bubble">
            <strong v-if="msgIndex === 0 && senderGroup.sender_id !== userId">{{ senderGroup.sender_name }}</strong>
            <div class="message-content">
              {{ msg.message_text || msg.message }}
              <!-- Display images/files if present -->
              <div v-if="msg.attachment_url" class="attachment-preview">
                <img v-if="isImage(msg.attachment_url)" :src="msg.attachment_url" alt="Attachment" class="attached-image" />
                <div v-else class="file-attachment">
                  📎 {{ getFileName(msg.attachment_url) }}
                </div>
              </div>
            </div>

<div class="message-meta">
  <small class="timestamp">{{ formatTime(msg.created_at) }}</small>
  <span v-if="msg.sender_id === userId" class="read-status">
    <span v-if="msg.is_read" class="blue-ticks">✓✓</span>
    <span v-else class="gray-ticks">✓</span>
  </span>
</div>       


<!-- Message options (three dots) - only show on last message in group -->
            <div 
  v-if="msgIndex === senderGroup.messages.length - 1" 
  :class="['message-options', selectedMessageId === msg.id ? 'active' : '']"
  @click.stop="toggleMessageOptions(msg.id)"
>
  ⋮
</div>
<div v-if="selectedMessageId === msg.id" class="options-menu">
  <button @click.stop="replyToMessage(msg)">Reply</button>
  <button @click.stop="forwardMessage(msg)">Forward</button>
  <button @click.stop="deleteMessage(msg)">Delete</button>
</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
               <!-- Reply indicator (shows above input when replying) -->
        <div v-if="replyMessage" class="reply-indicator">
          <div class="reply-header">
            <span class="reply-sender">Replying to: {{ replyMessage.sender }}</span>
            <button @click="cancelReply" class="cancel-reply">✕</button>
          </div>
          <div class="reply-content">{{ replyMessage.text }}</div>
        </div>
        
        <!-- Input -->
        <div class="input-row">
          <div class="input-tools">
            <button @click="openFilePicker" title="Attach file">
              📎
            </button>
            <button @click="toggleEmojiPicker" title="Add emoji">
              😊
            </button>
          </div>
          <input
            v-model="newMessage"
            @keyup.enter="sendMessage"
            placeholder="Type a message..."
          />
          <button @click="sendMessage" :disabled="!newMessage.trim() && !selectedFile">Send</button>
          <input 
            type="file" 
            ref="fileInput" 
            @change="handleFileSelect" 
            style="display: none" 
            multiple
          />
        </div>
        
        <!-- Emoji Picker -->
        <div v-if="showEmojiPicker" class="emoji-picker">
          <div 
            v-for="emoji in emojiList" 
            :key="emoji" 
            @click="addEmoji(emoji)" 
            class="emoji-option"
            :title="emoji"
          >
            {{ emoji }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

        <!-- Feedback Section - Beautiful Card View -->
        <section v-if="section === 'feedback'" class="content-section view-feedback">
          <h2 class="section-title">
            <span>⭐</span> Manage Feedback & Replies
          </h2>
          <!-- Feedback Cards -->
          <div v-if="feedback.length" class="feedback-cards-grid">
            <div v-for="item in feedback" :key="item.id" class="feedback-card">
              <!-- Header: User & Product -->
              <div class="card-header">
                <div class="user-chip">
                  <div class="avatar">{{ (item.user?.name || 'U')[0].toUpperCase() }}</div>
                  <span>{{ item.user?.name || 'Unknown User' }}</span>
                </div>
                <div class="product-tag">
                  {{ item.product?.name || 'Unknown Product' }}
                </div>
              </div>
              <!-- Body -->
              <div class="card-body">
                <!-- Rating -->
                <div class="rating-stars">
                  <span v-for="star in 5" :key="star" :class="{ filled: star <= item.rating }">
                    ★
                  </span>
                  <small>Rated {{ item.rating }}/5</small>
                </div>
                <!-- Comment -->
                <blockquote class="comment">
                  "{{ item.comment }}"
                </blockquote>
                <!-- Meta Info -->
                <div class="meta-info">
                  <time>{{ new Date(item.created_at).toLocaleDateString('en-US', {
                      year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    }) }}</time>
                  <div class="status-badge" :class="item.approved ? 'approved' : 'pending'">
                    {{ item.approved ? 'Approved' : 'Pending' }}
                  </div>
                </div>
                <!-- Reply Section -->
                <div class="reply-section">
                  <div v-if="item.reply" class="reply-box">
                    <strong>✅ You replied:</strong>
                    <p class="reply-text">{{ item.reply }}</p>
                  </div>
                  <div v-else class="reply-form">
                    <textarea
                      v-model="replies[item.id]"
                      placeholder="Type your thoughtful reply..."
                      rows="2"
                      maxlength="500"
                    ></textarea>
                    <button
                      @click="sendReply(item.id)"
                      :disabled="!replies[item.id] || sendingReply[item.id]"
                      class="btn primary"
                    >
                      {{ sendingReply[item.id] ? '📤 Sending...' : '📤 Reply' }}
                    </button>
                  </div>
                </div>
              </div>
              <!-- Card Footer / Actions -->
              <div class="card-footer">
                <button
                  v-if="!item.approved"
                  @click="approveFeedback(item.id)"
                  class="btn outline approve"
                >
                  ✅ Approve
                </button>
                <button @click="deleteFeedback(item.id)" class="btn danger">
                  🗑 Delete
                </button>
              </div>
            </div>
          </div>
          <!-- No Feedback -->
          <div v-else class="empty-state">
            <img src="https://img.icons8.com/ios-filled/100/cccccc/speech-bubble.png" alt="No feedback" class="empty-icon" />
            <p>No feedback found. Come back later!</p>
          </div>
          <!-- Pagination -->
          <div v-if="pagination.total > pagination.per_page" class="pagination-controls">
            <button
              class="btn pagination-btn"
              :disabled="pagination.current_page === 1"
              @click="changePage(pagination.current_page - 1)"
            >
              ← Prev
            </button>
            <span class="page-info">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
            <button
              class="btn pagination-btn"
              :disabled="pagination.current_page === pagination.last_page"
              @click="changePage(pagination.current_page + 1)"
            >
              Next →
            </button>
          </div>
        </section>


        <!-- Order Management Section -->
        <section v-if="section === 'orders'" class="content-section">
          <h2>📦 Order Management</h2>
          <div v-if="ordersLoading" class="loading">Loading orders...</div>
          <div v-else-if="orders.length === 0" class="empty-state">
            <p>No orders yet. Your products will appear here once a customer places an order.</p>
          </div>
          <div v-else class="orders-list">
            <div v-for="order in orders" :key="order.id" class="order-card">
              <!-- Order Header -->
              <div class="order-header">
                <div class="order-id">Order #{{ order.id }}</div>
                <div class="order-status" :class="order.status || 'unknown'">
                  {{ formatStatus(order.status) }}
                </div>
              </div>
              <!-- Customer Info -->
              <div class="order-customer">
                <strong>Customer:</strong> {{ order.customer_name }}<br />
                <strong>Phone:</strong> {{ order.customer_phone || 'Not provided' }}<br />
                <strong>Address:</strong> {{ order.shipping_address }}
              </div>
              <!-- Order Items -->
              <div class="order-items">
                <div v-for="item in order.items" :key="item.id" class="order-item">
                  <img
                    v-if="item.product?.image"
                    :src="`http://127.0.0.1:8000/storage/${item.product.image}`"
                    :alt="item.product.name"
                    class="item-image"
                    @error="useFallbackImage"
                  />
                  <div v-else class="item-image placeholder">🖼️</div>
                  <div class="item-details">
                    <h4>{{ item.product?.name || 'Unknown Product' }}</h4>
                    <p>
                      {{ item.quantity }} × ₺{{ item.price }}
                      <span v-if="item.product?.unit"> {{ item.product.unit.abbreviation }}</span>
                      = <strong>₺{{ (item.quantity * item.price).toFixed(2) }}</strong>
                    </p>
                  </div>
                </div>
              </div>
              <!-- Order Summary -->
              <div class="order-summary">
                <div><strong>Total:</strong> ₺{{ order.total_price }}</div>
                <div><strong>Date:</strong> {{ formatDate(order.created_at) }}</div>
              </div>
              <!-- Farmer Instructions -->
              <div class="farmer-instructions">
                <h4>📋 What You Should Do:</h4>
                <ol>
                  <li v-if="order.status === 'pending'">
                    <strong>Wait for Payment</strong> – The customer has placed the order. Wait for status to change to <em>paid</em>.
                  </li>
                  <li v-if="order.status === 'paid'">
                    <strong>Prepare the Order</strong> – Pack the items. This order is confirmed and paid.
                  </li>
                  <li v-if="order.status === 'paid' || order.status === 'shipped'">
                    <strong>Delivery:</strong>
                    <template v-if="order.delivery_method === 'pickup'">
                      Customer will pick up from your farm.
                    </template>
                    <template v-else>
                      Deliver to: {{ order.shipping_address }}
                    </template>
                  </li>
                  <li v-if="order.status === 'shipped'">
                    <strong>🚚 Shipped</strong> – Marked as shipped on {{ formatDate(order.shipped_at) }}
                  </li>
                  <li v-if="order.status === 'delivered'">
                    <strong>✅ Delivered</strong> – Customer has received the order.
                  </li>
                  <li v-if="order.status === 'cancelled'">
                    <strong>🚫 Cancelled</strong> – Order was cancelled.
                  </li>
                  <li v-if="order.status === 'unknown'">
                    <strong>❓ Unknown Status</strong> – Contact support.
                  </li>
                </ol>
              </div>
              <!-- Action Buttons -->
              <div class="order-actions">
                <button
                  v-if="order.status === 'paid'"
                  @click="markAsShipped(order.id)"
                  class="btn-primary"
                >
                  Mark as Shipped
                </button>
                <button
                  v-if="order.status === 'shipped'"
                  @click="markAsDelivered(order.id)"
                  class="btn-success"
                >
                  Mark as Delivered
                </button>
                <button @click="printOrder(order)" class="btn-secondary">
                  🖨️ Print Order
                </button>
              </div>
            </div>
          </div>
        </section>


        
        <!-- Sales History Section -->
        <section v-if="section === 'sales'" class="content-section">
          <h2>📦 Sales History</h2>
          <div v-if="salesLoading" class="loading">Loading sales data...</div>
          <div v-else-if="sales.length > 0" class="sales-grid">
            <div v-for="sale in sales" :key="sale.id" class="sale-card">
              <!-- Product Image & Info -->
              <div class="sale-header">
                <img
                  v-if="sale.product?.image"
                  :src="`http://127.0.0.1:8000/storage/${sale.product.image}`"
                  :alt="sale.product.name"
                  class="sale-product-image"
                  @error="useFallbackImage"
                />
                <div v-else class="sale-product-image placeholder">🖼️</div>
                <div class="sale-details">
                  <h3>{{ sale.product?.name || 'Unknown Product' }}</h3>
                  <p class="farmer">
                    <strong>Farmer:</strong> {{ sale.product?.user?.name || 'You' }}
                  </p>
                  <p class="category" v-if="sale.product?.category">
                    <strong>Category:</strong> {{ sale.product.category.name }}
                  </p>
                </div>
              </div>
              <!-- Sales Stats -->
              <div class="sale-stats">
                <div class="stat">
                  <span class="label">Price</span>
                  <span class="value">₺{{ sale.unit_price }}</span>
                </div>
                <div class="stat">
                  <span class="label">Qty</span>
                  <span class="value">
                    {{ sale.quantity }}
                    <span v-if="sale.product?.unit"> {{ sale.product.unit.abbreviation }}</span>
                  </span>
                </div>
                <div class="stat">
                  <span class="label">Total</span>
                  <span class="value">₺{{ sale.total_price }}</span>
                </div>
              </div>
              <!-- Order & Date -->
              <div class="sale-meta">
                <span>Order #{{ sale.order_id }}</span>
                <span>{{ formatDate(sale.created_at) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>Nothing has been sold yet.</p>
          </div>
        </section>
        <!-- Inline Confirmation Box -->
        <div v-if="confirmBox.visible" class="confirm-overlay">
          <div class="confirm-box">
            <p>{{ confirmBox.message }}</p>
            <div class="actions">
              <button @click="confirmYes" class="btn-primary">Yes</button>
              <button @click="confirmNo" class="btn-secondary">No</button>
            </div>
          </div>
        </div>
        <!-- Inline Status Message -->
        <div v-if="statusMessage.text" :class="['status-message', statusMessage.type]">
          {{ statusMessage.text }}
        </div>
        <!-- Custom Confirmation Dialog -->
        <div v-if="confirmBox.visible" class="confirm-overlay">
          <div class="confirm-modal">
            <p>{{ confirmBox.message }}</p>
            <div class="confirm-buttons">
              <button @click="confirmNo" class="btn-cancel">No</button>
              <button @click="confirmYes" class="btn-confirm">Yes</button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import ProfileModal from '@/components/ProfileModal2.vue'
// Initialize auth store
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user)
// API configuration
axios.defaults.baseURL = 'http://127.0.0.1:8000'
// Section management
const section = ref('overview')
const switchSection = (newSection) => {
  section.value = newSection
  if (newSection === 'messages') {
    fetchConversations()
  } else if (newSection === 'feedback') {
    fetchFeedback()
  } else if (newSection === 'sales') {
    fetchSales()
  } else if (newSection === 'listings') {
    fetchProducts()
  } else if (newSection === 'orders') {
    fetchOrders() // ✅ Refetch when tab is clicked
  }
}
// Reactive state
const confirmBox = reactive({
  visible: false,
  message: '',
  action: null, // function to call on confirm
})
const statusMessage = reactive({
  text: '',
  type: '' // success or error
})
// Show confirmation box
const showConfirm = (message, action) => {
  confirmBox.visible = true
  confirmBox.message = message
  confirmBox.action = action
}
// Handle confirm
const confirmYes = () => {
  if (confirmBox.action) confirmBox.action()
  confirmBox.visible = false
}
const confirmNo = () => {
  confirmBox.visible = false
}
// Handle status messages
const showStatus = (text, type = 'success') => {
  statusMessage.text = text
  statusMessage.type = type
  setTimeout(() => {
    statusMessage.text = ''
  }, 3000)
}
// Replace the userId line with:

// Update it when profile loads:
const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      showStatus('Authentication token not found. Please log in again.', 'error');
      return;
    }
    const res = await axios.get('/api/user/profile', {
      headers: { Authorization: `Bearer ${token}` },
    });
    const userData = res.data;
    userData.avatar_url = userData.avatar_url || '/default-avatar.png';
    
    // Save to auth store AND set userId
    authStore.setUser(userData);
    userId.value = userData.id; // ✅ Set the real user ID
    
    console.log('Fetched user:', userData);
    showStatus('Profile loaded successfully.');
  } catch (error) {
    console.error('Failed to fetch user profile:', error);
    showStatus('Could not load your profile. Please log in again.', 'error');
    localStorage.removeItem('token');
    window.location.href = '/login';
  }
};




// Orders
const orders = ref([])
const ordersLoading = ref(false)
// Fetch Orders - Safe & Verified
const fetchOrders = async () => {
  ordersLoading.value = true;
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      showStatus('Authentication token not found. Please log in again.', 'error');
      return;
    }
    const res = await axios.get('/api/farmer/orders', {
      headers: { Authorization: `Bearer ${token}` }
    });
    // Normalize: handle both [ ] and { [ ] }
    const data = Array.isArray(res.data) ? res.data : res.data.data || [];
    orders.value = data;
    console.log('✅ Orders loaded:', orders.value);
    showStatus('Orders loaded successfully.');
  } catch (error) {
    console.error('❌ Failed to fetch orders:', error);
    orders.value = []; // ✅ Fallback to empty array
    showStatus('Could not load orders. Please try again later.', 'error');
  } finally {
    ordersLoading.value = false;
  }
};
// Helper: Safely format status (handles undefined/null)
const formatStatus = (status) => {
  if (!status) return 'Unknown'
  return status.replace('_', ' ').replace('-', ' ').toUpperCase()
}
const markAsDelivered = async (orderId) => {
  try {
    await axios.post(
      `/api/farmer/orders/${orderId}/status`,
      { status: 'delivered' },
      { headers: getAuthHeaders() }
    )
    showStatus('✅ Order marked as delivered.')
    fetchOrders()
  } catch (error) {
    console.error('Failed to mark as delivered:', error)
    showStatus(error.response?.data?.message || '❌ Failed to update status.', 'error')
  }
}
// ✅ Updated markAsShipped without alert()
const markAsShipped = async (orderId) => {
  if (!confirm('Has the order been shipped?')) return
  try {
    await axios.post(`/api/farmer/orders/${orderId}/status`, {
      status: 'shipped'
    }, {
      headers: getAuthHeaders()
    })
    message.value = 'Order marked as shipped.'
    fetchOrders()
    // auto-clear message after 3 seconds
    setTimeout(() => {
      message.value = ''
    }, 3000)
  } catch (error) {
    console.error('Failed to mark as shipped:', error)
    message.value = 'Failed to mark as shipped.'
    setTimeout(() => {
      message.value = ''
    }, 3000)
  }
}
// Print Order
const printOrder = (order) => {
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>Order #${order.id}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
          .item { display: flex; gap: 10px; margin: 10px 0; }
          .item img { width: 50px; height: 50px; object-fit: cover; }
        </style>
      </head>
      <body>
        <div class="header">
          <h2>FASI-MARKET – Order #${order.id}</h2>
          <p>Date: ${formatDate(order.created_at)}</p>
        </div>
        <h3>Customer: ${order.customer_name}</h3>
        <p>Phone: ${order.customer_phone || 'N/A'}</p>
        <p>Address: ${order.shipping_address}</p>
        <h3>Items:</h3>
        <ul>
          ${order.items.map(item => `
            <li class="item">
              <img src="http://127.0.0.1:8000/storage/${item.product.image}" />
              <div>
                <strong>${item.product.name}</strong><br/>
                ${item.quantity} × ₺${item.price} = ₺${(item.quantity * item.price).toFixed(2)}
              </div>
            </li>
          `).join('')}
        </ul>
        <h3>Total: ₺${order.total_price}</h3>
        <p>Status: ${order.status.toUpperCase()}</p>
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.print()
}
// Profile modal
const profileModalOpen = ref(false)
const openProfileModal = () => profileModalOpen.value = true
const closeProfileModal = () => profileModalOpen.value = false
const onProfileUpdated = (newUserData) => {
  authStore.setUser(newUserData);
  userId.value = newUserData.id; // Sync ID
  avatarUrl.value = newUserData.avatar_url || '/default-avatar.png'; // ← Add this
};
// Logout
const handleLogout = async () => {
  try {
    const token = localStorage.getItem('token')
    await axios.post('/api/logout', {}, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
    localStorage.removeItem('token')
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout failed:', error)
    localStorage.removeItem('token')
    window.location.href = '/login'
  }
}
// Product management
const products = ref([])
const categories = ref([])
const units = ref([])
const selectedCategory = ref('')
const showForm = ref(false)
const editMode = ref(false)
const editingProductId = ref(null)
const imagePreview = ref(null)
const fileInput = ref(null)
const newProduct = ref({
  name: '',
  description: '',
  price: 0,
  quantity: 0,
  category_id: '',
  unit_id: '',
  image: null
})
// Fetch data functions
const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/farmer/products', {
      headers: getAuthHeaders()
    })
    products.value = res.data
  } catch (err) {
    console.error('Failed to fetch products:', err)
  }
}
const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  }
}
const unitsFetched = ref(false)
const fetchUnits = async () => {
  if (unitsFetched.value) {
    console.log('Units already fetched. Skipping.')
    return
  }
  try {
    const res = await axios.get('/api/units')
    if (Array.isArray(res.data)) {
units.value = res.data
      unitsFetched.value = true
      console.log('Units loaded:', units.value)
    }
  } catch (err) {
    console.error('Failed to fetch units:', err)
  }
}
// Product form handlers
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (!file) return
  newProduct.value.image = file
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}
const submitProduct = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      showNotification('You are not logged in!')
      return
    }
    const formData = new FormData()
    formData.append('name', newProduct.value.name)
    formData.append('description', newProduct.value.description)
    formData.append('price', newProduct.value.price)
    formData.append('quantity', newProduct.value.quantity)
    formData.append('category_id', newProduct.value.category_id)
    formData.append('unit_id', newProduct.value.unit_id)
    if (newProduct.value.image) {
      formData.append('image', newProduct.value.image)
    }
    let response
    if (editMode.value && editingProductId.value) {
      // Update product
      response = await axios.post(
        `/api/farmer/products/${editingProductId.value}?_method=PUT`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        }
      )
    } else {
      // Create product
      response = await axios.post('/api/farmer/products', formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      })
    }
    await fetchProducts()
    resetForm()
  } catch (err) {
    console.error('Failed to submit product:', err.response?.data || err.message)
    showNotification('Error: ' + (err.response?.data?.message || 'Failed to submit product'))
  }
}
const startEdit = (product) => {
  editMode.value = true
  editingProductId.value = product.id
  showForm.value = true
  newProduct.value = {
    name: product.name,
    description: product.description,
    price: product.price,
    quantity: product.quantity,
    category_id: product.category_id,
    unit_id: product.unit_id,
    image: null
  }
  imagePreview.value = product.image_url || null
}
const deleteProduct = async (id) => {
  // Show custom confirmation dialog
  showConfirm('Are you sure you want to delete this product?', async () => {
    try {
      await axios.delete(`/api/farmer/products/${id}`, { headers: getAuthHeaders() });
      showNotification('Product deleted successfully.', 'success');
      await fetchProducts();
    } catch (err) {
      console.error('Failed to delete product:', err);
      showNotification('Failed to delete product.', 'error');
    }
  });
};
const resetForm = () => {
  showForm.value = false
  editMode.value = false
  editingProductId.value = null
  newProduct.value = {
    name: '',
    description: '',
    price: '',
    quantity: '',
    category_id: '',
    unit_id: '',
    image: null
  }
  imagePreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}
const cancelEdit = () => {
  resetForm()
}
const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value
  return products.value.filter(product => product.category_id == selectedCategory.value)
})
const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id == categoryId)
  return category ? category.name : 'Uncategorized'
}
// Dashboard calculations
const calculateMonthlyRevenue = () => {
  const currentMonth = new Date().getMonth()
  const currentYear = new Date().getFullYear()
  const monthlyTotal = sales.value
    .filter(sale => {
      const saleDate = new Date(sale.created_at)
      return saleDate.getMonth() === currentMonth && saleDate.getFullYear() === currentYear
    })
    .reduce((total, sale) => total + parseFloat(sale.total_price || 0), 0)
  return monthlyTotal.toFixed(0)
}
const calculateAverageRating = () => {
  if (!feedback.value || feedback.value.length === 0) return '0.0'
  const totalRating = feedback.value.reduce((sum, item) => sum + (item.rating || 0), 0)
  const average = totalRating / feedback.value.length
  return average.toFixed(1)
}

// Feedback management
const feedback = ref([])
const feedbackLoading = ref(true)
const pagination = ref({})
const replies = reactive({})
const sendingReply = reactive({})
const fetchFeedback = async (page = 1) => {
  feedbackLoading.value = true
  try {
    const res = await axios.get(`/api/feedbacks/farmer?page=${page}`, {
      headers: getAuthHeaders()
    })
    feedback.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
      total: res.data.total
    }
    feedback.value.forEach((fb) => {
      if (!replies[fb.id]) {
        replies[fb.id] = fb.reply || ''
      }
    })
  } catch (err) {
    console.error('Failed to load feedback:', err)
    alert('Failed to load feedback.')
  } finally {
    feedbackLoading.value = false
  }
}
const sendReply = async (id) => {
  if (!replies[id]) return alert('Reply cannot be empty.')
  sendingReply[id] = true
  try {
    await axios.post(
      `/api/reviews/${id}/reply`,
      { reply: replies[id] },
      { headers: getAuthHeaders() }
    )
    alert('Reply sent successfully.')
    fetchFeedback(pagination.value.current_page)
  } catch (err) {
    console.error('Failed to send reply:', err)
    alert('Failed to send reply.')
  } finally {
    sendingReply[id] = false
  }
}
const approveFeedback = async (id) => {
  try {
    await axios.post(
      `/api/reviews/${id}/approve`,
      {},
      { headers: getAuthHeaders() }
    )
    alert('Feedback approved successfully.')
    fetchFeedback(pagination.value.current_page)
  } catch (err) {
    console.error('Failed to approve feedback:', err)
    alert('Failed to approve feedback.')
  }
}
const deleteFeedback = (id) => {
  showConfirm('Are you sure you want to delete this feedback?', async () => {
    try {
      await axios.delete(`/api/reviews/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      });
      showStatus('Feedback deleted successfully.', 'success');
      fetchFeedback(pagination.value.current_page);
    } catch (err) {
      console.error('Failed to delete feedback:', err);
      showStatus('Could not delete feedback.', 'error');
    }
  });
};
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchFeedback(page)
  }
}
// Sales history
const sales = ref([])
const salesLoading = ref(false)
const fetchSales = async () => {
  try {
    salesLoading.value = true
    const res = await axios.get('/api/farmer/sales-history', {
      headers: getAuthHeaders()
    })
    sales.value = res.data
  } catch (error) {
    console.error('Error fetching sales history', error)
  } finally {
    salesLoading.value = false
  }
}
// Utility functions
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}
const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
const getAuthHeaders = () => {
  const token = localStorage.getItem('token')
  return {
    Authorization: `Bearer ${token}`
  }
}
// === NEW: Inline Notification System (No alerts/confirm) ===
const notification = ref({ show: false, message: '', type: 'info' });
const showNotification = (message, type = 'info') => {
  notification.value = { show: true, message, type };
  setTimeout(() => { notification.value.show = false; }, 5000);
};

// Initialize data
onMounted(async () => {
  try {
    await fetchProfile();
    await fetchProducts();
    await fetchCategories();
    await fetchUnits();
    await fetchFeedback();
    await fetchSales();
    await fetchOrders() // ✅ Add this
  } catch (err) {
    console.error('Error during initialization:', err);
  }
});

// --- NEW: Responsive Sidebar State ---
const sidebarHidden = ref(false)

const toggleSidebar = () => {
  sidebarHidden.value = !sidebarHidden.value
}

// You would need to add a button in the template to call toggleSidebar
// For example, add this button inside the dashboard-header:
// <button @click="toggleSidebar" class="hamburger-icon">☰</button>



// ... your existing imports and setup ...

// ... your existing imports and setup ...

const userId = computed(() => authStore.user?.id || null)
const conversationSearchQuery = ref('')
const conversations = ref([])
const currentConversation = ref(null)
const newMessage = ref('')
const loadingConversations = ref(false)
const messagesContainer = ref(null)
const selectedMessageId = ref(null)
const showEmojiPicker = ref(false)
const selectedFile = ref(null)
const replyMessage = ref(null)

// Emoji list
const emojiList = ref(['😀', '😂', '😍', '😎', '😊', '🥰', '😘', '😗', '😙', '😚', '🙂', '🤗', '🤩', '🤔', '🤨', '😐', '😑', '😶', '🙄', '😏', 
  '😣', '😥', '😮', '🤐', '😯', '😪', '😫', '😴', '😌', '😛', '😜', '😝', '🤤', '😒', '😓', '😔', '😕', '🙃', '🤑', '😲', 
  '☹️', '🙁', '😖', '😞', '😟', '😤', '😢', '😭', '😦', '😧', '😨', '😩', '😬', '😰', '😱', '😳', '🤪', '😵', '😡', '😠', 
  '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '✋', '🤚', '🖖', '👋', '🤙', '💪', 
  '❤️', '🧡', '💛', '💚', '💙', , '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', 
  '🔥', '✨', '⭐', '🌟', '💫', '💥', '💦', '💨', '💫', '💯', '💢', '💥', '💫', '💦', '💨', '💫', '💯', '💢', '💥', '💫'])

/* Computed search filter */
const filteredConversations = computed(() => {
  if (!conversationSearchQuery.value.trim()) return conversations.value
  return conversations.value.filter((conv) =>
    getConversationTitle(conv)
      .toLowerCase()
      .includes(conversationSearchQuery.value.toLowerCase())
  )
})

/* Helpers */
function getConversationTitle(conv) {
  const others = conv.participants?.filter((p) => p.id !== userId.value)
  return others?.[0]?.name || conv.chat_name || 'Conversation'
}


function getConversationAvatar(conv) {
  if (!conv || !conv.participants) return getAvatarUrl("0", "User");

  // Get the other person (not the logged-in user)
  const other = conv.participants.find(p => p.id !== userId.value);

  // Must use the REAL participant name + id
  if (other?.id && other?.name) {
    return getAvatarUrl(other.id, other.name);
  }

  // LAST fallback: use conversation name (ONLY if no participants)
  const fallbackName = conv.chat_name || "User";
  const fallbackId = conv.id || Date.now();
  return getAvatarUrl(fallbackId, fallbackName);
}

function getInitials(name) {
  if (!name) return 'U'
  const names = name.split(' ')
  const first = names[0]?.charAt(0) || ''
  const last = names.length > 1 ? names[names.length - 1]?.charAt(0) || '' : ''
  return (first + last).toUpperCase()
}

function getAvatarUrl(userId, name) {
  const colors = ['0ea5e9', '10b981', 'f59e0b', 'ef4444', '8b5cf6', 'ec4899', 'f97316', '6366f1']
  const hash = userId.toString().split('').reduce((a, b) => {
    a = ((a << 5) - a) + b.charCodeAt(0)
    return a & a
  }, 0)
  const color = colors[Math.abs(hash) % colors.length]

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(getInitials(name))}&background=${color}&color=fff&size=128`
}

function getSenderAvatar(message) {
  // First try to get avatar from message sender if available
  if (message.sender_avatar_url) return message.sender_avatar_url
  if (message.sender?.name) return getAvatarUrl(message.sender_id, message.sender.name)
  
  // Fallback to conversation participant avatar
  const conv = currentConversation.value
  if (conv?.participants) {
    const participant = conv.participants.find(p => p.id === message.sender_id)
    if (participant?.avatar_url) {
      return participant.avatar_url
    }
  }
  
  // Fallback to conversation avatar
  return getConversationAvatar(conv)
}
function getLastMessagePreview(conv) {
  const msg = conv.latest_message?.message_text || ''
  return msg.length > 40 ? msg.slice(0, 40) + '...' : msg
}

/* Load conversations */
async function loadConversations() {
  loadingConversations.value = true
  try {
    const res = await axios.get('/api/conversations')
    conversations.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingConversations.value = false
  }
}

/* Select a conversation */
async function selectConversation(conv) {
  // Mark messages as read on the backend
   console.log('Selecting conversation with:', conv)
  console.log('Other user:', conv.participants?.find(p => p.id !== userId.value))
  try {
    await axios.post(`/api/conversations/${conv.id}/read`, {}, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
  } catch (e) {
    console.warn('Failed to mark messages as read:', e);
  }

  // Update local UI: set unread_count to 0
  const existingConv = conversations.value.find(c => c.id === conv.id);
  if (existingConv) {
    existingConv.unread_count = 0;
  }

  // Load messages
  currentConversation.value = {
    id: conv.id,
    participants: conv.participants,
    latest_message: conv.latest_message,
    unread_count: 0, // now read
    messages: []
  };

  try {
    const res = await axios.get(`/api/conversations/${conv.id}/messages`);
    currentConversation.value.messages = res.data;
    await nextTick();
    scrollToBottom();
  } catch (e) {
    console.error(e);
  }
}

// Function to group messages by date and sender
function groupMessagesByDateAndSender(messages) {
  if (!messages || messages.length === 0) return []
  
  const grouped = []
  let currentDateGroup = null
  let currentSenderGroup = null
  
  // Sort messages by timestamp
  const sortedMessages = [...messages].sort((a, b) => 
    new Date(a.created_at) - new Date(b.created_at)
  )
  
  for (let i = 0; i < sortedMessages.length; i++) {
    const currentMessage = sortedMessages[i]
    const messageDate = new Date(currentMessage.created_at).toDateString()
    
    // Check if we need a new date group
    const isNewDate = !currentDateGroup || currentDateGroup.date !== messageDate
    
    if (isNewDate) {
      // Start new date group
      currentDateGroup = {
        date: messageDate,
        formattedDate: formatDateHeader(messageDate),
        senderGroups: []
      }
      grouped.push(currentDateGroup)
      currentSenderGroup = null
    }
    
    // Check if we need a new sender group within the date
    const previousMessage = i > 0 ? sortedMessages[i - 1] : null
    const shouldGroupWithPrevious = previousMessage && 
                                   currentMessage.sender_id === previousMessage.sender_id &&
                                   new Date(currentMessage.created_at).getTime() - new Date(previousMessage.created_at).getTime() < 5 * 60 * 1000
    
    if (!shouldGroupWithPrevious || !currentSenderGroup) {
      // Start new sender group
      currentSenderGroup = {
        sender_id: currentMessage.sender_id,
        sender_name: currentMessage.sender?.name || getConversationTitle(currentConversation.value),
        avatar_url: getSenderAvatar(currentMessage),
        messages: [currentMessage]
      }
      currentDateGroup.senderGroups.push(currentSenderGroup)
    } else {
      // Add to existing sender group
      currentSenderGroup.messages.push(currentMessage)
    }
  }
  
  return grouped
}

// Helper function to format date header
function formatDateHeader(dateString) {
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  
  const messageDate = new Date(dateString)
  
  if (messageDate.toDateString() === today.toDateString()) {
    return 'Today'
  } else if (messageDate.toDateString() === yesterday.toDateString()) {
    return 'Yesterday'
  } else {
    // Format as "Monday, November 21"
    return new Date(dateString).toLocaleDateString('en-US', {
      weekday: 'long',
      month: 'long',
      day: 'numeric'
    })
  }
}

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedFile.value) || !currentConversation.value) return
  
  const tempMessageText = newMessage.value.trim()
  const msg = {
    id: Date.now(),
    message_text: tempMessageText,
    sender_id: userId.value,
    created_at: new Date().toISOString(),
    attachment_url: selectedFile.value ? URL.createObjectURL(selectedFile.value) : null
  }
  
  currentConversation.value.messages.push(msg)
  console.log('👉 Sending message from:', msg.sender_id)
  console.log('👉 Current user ID:', userId.value)
  console.log('👉 Are they equal?', msg.sender_id === userId.value)
  console.log('👉 Message object:', msg)
  
  // Reset input
  newMessage.value = ''
  selectedFile.value = null
  
  try {
    const token = localStorage.getItem('token')
    const formData = new FormData()
    formData.append('conversation_id', currentConversation.value.id)
    formData.append('message_text', tempMessageText)
    
    if (selectedFile.value) {
      formData.append('attachment', selectedFile.value)
    }
    
    const res = await axios.post(
      '/api/messages',
      formData,
      { 
        headers: { 
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        } 
      }
    )
    Object.assign(msg, res.data)
    await loadConversations()
    scrollToBottom()
  } catch (e) {
    console.error('Failed to send message', e)
  }
}

function scrollToBottom() {
  nextTick(() => {
    const el = messagesContainer.value
    if (el) el.scrollTop = el.scrollHeight
  })
}

/* Enhanced message functions */
function toggleMessageOptions(messageId) {
  selectedMessageId.value = selectedMessageId.value === messageId ? null : messageId
}

function replyToMessage(message) {
  // Get the sender name from the conversation participants
  const conv = currentConversation.value
  let senderName = getConversationTitle(conv)
  
  // Try to get the actual sender name from the message
  if (message.sender?.name) {
    senderName = message.sender.name
  } else if (conv?.participants) {
    const participant = conv.participants.find(p => p.id === message.sender_id)
    if (participant?.name) {
      senderName = participant.name
    }
  }
  
  // Set the reply message to show above input
  replyMessage.value = {
    text: message.message_text || message.message,
    sender: senderName
  }
  selectedMessageId.value = null
  // Focus on input after setting reply
  nextTick(() => {
    const input = document.querySelector('.input-row input')
    if (input) input.focus()
  })
}

function cancelReply() {
  replyMessage.value = null
}

function forwardMessage(message) {
  alert(`Forwarding: ${message.message_text || message.message}`)
  selectedMessageId.value = null
}


function deleteMessage(message) {
  // ✅ Check if message belongs to current user
  const currentUserId = authStore.user?.id
 

  // ✅ Use your custom confirmation (not browser confirm)
  showConfirm('Are you sure you want to delete this message?', async () => {
    try {
      // ✅ Delete from backend
      await axios.delete(`/api/messages/${message.id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      })

      // ✅ Delete from frontend
      currentConversation.value.messages = currentConversation.value.messages.filter(
        msg => msg.id !== message.id
      )

      // ✅ Refresh conversations list (updates latest message, unread count)
      await loadConversations()

      selectedMessageId.value = null
      showStatus('Message deleted successfully', 'success')
    } catch (e) {
      console.error('Delete failed:', e)
      showStatus('Failed to delete message', 'error')
    }
  })
}


function openFilePicker() {
  fileInput.value.click()
}

function handleFileSelect(event) {
  const files = event.target.files
  if (files.length > 0) {
    selectedFile.value = files[0]
    // You can implement file upload logic here
  }
}

// Add this function to handle clicks outside the options menu
function handleClickOutside(event) {
    const replyIndicator = event.target.closest('.reply-indicator')
  const optionsMenu = event.target.closest('.options-menu')
  const optionsButton = event.target.closest('.message-options')
  
  // Only close if the click is outside both the menu and the button
  if (!optionsMenu && !optionsButton) {
    selectedMessageId.value = null
  }
}


// Update onMounted
onMounted(() => {
  loadConversations()
  document.addEventListener('click', handleClickOutside)
})



// Add onUnmounted to clean up
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

function toggleEmojiPicker() {
  showEmojiPicker.value = !showEmojiPicker.value
}

function addEmoji(emoji) {
  newMessage.value += emoji
  showEmojiPicker.value = false
}

function isImage(url) {
  return /\.(jpeg|jpg|gif|png|webp)$/i.test(url)
}

function getFileName(url) {
  return url.split('/').pop()
}

/* Group messages by sender for display */
function groupMessagesBySender(messages) {
  if (!messages || messages.length === 0) return []
  
  const grouped = []
  let currentGroup = null
  
  for (let i = 0; i < messages.length; i++) {
    const currentMessage = messages[i]
    const previousMessage = i > 0 ? messages[i - 1] : null
    
    // Check if this message should be grouped with the previous one
    const shouldGroup = previousMessage && 
                       currentMessage.sender_id === previousMessage.sender_id &&
                       // Group if messages are within 5 minutes of each other
                       new Date(currentMessage.created_at).getTime() - new Date(previousMessage.created_at).getTime() < 5 * 60 * 1000
    
    if (shouldGroup && currentGroup) {
      // Add to existing group
      currentGroup.messages.push(currentMessage)
    } else {
      // Start new group
      currentGroup = {
        sender_id: currentMessage.sender_id,
        sender_name: currentMessage.sender?.name || getConversationTitle(currentConversation.value),
        avatar_url: getSenderAvatar(currentMessage),
        messages: [currentMessage]
      }
      grouped.push(currentGroup)
    }
  }
  
  return grouped
}

/* Watch for new messages */
watch(
  () => currentConversation.value?.messages?.length,
  () => scrollToBottom()
)

onMounted(() => {
  loadConversations()
})

</script>

<style scoped>
/* Global Reset & Box Sizing */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* App Container */
.app-container {
  display: flex;
  min-height: 100vh;
  background: rgba(30, 41, 59, 0.95);
  font-family: 'Segoe UI', sans-serif;
  color: white;
  position: relative;
}

/* Sidebar */
.sidebar {
  width: 250px;
  height: 100vh; /* Changed from calc(100vh - 72px) */
  position: fixed; /* Fixed positioning */
  top: 0;
  left: 0;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(10px);
  color: white;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  overflow-y: auto;
  transition: transform 0.3s ease; /* Smooth transition for hiding */
}

.sidebar h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #10b981;
  text-align: center;
}

.sidebar nav ul {
  list-style: none;
}

.sidebar nav li {
  padding: 0.75rem 1rem;
  margin: 0.5rem 0;
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.3s ease;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sidebar nav li:hover {
  background: rgba(255, 255, 255, 0.1);
}

.sidebar nav li.active {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.badge {
  background: #ef4444;
  color: white;
  border-radius: 999px;
  padding: 0.25rem 0.6rem;
  font-size: 0.75rem;
  margin-left: auto;
}

/* Hide sidebar on small screens */
.sidebar.sidebar-hidden {
  transform: translateX(-100%);
}

/* Main Wrapper */
.main-wrapper {
  flex: 1;
  /* margin-left is now controlled by the sidebar-hidden class via inline style */
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left 0.3s ease; /* Smooth transition for main content */
}

/* Dashboard Header */
.dashboard-header {
  position: sticky; /* Changed from fixed */
  top: 0; /* Stick to the top */
  left: 0;
  right: 0;
  height: 100px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 1.7rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 0px;
  border-bottom: 4px solid rgba(255, 255, 255, 0.2);
  z-index: 400; /* Lower z-index than sidebar to avoid overlap */
  color: white;
  transition: margin-left 0.3s ease; /* Smooth transition when sidebar toggles */
}

.hamburger-icon {
  display: flex; /* Hidden by default, shown on mobile */
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  margin-right: 0rem;
}

.greeting h2 {
  margin: 0;
  font-size: 1.9rem;
  color: white;
  font-weight: 600;
}

.greeting p {
  margin: 0rem 0 0 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.3rem;
}

.user-profile {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  border: 3px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.user-profile:hover {
  border-color: #10b981;
  transform: scale(1.05);
}

.profile-picture {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-picture.placeholder {
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 2rem;
  padding-top: 25px; 
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  overflow-y: auto;
  color: white;
  transition: margin-left 0.3s ease; /* Smooth transition when sidebar toggles */
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  background: rgba(255, 255, 255, 0.15);
}

.stat-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Content Section */
.content-section {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  margin-bottom: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.section-title {
  color: white;
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

/* Quick Actions */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  padding: 1rem 1.5rem;
  border-radius: 15px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.activity-icon {
  font-size: 1.5rem;
  width: 40px;
  text-align: center;
}

.activity-content {
  flex: 1;
}

.activity-content p {
  margin: 0;
  color: white;
  font-weight: 500;
}

.activity-content span {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

.activity-value {
  color: #10b981;
  font-weight: bold;
  font-size: 1.1rem;
}

.empty-activity {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 2rem;
  font-style: italic;
}

/* Farmer Listings */
.farmer-listings {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header h2 {
  color: white;
  font-size: 1.5rem;
  margin: 0;
}

.btn-primary {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.filter-controls {
  margin-bottom: 2rem;
}

.filter-controls select {
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-size: 1rem;
  min-width: 200px;
}

.filter-controls select option {
  background: #1e293b;
  color: white;
}

/* Form */
.form {
  background: rgba(255, 255, 255, 0.05);
  padding: 2rem;
  border-radius: 15px;
  margin-bottom: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: white;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.2);
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.4);
}

.image-preview {
  margin-top: 1rem;
  position: relative;
}

.image-preview img {
  max-width: 200px;
  max-height: 200px;
  border-radius: 12px;
  object-fit: cover;
}

.image-preview button {
  position: absolute;
  top: 5px;
  right: 5px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Product Grid */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.product-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

.product-image-container {
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.1);
}

.product-image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.5);
  font-style: italic;
}

.product-info {
  padding: 1rem;
}

.product-info h3 {
  color: white;
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
}

.product-info p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.4;
}

.product-meta-beautified {
  padding: 0 1rem 1rem 1rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  color: white;
  font-size: 0.9rem;
}

.meta-item strong {
  color: #10b981;
}

.category-tag {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  display: inline-block;
  margin: 0.5rem 0;
}

.out-of-stock {
  color: #ef4444 !important;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-danger:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

.empty-state {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 3rem;
  font-style: italic;
  font-size: 1.1rem;
}

/* View Feedback */
.view-feedback h2 {
  color: white;
  margin-bottom: 2rem;
}

/* Feedback Cards Grid */
.feedback-cards-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); /* Responsive grid */
  margin-top: 16px;
}

/* Feedback Card */
.feedback-card {
  background: rgba(46, 50, 77, 0.76);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #eaeaea;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.feedback-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

/* Card Header */
.card-header {
  padding: 16px 20px;
  background: #3a3f5dff;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.user-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #f6f6f7ff;
  font-size: 14px;
}

.avatar {
  width: 32px;
  height: 32px;
  background: #3498db;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
}

.product-tag {
  background: #f7f6f6ff;
  color: #303c6aff;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

/* Card Body */
.card-body {
  padding: 20px;
  color: #444;
  line-height: 1.6;
}

/* Rating */
.rating-stars {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 18px;
  margin-bottom: 10px;
}

.rating-stars .filled {
  color: #ffc107;
}

.rating-stars small {
  color: #c3d013ff;
  margin-left: 8px;
  font-size: 12px;
}

/* Comment */
.comment {
  font-style: italic;
  color: #0c0c0cff;
  margin: 12px 0;
  padding: 14px;
  background: #f1f5f9;
  border-left: 3px solid #3498db;
  border-radius: 6px;
  font-size: 14px;
  line-height: 1.5;
}

/* Meta Info */
.meta-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #fefbfbff;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.approved {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.pending {
  background: #fff8e1;
  color: #9c6b00;
}

/* Reply Section */
.reply-section {
  margin: 16px 0;
}

.reply-box {
  background: #e8f5e8;
  border: 1px solid #c8e6c9;
  padding: 12px;
  border-radius: 10px;
  font-size: 14px;
  color: #10abdeff;
}

.reply-text {
  margin: 6px 0 0;
  font-style: italic;
}

.reply-form textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  resize: vertical;
  font-family: inherit;
  font-size: 14px;
  margin-bottom: 8px;
  background: #fafafa;
}

.reply-form textarea:focus {
  outline: none;
  border-color: #3498db;
  background: white;
}

/* Buttons */
.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn.primary {
  background: #3498db;
  color: white;
}

.btn.primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn.outline.approve {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.btn.outline.approve:hover {
  background: #c8e6c9;
}

.btn.danger {
  background: #e53935;
  color: white;
}

.btn.danger:hover {
  background: #c62828;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Card Footer */
.card-footer {
  padding: 0 20px 20px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #aaa;
  font-size: 16px;
}

.empty-icon {
  opacity: 0.3;
  margin-bottom: 12px;
  max-width: 80px;
}

/* Pagination */
.pagination-controls {
  margin-top: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  font-size: 14px;
  color: #555;
}

.pagination-controls .page-info {
  font-weight: 500;
}

.pagination-controls .btn.pagination-btn {
  background: #f1f1f1;
  color: #333;
}

.pagination-controls .btn.pagination-btn:hover:not(:disabled) {
  background: #e0e0e0;
}
/* Orders Section - Minimal Design */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  padding: 1rem 0;
}

.order-card {
  padding: 1.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.order-card:last-child {
  border-bottom: none;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.order-id {
  color: white;
  font-size: 1.1rem;
}

.order-status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.order-status.pending { background: #f59e0b; color: white; }
.order-status.paid { background: #10b981; color: white; }
.order-status.shipped { background: #0ea5e9; color: white; }
.order-status.delivered { background: #16a34a; color: white; }
.order-status.cancelled { background: #ef4444; color: white; }

.order-customer {
  margin: 0.5rem 0 1rem 0;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.5;
}

.order-customer strong {
  color: #10b981;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin: 1rem 0;
}

.order-item {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.item-image {
  width: 50px;
  height: 50px;
  border-radius: 6px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.item-details h4 {
  margin: 0 0 0.1rem 0;
  color: white;
  font-size: 1rem;
}

.item-details p {
  margin: 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.order-summary {
  display: flex;
  justify-content: space-between;
  margin: 1rem 0;
  padding: 0.5rem 0;
  font-size: 0.95rem;
  color: white;
  font-weight: 600;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.farmer-instructions {
  margin: 1rem 0 1.2rem 0;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
}

.farmer-instructions h4 {
  margin: 0 0 0.5rem 0;
  color: #10b981;
  font-size: 1rem;
}

.farmer-instructions ol {
  margin: 0.3rem 0 0 0;
  padding-left: 1.2rem;
}

.farmer-instructions li {
  margin-bottom: 0.3rem;
  line-height: 1.4;
}

.order-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-success {
  background: linear-gradient(135deg, #16a34a, #15803d);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-success:hover {
  background: #145f2f;
}

/* Sales History - Minimal */
.sales-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.2rem;
}

.sale-card {
  padding: 1.2rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sale-card:last-child {
  border-bottom: none;
}

.sale-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.8rem;
  align-items: flex-start;
}

.sale-product-image {
  width: 60px;
  height: 60px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.sale-details h3 {
  margin: 0 0 0.2rem 0;
  font-size: 1.1rem;
  color: white;
}

.sale-details p {
  margin: 0;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
}

.sale-details p.farmer {
  color: #a8f7c5;
}

.sale-details p.category {
  color: #a0d8f1;
  font-size: 0.85rem;
}

.sale-stats {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0.5rem;
  margin: 0.8rem 0;
  text-align: center;
}

.stat .label {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.1rem;
}

.stat .value {
  font-weight: 700;
  color: #10b981;
  font-size: 1rem;
}

.sale-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: 0.8rem;
}




/* MESSAGING LAYOUT */
.messaging-section {
  width: 100%;
  height: calc(95vh - 120px);
  background: rgba(30, 41, 59, 1);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  color: #f1f5f9;
  padding: 10px;
}

.messaging-container {
  display: flex;
  width: 100%;
  height: 85vh;
  background: #1e293b;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  margin-top: 60px;
}

/* LEFT SIDEBAR */
.conversation-list {
  width: 20%;
  background: #273349;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #e2e8f0;
}

.conversation-header {
  padding: 16px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  flex-shrink: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.conversation-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #f1f5f9;
}

.search-box {
  width: 100%;
  padding: 12px 16px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(51, 65, 85, 0.7);
  color: #e2e8f0;
  font-size: 0.9rem;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.search-box::placeholder {
  color: #94a3b8;
}

.search-box:focus {
  outline: none;
  border-color: rgba(34, 197, 94, 0.5);
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}

.conversation-items {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
  flex: 1;
  scrollbar-width: thin;
}

.conversation {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.2s ease;
  position: relative;
}

.conversation:hover {
  background: rgba(255, 255, 255, 0.05);
}

.conversation.active {
  background: rgba(34, 197, 94, 0.2);
  border-left: 3px solid #22c55e;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.info {
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.info .top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 3px;
}

.info .top strong {
  font-size: 0.95rem;
  font-weight: 600;
  color: #f1f5f9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.info .top small {
  font-size: 0.75rem;
  color: #94a3b8;
  flex-shrink: 0;
}

.last {
  font-size: 0.8rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 2px;
}

.badge {
  background: #22c55e;
  color: white;
  font-size: 0.7rem;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
  flex-shrink: 0;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
/* CHAT AREA */
.chat-area {
  width: 80%;
  display: flex;
  flex-direction: column;
  background: #0f172a;
  height: 100%;
  position: relative;
  color: #f1f5f9;
}

/* HEADER - FIXED AT TOP */
.chat-header {
  display: flex;
  align-items: center;
  gap: 14px;
  background: rgba(51, 65, 85, 0.7);
  padding: 14px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  flex-shrink: 0;
  position: sticky;
  top: 0;
  z-index: 10;
  backdrop-filter: blur(10px);
}

.chat-header .avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.chat-header h4 {
  color: #f1f5f9;
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-header small {
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 500;
}

/* MESSAGES AREA - ONLY SCROLLABLE PART */
.messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
  background: #0f172a;
  display: flex;
  flex-direction: column;
  gap: 12px;
  scroll-behavior: smooth;
  max-height: calc(100vh - 300px);
  position: relative;
  z-index: 5;
}

/* DATE HEADER STYLING — CLEAN, MODERN, CENTERED */
.date-group {
  margin-bottom: 20px;
}

.date-header {
  text-align: center;
  margin: 18px 0 12px;
  position: relative;
  width: 100%;
}

.date-header::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1), transparent);
  z-index: 1;
}

.date-header span {
  background-color: #1e293b;
  padding: 8px 20px;
  border-radius: 16px;
  font-size: 0.85rem;
  color: #94a3b8;
  font-weight: 500;
  position: relative;
  z-index: 2;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

/* MESSAGE GROUPS */
.message-group {
  display: flex;
  margin-bottom: 8px;
  align-items: flex-start;
  position: relative;
}

.sent-group {
  justify-content: flex-end;
}

.received-group {
  justify-content: flex-start;
}

.avatar-container {
  margin-right: 10px;
  flex-shrink: 0;
  display: flex;
  align-items: flex-start;
  margin-top: 4px;
}

.messages-bubble {
  display: flex;
  flex-direction: column;
  max-width: 70%;
  gap: 4px;
}

.grouped-message {
  margin-bottom: 2px;
  position: relative;
}

.grouped-message:last-child {
  margin-bottom: 0;
}

.grouped-message .bubble {
  max-width: 100%;
  margin-bottom: 0;
  position: relative;
}

.message.sent .bubble {
  background: #22c55e;
  color: #fff;
  border-radius: 16px 16px 4px 16px;
  padding: 12px 16px;
  box-shadow: 0 2px 6px rgba(34, 197, 94, 0.2);
  font-size: 0.95rem;
  line-height: 1.4;
  word-wrap: break-word;
}

.message.received .bubble {
  background: #334155;
  color: #f1f5f9;
  border-radius: 16px 16px 16px 4px;
  padding: 12px 16px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  font-size: 0.95rem;
  line-height: 1.4;
  word-wrap: break-word;
}

/* Timestamp */
.timestamp {
  display: block;
  text-align: right;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 6px;
  font-weight: 500;
}

.message.sent .timestamp {
  color: rgba(255, 255, 255, 0.7);
}

.message.received .timestamp {
  color: #94a3b8;
}

/* Message Content */
.message-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.attachment-preview {
  margin-top: 8px;
  max-width: 100%;
}

.attached-image {
  max-width: 180px;
  max-height: 180px;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.file-attachment {
  background: #1e293b;
  color: #94a3b8;
  padding: 8px 12px;
  border-radius: 12px;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

/* Avatar for received messages */
.message.received img.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
  margin-right: 8px;
  margin-top: 4px;
}

/* Message Options (Three Dots) */
.message-options {
  position: absolute;
  top: 8px;
  right: 8px;
  cursor: pointer;
  font-size: 18px;
  opacity: 0;
  transition: opacity 0.2s ease;
  color: #94a3b8;
  z-index: 20;
  background: none;
  border: none;
  padding: 0;
  margin: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.message:hover .message-options {
  opacity: 1;
}

.message-options:hover {
  opacity: 1 !important;
  background-color: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

.options-menu {
  position: absolute;
  top: 28px;
  right: 0;
  background: #1e293b;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  z-index: 1000;
  min-width: 140px;
  overflow: hidden;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

/* Show menu when message option is clicked */
.message-options.active + .options-menu,
.options-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
  display: block !important;
}

.options-menu button {
  display: block;
  width: 100%;
  padding: 12px 16px;
  border: none;
  background: transparent;
  text-align: left;
  cursor: pointer;
  font-size: 0.9rem;
  color: #e2e8f0;
  transition: background 0.2s;
  border-radius: 0;
  font-weight: 500;
}

.options-menu button:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #f1f5f9;
}

.options-menu button:first-child {
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}

.options-menu button:last-child {
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}

/* Ensure bubble has relative positioning */
.bubble {
  position: relative;
  max-width: 100%;
  margin-bottom: 0;
}
/* Reply indicator (shows above input when replying) */
.reply-indicator {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  backdrop-filter: blur(10px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: fixed;
  bottom: 60px; /* Position above input row */
  left: 24px;
  right: 24px;
  z-index: 15;
  max-width: calc(100% - 48px);
  margin: 0 24px 8px 24px;
  max-height: 80px;
  overflow: hidden;
}

.reply-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: #22c55e;
  font-weight: 500;
}

.reply-sender {
  font-style: italic;
}

.cancel-reply {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #94a3b8;
  padding: 0 6px;
  border-radius: 8px;
  transition: all 0.2s;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cancel-reply:hover {
  color: #f1f5f9;
  background-color: rgba(255, 255, 255, 0.08);
}

.reply-content {
  font-size: 0.9rem;
  color: #e2e8f0;
  padding: 4px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  margin-top: 2px;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}



/* INPUT ROW - FIXED AT BOTTOM */
.input-row {
  display: flex;
  align-items: center;
  padding: 12px 24px;
  background: rgba(51, 65, 85, 0.7);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  position: sticky;
  bottom: 0;
  z-index: 10;
  flex-shrink: 0;
  backdrop-filter: blur(10px);
}

.input-row input {
  flex: 1;
  padding: 14px 20px;
  border-radius: 24px;
  border: none;
  outline: none;
  background: #334155;
  color: #f1f5f9;
  font-size: 0.95rem;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.input-row input::placeholder {
  color: #94a3b8;
}

.input-row input:focus {
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
}

.input-row button {
  background: #22c55e;
  color: white;
  border: none;
  border-radius: 24px;
  padding: 12px 20px;
  margin-left: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  transition: background 0.2s, transform 0.1s;
  box-shadow: 0 2px 6px rgba(34, 197, 94, 0.3);
}

.input-row button:hover {
  background: #16a34a;
  transform: translateY(-1px);
}

.input-row button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  background: #4a5568;
}

/* Placeholder */
.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #94a3b8;
  font-style: italic;
  font-size: 1rem;
}

/* Emoji Picker */
.emoji-picker {
  position: absolute;
  bottom: 100px;
  left: 24px;
  background: #1e293b;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 14px;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 10px;
  max-width: 240px;
  max-height: 180px;
  overflow-y: auto;
  z-index: 1000;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.emoji-option {
  text-align: center;
  cursor: pointer;
  font-size: 22px;
  padding: 6px;
  border-radius: 12px;
  transition: background 0.2s;
}

.emoji-option:hover {
  background: rgba(255, 255, 255, 0.08);
}

/* File Picker Button */
.input-tools button {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #94a3b8;
  padding: 8px;
  border-radius: 12px;
  transition: background 0.2s;
}

.input-tools button:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #f1f5f9;
}

/* Scrollbar Styling */
.messages::-webkit-scrollbar,
.conversation-items::-webkit-scrollbar {
  width: 6px;
}

.messages::-webkit-scrollbar-thumb,
.conversation-items::-webkit-scrollbar-thumb {
  background: #4a5568;
  border-radius: 10px;
}

.messages::-webkit-scrollbar-track,
.conversation-items::-webkit-scrollbar-track {
  background: #1e293b;
}

.messages::-webkit-scrollbar-thumb:hover,
.conversation-items::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* General Scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #1e293b;
}

::-webkit-scrollbar-thumb {
  background: #4a5568;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* Message alignment fixes */
.message.sent {
  align-self: flex-end !important;
  text-align: right;
}

.message.received {
  align-self: flex-start !important;
  text-align: left;
}
/* Message Grouping */
.message-group {
  display: flex;
  margin-bottom: 8px;
  align-items: flex-start;
  position: relative;
}

.sent-group {
  justify-content: flex-end;
}

.received-group {
  justify-content: flex-start;
}

.avatar-container {
  margin-right: 12px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.avatar-container .avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.messages-bubble {
  display: flex;
  flex-direction: column;
  max-width: 70%;
  gap: 2px;
}

.grouped-message {
  margin-bottom: 2px;
  position: relative;
}

.grouped-message:last-child {
  margin-bottom: 0;
}

.bubble {
  position: relative;
  max-width: 100%;
  margin-bottom: 0;
}

.message.sent .bubble {
  background: #22c55e;
  color: #fff;
  border-radius: 16px 16px 4px 16px;
  padding: 12px 16px;
  font-size: 0.95rem;
  line-height: 1.4;
}

.message.received .bubble {
  background: #334155;
  color: #f1f5f9;
  border-radius: 16px 16px 16px 4px;
  padding: 12px 16px;
  font-size: 0.95rem;
  line-height: 1.4;
}

.timestamp {
  display: block;
  text-align: right;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 6px;
  font-weight: 500;
}
.message-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
}

.read-status {
  font-size: 12px;
  line-height: 1;
}

.gray-ticks {
  color: #94a3b8;
}

.blue-ticks {
  color: #22c55e; /* or #34b7f1 for WhatsApp-style blue */
  font-weight: bold;
}
.message.received .timestamp {
  color: #94a3b8;
  text-align: left;
}
/* Confirmation Overlay & Modal */
.confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
}

.confirm-box {
  background: #fff;
  padding: 10px;
  border-radius: 10px;
  text-align: center;
  max-width: 400px;
  width: 90%;
  color: #333;
}

.confirm-box .actions {
  margin-top: 15px;
  display: flex;
  justify-content: center;
  gap: 10px;
}

.status-message {
  position: fixed;
  bottom: 20px; right: 20px;
  padding: 10px 15px;
  border-radius: 6px;
  font-weight: bold;
  z-index: 20;
}

.status-message.success { background: #d4edda; color: #155724; }
.status-message.error { background: #f8d7da; color: #721c24; }

.confirm-modal {
  background: white;
  color: #333;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0);
  text-align: center;
  max-width: 400px;
  width: 90%;
}

.confirm-modal p {
  margin-bottom: 40px;
  font-size: 1rem;
  line-height: 0.5;
}

.confirm-buttons {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.btn-confirm {
  background: #1bf217c6;
  color: white;
  border: none;
  padding: 8px 8px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-cancel {
  background: #e71a1aff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-confirm:hover {
  background: #2ce40bff;
}

.btn-cancel:hover {
  background: #fd0808ff;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .hamburger-icon {
    display: block; /* Show hamburger on mobile */
  }

  .sidebar {
    top: 0;
    height: 100vh;
    z-index: 1001; /* Higher than main content */
  }

  .sidebar.sidebar-hidden {
    transform: translateX(-100%);
  }

  .main-wrapper {
    margin-left: 0;
  }

  .dashboard-header {
    position: sticky; /* Ensure sticky behavior on mobile */
    margin-left: 0;
  }

  .main-content {
    padding: 1.5rem; /* Reduced padding */
    padding-top: 105px; /* Adjusted for sticky header */
  }

  .stats-grid,
  .quick-actions,
  .product-grid,
  .sales-grid,
  .feedback-cards-grid {
    grid-template-columns: 1fr; /* Single column on small screens */
  }

  .form-row {
    grid-template-columns: 1fr; /* Single column form */
  }

  .order-card {
    padding: 1rem; /* Reduced padding */
  }

  .order-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .item-image {
    width: 50px;
    height: 50px;
  }

  .order-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-primary,
  .btn-secondary,
  .btn-success {
    width: 100%; /* Full width buttons */
  }

  /* Messaging Section Adjustments */
  .messaging-container {
    flex-direction: column; /* Stack sidebar and chat vertically */
    max-height: calc(100vh - 120px); /* Adjust height calculation */
  }

  .conversation-list {
    width: 100%; /* Full width when stacked */
    max-width: none;
    height: auto; /* Auto height when stacked */
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Separator line */
  }

  .conversation-items {
    max-height: 200px; /* Limit height on smaller screens when stacked */
  }

  .chat-area {
    height: 100%; /* Take full height when stacked */
  }

  .input-row {
    padding: 8px 12px; /* Reduce input padding */
  }

  .input-row input {
    padding: 8px; /* Smaller input padding */
  }

  .input-row button {
    padding: 8px 12px; /* Smaller button padding */
  }
}

@media (max-width: 480px) {
  .greeting h2 {
    font-size: 1.5rem; /* Smaller title */
  }

  .greeting p {
    font-size: 0.9rem; /* Smaller subtitle */
  }

  .user-profile {
    width: 50px; /* Smaller profile pic */
    height: 50px;
  }

  .main-content {
    padding: 1rem; /* Further reduced padding */
    padding-top: 95px; /* Adjusted for smaller header */
  }

  .stat-card {
    padding: 1.5rem; /* Smaller cards */
  }

  .stat-icon {
    font-size: 2rem; /* Smaller icons */
  }

  .stat-number {
    font-size: 1.5rem; /* Smaller numbers */
  }

  .content-section {
    padding: 1.5rem; /* Smaller sections */
  }

  .action-btn {
    padding: 0.75rem 1rem; /* Smaller buttons */
  }

  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .activity-icon {
    width: auto; /* Allow icon to fit */
  }

  .product-card {
    flex-direction: column; /* Stack image and info */
  }

  .product-image-container {
    height: 150px; /* Smaller image */
  }

  .product-actions {
    flex-direction: column; /* Stack action buttons */
    width: 100%;
  }

  .btn-secondary,
  .btn-danger {
    width: 100%; /* Full width action buttons */
  }

  .feedback-card {
    padding: 1rem; /* Smaller cards */
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .order-summary {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }

  .sale-card {
    padding: 1rem; /* Smaller cards */
  }

  .sale-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .sale-product-image {
    width: 60px; /* Smaller image */
    height: 60px;
  }

  .sale-stats {
    grid-template-columns: 1fr; /* Single column stats */
  }

  /* Messaging Section Adjustments */
  .messaging-section {
    padding: 5px; /* Reduce section padding */
  }

  .conversation-header,
  .chat-header {
    padding: 8px 10px; /* Reduce header padding */
  }

  .messages {
    padding: 10px; /* Reduce message padding */
  }

  .message {
    max-width: 85%; /* Wider messages on very small screens */
  }

  .input-row {
    padding: 6px 10px; /* Further reduce input padding */
  }

  .input-row input {
    padding: 6px; /* Smaller input padding */
  }

  .input-row button {
    padding: 6px 10px; /* Smaller button padding */
  }
}

/* Form Improvements (Responsive) */
.form {
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem; /* Slightly smaller padding */
  border-radius: 15px;
  margin-bottom: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  color: rgba(11, 11, 11, 0.9);
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.95rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 1);
  color: rgba(14, 14, 14, 1);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.image-preview {
  margin-top: 1rem;
}

.image-preview img {
  max-width: 200px;
  max-height: 200px;
  border-radius: 12px;
  object-fit: cover;
}

.image-preview button {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}
</style>