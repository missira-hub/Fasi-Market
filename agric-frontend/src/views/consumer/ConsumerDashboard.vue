<template>  
  <div class="consumer-app">
<header class="header">
  <div class="dashboard-header">
    <!-- Centered Greeting (now properly centered) -->
    <div class="greeting-center">
      <h2>Hello, {{ firstName || 'User' }}</h2>
      <p>Welcome back to your dashboard</p>
    </div>

    <!-- Avatar with Settings Toggle (unchanged) -->
    <div class="settings-wrapper" @click.stop="toggleSettings">
      <img
        v-if="profilePictureUrl"
        :src="profilePictureUrl"
        alt="Profile"
        class="profile-avatar"
      />
      <!-- Use an img tag for the default avatar -->
      <img
        v-else
        :src="defaultAvatar"
        alt="Default Profile"
        class="profile-avatar"
      />      
      <!-- Settings Dropdown -->
      <div v-if="settingsOpen" class="settings-menu">
        <ul>
          <li @click.stop="openProfile">Profile</li>
          <li @click.stop="handleLogout">Logout</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Profile Modal -->
  <ProfileModal
    v-if="profileModalOpen"
    @close="closeProfile"
    @updated="onProfileUpdated"
  />
</header>
  
    <!-- Error Alert -->
    <div v-if="globalError" class="error-alert">
      <svg class="error-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
      <span>{{ globalError }}</span>
      <button @click="globalError = null" class="close-error">×</button>
    </div>

    <!-- Tab Navigation -->
    <nav class="bottom-nav">


      <div @click="switchSection('market')" :class="{ active: section === 'market' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <span>Market</span>
      </div>
       
      <div @click="switchSection('cart')" :class="{ active: section === 'cart' }" class="nav-item">
  <div class="cart-tab-icon">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L5 3H3m4 10v6a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-6"></path>
    </svg>
    <!-- Cart badge -->
    <span v-if="cartItemCount > 0" class="cart-badge">{{ cartItemCount }}</span>
  </div>
  <span>Cart</span>
</div>
      <div @click="switchSection('orders')" :class="{ active: section === 'orders' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
          <path d="M3.3 7l8.7 5 8.7-5"></path>
          <path d="M12 22V12"></path>
        </svg>
        <span>Orders</span>
      </div>
      <div @click="switchSection('messages')" :class="{ active: section === 'messages' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span>Messages</span>
      </div>
      <div @click="switchSection('feedback')" :class="{ active: section === 'feedback' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
        </svg>
        <span>Feedback</span>
      </div>
      <div @click="switchSection('payment')" :class="{ active: section === 'payment' }" class="nav-item">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M12 1v22M17 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2z"></path>
  </svg>
  <span>Payment</span>
</div>

    </nav>
<main class="main-view">
  <!-- Loading Spinner -->
<div v-if="loading" class="loading-spinner">
  <div class="spinner"></div>
  <p>Loading...</p>
</div>

<!-- ✅ CUSTOM CONFIRMATION MODAL (ADD THIS BLOCK) -->
<div v-if="confirmVisible" class="custom-confirm-overlay">
  <div class="custom-confirm-modal">
    <p>{{ confirmMessage }}</p>
    <div class="confirm-buttons">
      <button @click="cancelConfirm" class="btn-cancel">Cancel</button>
      <button @click="handleConfirm" class="btn-confirm">Confirm</button>
    </div>
  </div>
</div>


<!-- MESSAGES SECTION -->
<section v-if="section === 'messages'" class="messaging-section">
   <div class="messaging-container">
    <!-- Mobile Back Button (always shown in messages on mobile) -->
    <button
      v-if="isMobile"
      @click="goBackFromMessages"
      class="mobile-section-back-button"
    >
      ← Back
    </button>
    <!-- Conversations Sidebar -->
<div class="conversation-list" :class="{ 'mobile-hidden': currentConversation }">
    <div class="conversation-header">
  <h3 v-if="!currentConversation">Messages</h3>

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
          <!-- Use new helper for avatar -->
          <img :src="getConversationAvatar(conv)" class="avatar" />
          <div class="info">
  <div class="top">
    <strong>{{ getConversationTitle(conv) }}</strong>
    <small>{{ formatTime(conv.latest_message?.created_at) }}</small>
    <!-- Delete button (only visible on hover or always) -->
    <button
      @click.stop="deleteConversation(conv.id)"
      class="delete-conv-btn"
      title="Delete conversation"
    >
      ✕
    </button>
  </div>
  <p class="last">{{ getLastMessagePreview(conv) }}</p>
</div>
          <span v-if="conv.unread_count > 0" class="badge">
            {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
          </span>
        </li>
      </ul>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
      <div v-if="!currentConversation" class="placeholder">
        <p>Select a conversation to start messaging</p>
      </div>
      <div v-else class="chat-window">
        <!-- Header -->
       <div class="chat-header">
  <button 
    v-if="currentConversation" 
    @click="currentConversation = null" 
    class="back-button"
  >
    ←
  </button>
  <img :src="getConversationAvatar(currentConversation)" class="avatar" />
  <div>
    <h4>{{ getConversationTitle(currentConversation) }}</h4>
    <small>Online</small>
  </div>
</div>
        <!-- Scrollable messages area -->
        <div class="messages-wrapper">
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
                      <strong v-if="msgIndex === 0 && senderGroup.sender_id !== userId">
                        {{ senderGroup.sender_name }}
                      </strong>
             <!-- Inline reply preview for sent messages -->
           <div v-if="msg.reply_to_sender_name" class="inline-reply-preview">
        <span class="reply-label">Replying to: {{ msg.reply_to_sender_name }}</span>
            <p class="reply-text">{{ msg.reply_to_message_text }}</p>
                  </div>
                      <div class="message-content">
                        {{ msg.message_text || msg.message }}
                        <!-- Display images/files if present -->
                        <div v-if="msg.attachment_url" class="attachment-preview">
                          <img
                            v-if="isImage(msg.attachment_url)"
                            :src="msg.attachment_url"
                            alt="Attachment"
                            class="attached-image"
                          />
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

                      <!-- Message options (three dots) - only on last message in group -->
                  <!-- Inside each message bubble -->
           <div 
  v-if="msgIndex === senderGroup.messages.length - 1" 
  :class="['message-options', selectedMessageId === msg.id ? 'active' : '']"
  @click.stop="toggleMessageOptions(msg.id)">
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
        </div>

        <!-- Reply indicator -->
        <div v-if="replyMessage" class="reply-indicator">
          <div class="reply-header">
            <span class="reply-sender">Replying to: {{ replyMessage.sender }}</span>
            <button @click="cancelReply" class="cancel-reply">✕</button>
          </div>
          <div class="reply-content">{{ replyMessage.text }}</div>
        </div>

        <!-- Fixed input row -->
        <div class="input-row">
          <div class="input-tools">
            <button @click="openFilePicker" title="Attach file">📎</button>
            <button @click="toggleEmojiPicker" title="Add emoji">😊</button>
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

  <!-- Example notification component -->
  <div v-if="notification.show" :class="['notification', notification.type]">
    {{ notification.message }}
  </div>

  <section v-if="section === 'market' && !loading" class="card-section">
 <!-- SEARCH BAR -->
<div class="search-bar">
  <div class="search-input-wrapper">
    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"></circle>
      <path d="m21 21-4.35-4.35"></path>
    </svg>
    <input
      type="text"
      v-model="query"
      @input="searchProducts"
      placeholder="Search products..."
      autocomplete="off"
    />
  </div>
</div>

  <div class="section-header">
<!-- FILTER BUTTONS -->
<div class="filter-controls">
  <button
    class="filter-btn"
    :class="{ active: activeFilter === 'all' }"
    @click="setFilter('all')"
  >
    All
  </button>

  <button
    v-for="category in categories"
    :key="category.id"
    class="filter-btn"
    :class="{ active: activeFilter === category.slug }"
    @click="setFilter(category.slug)"
  >
    {{ category.name }}
  </button>
</div>

<!-- NO PRODUCTS MESSAGE -->
<div v-if="filteredProducts.length === 0" class="no-products">
  No products found in this category.
</div>

<!-- PRODUCT GRID -->
<div v-else class="product-grid">
  <div
    v-for="product in filteredProducts"
    :key="product.id"
    class="product-card"
  >
    <img
      :src="product.image_url || 'default-image.jpg'"
      :alt="product.name"
      class="product-image"
    />
<h3>{{ product.name }}</h3>
<p>{{ product.description }}</p>

<!-- Price per unit -->
<p>
  <strong>₺{{ product.price }}</strong>
  <span v-if="product.unit">/ {{ product.unit.abbreviation }}</span>
</p>

<!-- Quantity with unit -->
<p>
  <strong>In Stock:</strong> {{ product.quantity }}
  <span v-if="product.unit"> {{ product.unit.abbreviation }}</span>
</p>

<!-- Farmer info -->
<p v-if="product.user">
  <strong>Farmer:</strong> {{ product.user.name }}
</p>
<!-- Review Toggle Button -->
<button class="review-btn" @click="toggleReview(product.id)">
  {{ reviewing[product.id] ? 'Cancel Review' : 'Leave a Review' }}
</button>



<!-- Review Form -->
<div v-if="reviewing[product.id]" class="review-box">
  <!-- Stars -->
  <div class="star-rating">
    <span
      v-for="n in 5"
      :key="n"
      class="star"
      :class="{ active: ratings[product.id] >= n }"
      @click="setRating(product.id, n)"
    >★</span>
  </div>

  <!-- Comment + Submit -->
  <div class="comment-box">
    <input
      type="text"
      placeholder="Leave a comment..."
      v-model="comments[product.id]"
    />
    <button @click="submitReview(product.id)">Submit</button>
  </div>
</div>

<!-- Message Farmer Button -->
<button class="message-farmer-btn" @click="goToMessaging(product)">
  💬 Message the Farmer
</button>


<!-- Quantity + Unit + Add to Cart -->
<div class="quantity-cart">
  <input
    type="number"
    v-model.number="quantities[product.id]"
    :min="1"
    :max="product.quantity"
    placeholder="Qty"
  />
  

<select v-model="selectedUnits[product.id]">
  <option
    v-for="unit in uniqueAvailableUnits"
    :key="unit.id"
    :value="unit.abbreviation"
  >
    {{ unit.name }} ({{ unit.abbreviation }})
  </option>
</select>


  <button @click="addToCart(product)">Add to Cart</button>
</div>
  </div>


  <!-- Empty State -->
  <div v-if="!products.length" class="empty-state">
    <p>No products available.</p>
  </div>
</div>
  </div>

</section>

<section v-if="section === 'cart' && !loading" class="cart-page">
  <!-- Header -->
  <div class="cart-header">
    <div class="header-content">
      <h1 class="cart-title">Shopping Cart</h1>
      <span v-if="cart.length > 0" class="items-count">{{ cart.length }} {{ cart.length === 1 ? 'item' : 'items' }}</span>
    </div>
    <button v-if="cart.length > 0" @click="clearCart" class="clear-cart-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 6h18l-2 13H5L3 6z"/>
        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        <line x1="10" y1="11" x2="10" y2="17"/>
        <line x1="14" y1="11" x2="14" y2="17"/>
      </svg>
      Clear Cart
    </button>
  </div>

  <!-- Empty State -->
  <div v-if="cart.length === 0" class="empty-state">
    <div class="empty-content">
      <div class="empty-icon">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
      </div>
      <h2>Your cart is empty</h2>
      <p>Browse our categories and discover our best deals!</p>
      <button @click="switchSection('market')" class="continue-shopping-btn">
        Start Shopping
      </button>
    </div>
  </div>

  <!-- Cart Content -->
  <div v-else class="cart-layout">
    <!-- Items List -->
    <div class="cart-items">
      <div class="items-header">
        <h3>Items in your cart</h3>
      </div>
      
      <div class="items-list">
        <div v-for="item in cartWithUnits" :key="item.product_id" class="cart-item">
          <div class="item-image">
            <img :src="item.image_url || getImageUrl(item.image)" :alt="item.name" />
          </div>
          
          <div class="item-details">
            <h4 class="item-name">{{ item.name }}</h4>
            <p class="item-description">{{ item.description }}</p>
            
            <div class="item-meta">
              <span class="item-price">₺{{ item.unit_price }}</span>
              <span v-if="item.unit" class="item-unit">per {{ item.unit.abbreviation || item.unit }}</span>
              <span class="item-availability">In Stock</span>
            </div>
            
            <div class="item-actions">
              <div class="quantity-selector">
                <label>Qty:</label>
                <div class="quantity-controls">
                  <button 
                    @click="updateCartQuantity(item.product_id, item.quantity-1)"
                    :disabled="item.quantity <= 1"
                    class="qty-btn"
                  >
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                  </button>
                  
                  <input
                    type="number"
                    :value="item.quantity"
                    @change="updateCartQuantity(item.product_id, parseInt($event.target.value))"
                    min="1"
                    class="qty-input"
                  />
                  
                  <button 
                    @click="updateCartQuantity(item.product_id, item.quantity+1)"
                    class="qty-btn"
                  >
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="5" x2="12" y2="19"/>
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                  </button>
                </div>
                <span v-if="item.unit" class="unit-label">{{ item.unit.abbreviation || item.unit }}</span>
              </div>
              
              <button @click="removeFromCart(item.product_id)" class="remove-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3,6 5,6 21,6"/>
                  <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                </svg>
                Remove
              </button>
            </div>
          </div>
          
          <div class="item-total">
            <span class="total-price">₺{{ (item.unit_price * item.quantity).toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Summary Sidebar -->
    <div class="order-summary">
      <div class="summary-card">
        <h3 class="summary-title">Order Summary</h3>
        
        <div class="summary-details">
          <div class="summary-row">
            <span>Items ({{ cart.length }}):</span>
            <span>₺{{ totalCartValue }}</span>
          </div>
          <div class="summary-row">
            <span>Shipping & handling:</span>
            <span>₺0.00</span>
          </div>
          <div class="summary-row">
            <span>Total before tax:</span>
            <span>₺{{ totalCartValue }}</span>
          </div>
          <div class="summary-row">
            <span>Estimated tax:</span>
            <span>₺0.00</span>
          </div>
        </div>
        
        <div class="summary-total">
          <div class="total-row">
            <span>Order total:</span>
            <span class="total-amount">₺{{ totalCartValue }}</span>
          </div>
        </div>
        
        <div class="summary-actions">
          <button class="checkout-btn" @click="checkout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
              <line x1="8" y1="21" x2="16" y2="21"/>
              <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Proceed to Payment
          </button>
          
          <button class="continue-btn" @click="switchSection('market')">
            Continue Shopping
          </button>
        </div>
        
        <div class="trust-badges">
          <div class="badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Secure checkout</span>
          </div>
          <div class="badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span>Fast delivery</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- Orders Section -->
<section v-if="section === 'orders' && !loading" class="card-section">
  <h2>Your Orders</h2>

  <div v-if="orders.length" class="orders-grid">
    <div class="card order-card" v-for="order in orders" :key="order.id">
      <div class="order-header">
<p><strong>Order #{{ order.displayId }}</strong></p>
        <span class="status-badge" :class="order.status.toLowerCase()">
          {{ order.status }}
        </span>
      </div>

      <p class="order-date">{{ formatDate(order.created_at) }}</p>
      <p class="order-total"><strong>Total:</strong> {{ order.total }} ₺</p>

      <ul v-if="order.items && order.items.length" class="order-items">
        <li v-for="(item, index) in order.items" :key="index">
          {{ item.product.name }} – {{ item.quantity }} x {{ item.price }} ₺
        </li>
      </ul>

      <div v-if="order.status === 'pending'" class="order-status-msg pending">
        🛒 Pending Payment
        <button @click="payOrder(order)" class="pay-now-btn">Pay Now</button>
      </div>

      <div v-else-if="order.status === 'paid'" class="order-status-msg waiting">
        ⏳ Waiting for delivery...
      </div>

      <div v-else-if="order.status === 'delivered'" class="order-status-msg delivered">
        ✅ Delivered
      </div>

      <button @click="deleteOrder(order.id)" class="delete-order-btn">
        Delete Order
      </button>
    </div>
  </div>

  <div v-else class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
    </svg>
    <p>No orders found.</p>
  </div>
</section>
<section v-if="section === 'feedback' && !loading" class="feedback-section">
  <h2 class="section-title">Feedback</h2>

  <div v-if="submittedReviews.length === 0" class="no-feedback">
    No feedback yet.
  </div>

  <div v-else class="feedback-grid">
    <div v-for="(review, i) in submittedReviews" :key="i" class="feedback-card">
      <h3 class="product-name">{{ review.product }}</h3>

      <div v-if="!editing[i]">
        <!-- Static stars -->
        <div class="stars">
          <span
            v-for="n in 5"
            :key="n"
            class="star"
            :class="{ active: review.rating >= n }"
          >★</span>
        </div>

        <p>{{ review.comment }}</p>
        <small class="timestamp">{{ new Date(review.timestamp).toLocaleString() }}</small>

        <!-- Farmer reply -->
        <div v-if="review.reply" class="farmer-reply">
          <strong>Farmer's reply:</strong>
          <p>{{ review.reply }}</p>
        </div>

        <!-- Edit button -->
        <button @click="startEdit(i)">Edit Review</button>
      </div>

      <div v-else>
        <!-- Editable stars -->
        <div class="stars">
          <span
            v-for="n in 5"
            :key="n"
            class="star"
            :class="{ active: editedRatings[i] >= n }"
            @click="setEditedRating(i, n)"
            style="cursor: pointer;"
          >★</span>
        </div>

        <!-- Editable comment -->
        <textarea v-model="editedComments[i]" rows="4" cols="40"></textarea>

        <!-- Save / Cancel buttons -->
        <button @click="saveEdit(i, review.id)">Save</button>
        <button @click="cancelEdit(i)">Cancel</button>
      </div>
    </div>
  </div>
</section>

<section v-if="section === 'payment'" class="payment-section">
  <h2>Complete Your Order</h2>
  <div class="payment-container">
    <div v-if="cart.length === 0" class="empty-cart">
      <p>Your cart is empty</p>
      <button @click="switchSection('market')" class="btn-primary">Continue Shopping</button>
    </div>
    <div v-else>
      <!-- Delivery Information -->
      <div class="delivery-info">
        <h3>Delivery Information</h3>

        <label for="full-address">Full Delivery Address</label>
        <textarea
          id="full-address"
          v-model="fullAddress"
          placeholder="e.g., 123 Main St, Istanbul, 34000, Turkey"
          class="address-input"
          :disabled="paymentProcessing"
          required
        ></textarea>

      <label for="delivery-method">Delivery Method</label>
<select v-model="deliveryMethod">
  <option value="" disabled>-- Choose delivery method --</option>
  <option value="delivery">Standard Delivery (3–5 days)</option>
  <option value="pickup">Store Pickup</option>
</select>
      </div>

      <!-- Order Summary -->
      <div class="order-summary">
        <h3>Order Summary</h3>
        <div class="summary-items">
          <div v-for="item in cart" :key="item.product_id" class="summary-item">
            <span>{{ item.name }} ({{ item.quantity }})</span>
            <span>₺{{ (item.unit_price * item.quantity).toFixed(2) }}</span>
          </div>
        </div>
        <div class="summary-total">
          <span><strong>Total:</strong></span>
          <span><strong>₺{{ totalCartValue }}</strong></span>
        </div>
      </div>

      <!-- Payment Method Selection -->
      <div class="payment-methods">
        <h3>Payment Method</h3>

        <!-- Payment Options Radio -->
        <div class="payment-options">
          <label class="payment-option">
            <input
              type="radio"
              v-model="selectedPaymentMethod"
              value="card"
              :disabled="paymentProcessing"
            />
            <div class="option-content">
              <div class="option-icon">💳</div>
              <div>
                <div class="option-title">Credit/Debit Card</div>
                <div class="option-desc">Pay securely with Stripe</div>
              </div>
            </div>
          </label>

          <label class="payment-option">
            <input
              type="radio"
              v-model="selectedPaymentMethod"
              value="cod"
              :disabled="paymentProcessing"
            />
            <div class="option-content">
              <div class="option-icon">💰</div>
              <div>
                <div class="option-title">Cash on Delivery</div>
                <div class="option-desc">Pay in cash when your order arrives</div>
              </div>
            </div>
          </label>
        </div>

        <!-- Action Button -->
        <button
          @click="processPayment"
          :disabled="paymentProcessing || !selectedPaymentMethod || !deliveryMethod"
          class="pay-btn"
        >
          {{ paymentProcessing ? 'Processing...' : `Pay ₺${totalCartValue}` }}
        </button>

        <div v-if="paymentError" class="payment-error">
          {{ paymentError }}
        </div>
        <div v-if="paymentProcessing" class="payment-processing">
          Please wait...
        </div>
      </div>
    </div>
  </div>
</section>


    </main>
  </div>


  
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted , nextTick, watch  } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import avatar from '@/assets/avatar.png'
import ProfileModal from '@/components/ProfileModal.vue'
import { loadStripe } from '@stripe/stripe-js';
import { useAuthStore } from '@/stores/auth'
import defaultAvatar from '@/assets/default-avatar.jpg'


const notification = ref({ show: false, message: '', type: 'info' });
const showNotifications = (message, type = 'info') => {
  notification.value = { show: true, message, type };
  setTimeout(() => { notification.value.show = false; }, 5000);
};

const showStatus = showNotifications;
// Axios configuration
axios.defaults.baseURL = 'http://127.0.0.1:8000'
const router = useRouter()
const counter = ref(0)

// ✅ Add these refs
const isContactOnline = ref(false);
const lastSeenTime = ref(null);


const section = ref("market");      

const switchSection = async (target) => {
  section.value = target;            
  globalError.value = null;

  if (target === "market") await fetchProducts();
  if (target === "cart") await fetchCart();
  if (target === "orders") await fetchOrders();
  if (target === "payment") await fetchCartTotal();

 if (target === 'messages') {
    loading.value = true
    await loadConversations()
    loading.value = false
  }
};

const goToMessaging = async (product) => {
  const farmerId = product?.user?.id;
  const farmerName = product?.user?.name || 'Farmer';
  if (!farmerId) {
    globalError.value = 'Farmer ID is missing.';
    return;
  }

  const token = localStorage.getItem('token');
  if (!token) {
    globalError.value = 'You must be logged in to message a farmer.';
    return;
  }

  const existingConv = conversations.value.find(conv =>
    conv.participants.some(p => Number(p.id) === farmerId)
  );

  if (existingConv) {
    section.value = 'messages';
    await selectConversation(existingConv);
  } else {
    try {
      const res = await axios.post(
        '/api/conversations',
        { user_id: farmerId },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      const newConv = {
        ...res.data,
        // 💡 Inject farmer info so avatar logic works immediately
        _farmerId: farmerId,
        _farmerName: farmerName
      };

      conversations.value.push(newConv);
      section.value = 'messages';
      await selectConversation(newConv);
    } catch (err) {
      console.error('Failed to create conversation:', err);
      globalError.value = err.response?.data?.message || 'Could not start conversation.';
    }
  }
};

const conversations = ref([])
const currentConversation = ref(null)
const conversationSearchQuery = ref('')
const newMessage = ref('')
const userId = computed(() => authStore.user?.id)
const userAvatar = ref(window.currentUserAvatar || null)
const loadingConversations = ref(false)
const loadingMessages = ref(false)
const messagesContainer = ref(null)
const showEmojiPicker = ref(false)
const selectedFile = ref(null)
const replyMessage = ref(null)
const selectedMessageId = ref(null)
const fileInput = ref(null)
const authStore = useAuthStore()
const getAvatar = getConversationAvatar



// Emoji list
const emojiList = ref(['😀', '😂', '😍', '😎', '😊', '🥰', '😘', '😗', '😙', '😚', '🙂', '🤗', '🤩', '🤔', '🤨', '😐', '😑', '😶', '🙄', '😏', 
  '😣', '😥', '😮', '🤐', '😯', '😪', '😫', '😴', '😌', '😛', '😜', '😝', '🤤', '😒', '😓', '😔', '😕', '🙃', '🤑', '😲', 
  '☹️', '🙁', '😖', '😞', '😟', '😤', '😢', '😭', '😦', '😧', '😨', '😩', '😬', '😰', '😱', '😳', '🤪', '😵', '😡', '😠', 
  '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '✋', '🤚', '🖖', '👋', '🤙', '💪', 
  '❤️', '🧡', '💛', '💚', '💙',  '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', 
  '🔥', '✨', '⭐', '🌟', '💫', '💥', '💦', '💨', '💫', '💯', '💢', '💥', '💫', '💦', '💨', '💫', '💯', '💢', '💥', '💫'])


// --- Computed
const filteredConversations = computed(() => {
  const q = conversationSearchQuery.value.toLowerCase()
  return conversations.value.filter(c =>
    getConversationTitle(c).toLowerCase().includes(q)
  )
})



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

function getInitials(name) {
  if (!name) return 'U'
  const names = name.split(' ')
  const first = names[0]?.charAt(0) || ''
  const last = names.length > 1 ? names[names.length - 1]?.charAt(0) || '' : ''
  return (first + last).toUpperCase()
}
const getConversationTitle = (conv) => conv.chat_name || 'Unknown User'
function getConversationAvatar(conv) {
  if (!conv || !conv.participants) return getAvatarUrl("0", "User");

  const myId = userId.value ? Number(userId.value) : null;

  // Try to find the other participant
  let other = null;
  if (myId !== null) {
    other = conv.participants.find(p => Number(p.id) !== myId);
  }

  // If we couldn't find "other", use injected farmer data
  if (!other && conv._farmerId) {
    return getAvatarUrl(conv._farmerId, conv._farmerName);
  }

  // If we have other with name → use it
  if (other && (other.name || other.id)) {
    return getAvatarUrl(other.id, other.name || 'User');
  }

  // Final fallback
  return getAvatarUrl(conv.id || "0", conv.chat_name || "User");
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

const confirmVisible = ref(false)
const confirmMessage = ref('')
const confirmCallback = ref(null)

function showConfirm(message, callback) {
  confirmMessage.value = message
  confirmCallback.value = callback
  confirmVisible.value = true
}

function handleConfirm() {
  confirmVisible.value = false
  if (typeof confirmCallback.value === 'function') {
    confirmCallback.value()
  }
  confirmCallback.value = null
}

function cancelConfirm() {
  confirmVisible.value = false
  confirmCallback.value = null
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

const getLastMessagePreview = (conv) => {
  const text =
    conv.latest_message?.message_text ||
    conv.latest_message?.message ||
    ''
  return text.length > 40 ? text.slice(0, 40) + '...' : text
}

const formatTime = (ts) => {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// --- API Calls
const loadConversations = async () => {
  loadingConversations.value = true;
  try {
    const token = localStorage.getItem('token');
    const res = await axios.get('/api/conversations', {
      headers: { Authorization: `Bearer ${token}` }
    });

    // ✅ Sort conversations: newest (latest message) first
    const sorted = res.data.sort((a, b) => {
      const timeA = a.latest_message?.created_at
        ? new Date(a.latest_message.created_at).getTime()
        : 0;
      const timeB = b.latest_message?.created_at
        ? new Date(b.latest_message.created_at).getTime()
        : 0;
      return timeB - timeA; // Descending: newest first
    });

    conversations.value = sorted;
  } catch (e) {
    console.error('Failed to load conversations', e);
  } finally {
    loadingConversations.value = false;
  }
};


const selectConversation = async (conv) => {
  // Move this conversation to the top of the list
  conversations.value = [
    conv,
    ...conversations.value.filter(c => c.id !== conv.id)
  ];

  // Update UI immediately: show loading & set conversation
  currentConversation.value = { ...conv, messages: [] };
  loadingMessages.value = true;

  try {
    const token = localStorage.getItem('token');
    const headers = { Authorization: `Bearer ${token}` };

    // ✅ STEP 1: Mark all messages as read on the server
    await axios.post(`/api/conversations/${conv.id}/read`, {}, { headers });

    // ✅ STEP 2: Now load messages
    const res = await axios.get(`/api/conversations/${conv.id}/messages`, { headers });
    const messages = res.data;

    // Update messages
    currentConversation.value.messages = messages;

    // Recalculate unread (though backend should mark all as read)
    const unreadCount = messages.filter(msg => !msg.is_read).length;
    currentConversation.value.unreadCount = unreadCount;

    // ✅ STEP 3: Ensure the conversation in the sidebar list has 0 unread
    const existingConv = conversations.value.find(c => c.id === conv.id);
    if (existingConv) {
      existingConv.unread_count = 0;
    }

    await nextTick();
    smoothScrollToBottom();
  } catch (e) {
    console.error('Failed to load messages or mark as read', e);
    showStatus('Failed to load conversation.', 'error');
  } finally {
    loadingMessages.value = false;
  }
};

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedFile.value) || !currentConversation.value) return

  const tempMessageText = newMessage.value.trim()

  // Get reply context if available
  const replyContext = replyMessage.value
    ? {
        reply_to_message_id: replyMessage.value.id,
        reply_to_sender_name: replyMessage.value.sender,
        reply_to_message_text: replyMessage.value.text
      }
    : null

  const msg = {
    id: Date.now(),
    message_text: tempMessageText,
    sender_id: userId.value,
    created_at: new Date().toISOString(),
    attachment_url: selectedFile.value ? URL.createObjectURL(selectedFile.value) : null,
    // ✅ Attach full reply context for display
    reply_to_message_id: replyContext?.reply_to_message_id || null,
    reply_to_sender_name: replyContext?.reply_to_sender_name || null,
    reply_to_message_text: replyContext?.reply_to_message_text || null
  }

  currentConversation.value.messages.push(msg)

  // ✅ Clear floating reply bar
  replyMessage.value = null

  // Reset input
  newMessage.value = ''
  selectedFile.value = null

  try {
    const token = localStorage.getItem('token')
    const formData = new FormData()
    formData.append('conversation_id', currentConversation.value.id)
    formData.append('message_text', tempMessageText)

    // Optional: send reply_to_message_id to backend if needed
    if (replyContext?.reply_to_message_id) {
      formData.append('reply_to_message_id', replyContext.reply_to_message_id)
    }

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


// --- Smooth Scrolling Logic
const smoothScrollToBottom = async () => {
  await nextTick()
  const el = messagesContainer.value
  if (el) {
    el.scrollTo({
      top: el.scrollHeight,
      behavior: 'smooth'
    })
  }
}

// Watch for new messages dynamically
watch(
  () => currentConversation.value?.messages?.length,
  () => smoothScrollToBottom()
)

// Delete an entire conversation
const deleteConversation = async (conversationId) => {
  showConfirm('Are you sure you want to delete this conversation?', async () => {
    try {
      await axios.delete(`/api/conversations/${conversationId}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      });

      // Remove from local list
      conversations.value = conversations.value.filter(conv => conv.id !== conversationId);

      // If currently viewing this conversation, clear it
      if (currentConversation.value?.id === conversationId) {
        currentConversation.value = null;
      }

      showNotifications('Conversation deleted.', 'success');
    } catch (err) {
      console.error('Failed to delete conversation:', err);
      showNotifications('Failed to delete conversation.', 'error');
    }
  });
};

const groupedMessages = computed(() => {
  if (!currentConversation.value?.messages?.length) return []

  const groups = []
  let lastSender = null
  let currentGroup = null

  for (const msg of currentConversation.value.messages) {
    if (msg.sender_id !== lastSender) {
      if (currentGroup) groups.push(currentGroup)
      currentGroup = {
        sender_id: msg.sender_id,
        sender: msg.sender,
        messages: []
      }
      lastSender = msg.sender_id
    }
    currentGroup.messages.push(msg)
  }

  if (currentGroup) groups.push(currentGroup)
  return groups
})

// --- Lifecycle
onMounted(() => {
  loadConversations()
})

// ✅ Unified payment processing state (use ONE name)
const paymentProcessing = ref(false);
const paymentError = ref(null);
const fullAddress = ref('');
const selectedPaymentMethod = ref('card'); // ← Add this ref if not already present


// ✅ Load Stripe
const stripePromise = loadStripe('pk_test_51RpdA0H7s9nJbI2dsiOJmgCJkE0Z6TgQhv7eThKVDPjTxqSmzHWXMetbcUwYUFZGSuvi47TTCMvwOGRXAGGKpq9700BGtC5EvM');



const deliveryMethod = ref(''); // will be 'delivery' or 'pickup'


const processPayment = async () => {
  // 1. Validate delivery info
  if (!fullAddress.value?.trim()) {
    paymentError.value = 'Please enter your full delivery address.';
    return;
  }
  if (!deliveryMethod.value || !['delivery', 'pickup'].includes(deliveryMethod.value)) {
    paymentError.value = 'Please select a valid delivery method.';
    return;
  }
  paymentError.value = null;
  paymentProcessing.value = true;

  try {
    if (selectedPaymentMethod.value === 'card') {
      // --- STRIPE PAYMENT WITH SPLIT ---
      const orderResponse = await axios.post(
        '/api/checkout',
        {
          full_address: fullAddress.value.trim(),
          delivery_method: deliveryMethod.value, // ✅ now 'delivery' or 'pickup'
          payment_method: 'card'
        },
        { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
      );

      // Create Stripe session with split
      const sessionResponse = await axios.post(
        '/api/create-checkout-session',
        {
          order_id: orderResponse.data.order_id
        },
        { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
      );

      const stripe = await stripePromise;
      const { error } = await stripe.redirectToCheckout({
        sessionId: sessionResponse.data.sessionId
      });
      if (error) {
        paymentError.value = error.message;
      }
    } else if (selectedPaymentMethod.value === 'cod') {
      // --- CASH ON DELIVERY ---
      await axios.post(
        '/api/checkout',
        {
          full_address: fullAddress.value.trim(),
          delivery_method: deliveryMethod.value,
          payment_method: 'cod'
        },
        { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
      );
      showNotifications('✅ Order placed successfully! You will pay on delivery.');
      section.value = 'orders';
      await fetchOrders();
    }
  } catch (err) {
    console.error('Payment error:', err);
    paymentError.value =
      err.response?.data?.errors?.delivery_method?.[0] ||
      err.response?.data?.message ||
      'An unexpected error occurred. Please try again.';
  } finally {
    paymentProcessing.value = false;
  }
};

// Existing payOrder function (unchanged)
const payOrder = async (order) => {
  try {
    const res = await axios.post(
      '/api/create-checkout-session',
      { order_id: order.id },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
    );
    const stripe = await stripePromise;
    await stripe.redirectToCheckout({ sessionId: res.data.sessionId });
  } catch (err) {
    console.error('Payment error:', err);
    showNotifications('Payment failed. Please try again.');
  }
};

const deleteOrder = async (orderId) => {
  console.log("Deleting order ID:", orderId); // 🔍 ADD THIS
  showConfirm('Are you sure you want to delete this order?', async () => {
    try {
      await axios.delete(`/api/orders/${orderId}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      });
      await fetchOrders();
      showNotifications('Order deleted successfully.', 'success');
    } catch (err) {
      console.error('Failed to delete order:', err);
      showNotifications('Failed to delete order.', 'error');
    }
  });
};

// UI State
const loading = ref(false)
const globalError = ref(null)
const isLoadingProfile = ref(true)
const settingsOpen = ref(false)
const profileModalOpen = ref(false)
const profilePictureUrl = ref('')

// User data
const userProfile = ref({
  name: '',
  email: '',
  avatar_url: null
})

// Computed properties
const firstName = computed(() => userProfile.value.name?.split(' ')[0] || 'User')
const userInitial = computed(() => userProfile.value.name?.charAt(0).toUpperCase() || 'U')


const closeSettings = () => {
  settingsOpen.value = false
}


// Profile modal methods
const openProfile = () => {
  profileModalOpen.value = true
}

const closeProfile = () => {
  profileModalOpen.value = false
}

const onProfileUpdated = (updatedUser) => {
  userProfile.value = updatedUser
  profilePictureUrl.value = updatedUser.avatar_url || defaultAvatar
  closeProfile()
}




onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Fetch user profile
const fetchUserProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return
    
    const response = await axios.get('/api/user/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    userProfile.value = response.data
    profilePictureUrl.value = response.data.avatar_url || defaultAvatar
  } catch (error) {
    console.error('Error fetching profile:', error)
    profilePictureUrl.value = defaultAvatar
  }
}

// Initialize on component mount
onMounted(() => {
  fetchUserProfile()
})


const updateProfile = async (form) => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return alert('Authentication error')

    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('email', form.email)
    formData.append('avatar', form.value.avatar);

    await axios.post('/api/user/profile', formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    })

    profileModalOpen.value = false
  } catch (err) {
    console.error('Update failed:', err)
    globalError.value = 'Profile update failed. Please try again.'
  }
}


// Modal controls
const toggleSettings = () => (settingsOpen.value = !settingsOpen.value)


// Add this with your other functions
const setAuthToken = () => {
  const token = localStorage.getItem('token')
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  } else {
    delete axios.defaults.headers.common['Authorization']
    router.push('/login') // Redirect to login if no token
  }
}

// Auth actions
const handleLogout = async () => {
  try {
    await axios.post('/api/logout')
    localStorage.removeItem('token')
    router.push('/login')
  } catch {
    showConfirm('Logout failed.')
  }
}

const products = ref([])
const categories = ref([])
const activeFilter = ref('all')
const query = ref('')
const results = ref([])

const searchProducts = () => {
  const lowerQuery = query.value.toLowerCase().trim();
  if (!lowerQuery) {
    results.value = [];
    return;
  }

  results.value = products.value.filter((product) =>
    product.name.toLowerCase().includes(lowerQuery)
  );
};

const selectProduct = (product) => {
  selectedProduct.value = product;
};



const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    globalError.value = handleApiError(err, 'Fetch categories')
  }
}

const setFilter = (slug) => {
  activeFilter.value = slug
}

const filteredProducts = computed(() => {
  let base = products.value;

  // Filter by category if not 'all'
  if (activeFilter.value !== 'all') {
    base = base.filter(
      product => product.category?.slug === activeFilter.value
    );
  }

  // Further filter by search query if provided
  const searchTerm = query.value.trim().toLowerCase();
  if (searchTerm !== '') {
    base = base.filter(
      product => product.name.toLowerCase().includes(searchTerm)
    );
  }

  return base;
});

// Fetching logic
const fetchProducts = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/products')
    products.value = Array.isArray(res.data.data) ? 
    res.data.data : []
    
    products.value.forEach(p => {
      if (!p.image_url && p.image) {
        p.image_url = getImageUrl(p.image)
      }
    })
  } catch (err) {
    globalError.value = handleApiError(err, 'Fetch products')
    products.value = []
  } finally {
    loading.value = false
  }
}


const allReviews = ref([]);
const submittedReviews = ref([]);

const reviewing = reactive({});
const ratings = reactive({});
const comments = reactive({});

const editing = reactive({});
const editedRatings = reactive({});
const editedComments = reactive({});

// Helper to get token and throw if missing
function getToken() {
  const token = localStorage.getItem("token");
  if (!token) {
    showNotifications("Please log in first.");
    throw new Error("No token found");
  }
  return token;
}

// ✅ Toggle review form
const toggleReview = (productId) => {
  reviewing[productId] = !reviewing[productId];
};

// ✅ Set star rating when leaving a new review
const setRating = (productId, rating) => {
  ratings[productId] = rating;
};

// ✅ Submit new review to backend
const submitReview = async (productId) => {
  try {
    const token = getToken();

    const res = await fetch("http://127.0.0.1:8000/api/feedbacks", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        product_id: productId,
        rating: ratings[productId],
        comment: comments[productId] || "",
      }),
    });

    const data = await res.json();

    if (res.ok) {
      // Optionally you can fetch the full product name here if needed
      submittedReviews.value.push({
        id: data.data.id,
        product: data.data.product_id,  // if you want product name, fetch separately
        rating: data.data.rating,
        comment: data.data.comment,
        timestamp: data.data.created_at,
      });

      reviewing[productId] = false;
      ratings[productId] = 0;
      comments[productId] = "";
    } else {
      alert(data.message || "Failed to submit review");
    }
  } catch (err) {
    console.error("Error submitting review:", err);
    showNotifications("An error occurred. Please try again.");
  }
};

// ✅ Fetch reviews for a specific product (used when displaying product feedback)
const fetchProductReviews = async (productId) => {
  try {
    const token = getToken();

    const res = await fetch(
      `http://127.0.0.1:8000/api/feedbacks/product/${productId}`, // Adjusted to match your backend route name: productFeedback
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const data = await res.json();
    return data.feedback || [];
  } catch (err) {
    console.error("Error loading product reviews:", err);
    return [];
  }
};

// ✅ Fetch current user's submitted reviews
const fetchReviews = async () => {
  try {
    const token = getToken();

    const res = await fetch("http://127.0.0.1:8000/api/feedbacks/user", {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (!res.ok) throw new Error("Failed to fetch user reviews");

    const data = await res.json();

    submittedReviews.value = data.map((r) => ({
      id: r.id,
      product: r.product?.name ?? "Unknown",
      rating: r.rating,
      comment: r.comment,
      reply: r.reply || null,
      timestamp: r.created_at,
    }));
  } catch (error) {
    console.error("Error fetching user reviews:", error);
  }
};

// ✅ Fetch all approved reviews (for public display)
const fetchAllReviews = async () => {
  try {
    const res = await axios.get(
      "http://127.0.0.1:8000/api/feedbacks/approved"
    );
    allReviews.value = res.data.feedback || [];
  } catch (err) {
    console.error("Error loading all reviews:", err);
  }
};

// ✅ Start editing a review
function startEdit(index) {
  if (submittedReviews.value[index].reply) {
    showNotifications("❌ You cannot edit this review because the farmer has already replied.");
    return;
  }

  editing[index] = true;
  editedRatings[index] = submittedReviews.value[index].rating;
  editedComments[index] = submittedReviews.value[index].comment;
}

// ✅ Change star rating in edit mode
function setEditedRating(index, rating) {
  editedRatings[index] = rating;
}

// ✅ Cancel editing without saving
function cancelEdit(index) {
  editing[index] = false;
  editedRatings[index] = 0;
  editedComments[index] = "";
}
async function saveEdit(index, reviewId) {
  try {
    const token = getToken();

    const payload = {
      rating: editedRatings[index],
      comment: editedComments[index],
    };

    const response = await axios.put(
      `http://127.0.0.1:8000/api/feedbacks/${reviewId}`,
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      }
    );

    showNotifications("✅ Review updated successfully.");
    submittedReviews.value[index].rating = payload.rating;
    submittedReviews.value[index].comment = payload.comment;
    cancelEdit(index);

  } catch (error) {
    console.error("Save edit error:", error);

    const status = error.response?.status;
    const msg = error.response?.data?.message || "Unknown error";

    if (status === 403) {
      showNotifications("🔒 You can't edit this review (permission denied).");
    } else if (status === 404) {
      showNotifications("❌ Review not found.");
    } else {
      showNotifications(`❌ Update failed: ${msg}`);
    }
  }
}

// ✅ Fetch reviews on component mount
onMounted(() => {
   // ✅ Check if user just returned from Stripe Checkout
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('session_id')) {
    // Clear the URL param to avoid re-triggering
    router.replace({ query: {} });
    // ✅ REFRESH ORDERS AFTER PAYMENT
    fetchOrders();
  }
  fetchReviews();
  fetchAllReviews();
});




// Cart & Orders
const cart = ref([])
const orders = ref([])
const quantities = ref({})
const totalAmount = ref(0)
const paymentMethod = ref('card')
const paymentStatus = ref('')
const card = ref({ number: '', expiry: '', cvc: '' })

// Image helper
const getImageUrl = (image) =>
  image ? `http://localhost:8000/storage/${image}` : 'https://via.placeholder.com/150'

// API error handler
const handleApiError = (error, context = 'Operation') => {
  console.error(`${context} error:`, error)
  let msg = `${context} failed.`
  if (error.response) {
    const status = error.response.status
    const data = error.response.data
    switch (status) {
      case 400: msg = data.message || 'Bad request'; break
      case 401:
        msg = 'Unauthorized. Please login again.'
        localStorage.removeItem('token')
        router.push('/login')
        return
      case 403: msg = 'Forbidden'; break
      case 404: msg = 'Not found'; break
      case 422: msg = data.message || 'Validation failed'; break
      case 500: msg = 'Server error'; break
      default: msg = data.message || `Error (${status})`
    }
  } else if (error.request) {
    msg = 'Network error.'
  }
  return msg
}

const fetchCart = async () => {
  console.log('Fetching cart...')  // Add this
  loading.value = true
  try {
    const res = await axios.get('/api/consumer/cart')
    console.log('Cart response:', res.data)  // Add this
    cart.value = res.data.cart || []
    cart.value.forEach(item => {
      if (!item.image_url && item.image) {
        item.image_url = getImageUrl(item.image)
      }
    })
  } catch (err) {
    globalError.value = handleApiError(err, 'Fetch cart')
  } finally {
    loading.value = false
  }
}

const cartWithUnits = computed(() =>
  cart.value.map((item) => {
    // Find the matching unit from availableUnits
    const unitObj = availableUnits.value.find((u) => u.id === item.unit_id);
    return {
      ...item,
      unit: unitObj || null, // Attach full unit info
    };
  })
);

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}
const fetchOrders = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get('/api/orders', {
      headers: { Authorization: `Bearer ${token}` }
    });

    orders.value = response.data.data.map(order => ({
      id: order.order_id,        // ✅ This is actually the real DB ID (31)
      displayId: `ORD-${order.order_id}`, // or just order.order_id if you prefer
      created_at: order.order_date,
      total: parseFloat(order.total_price) || 0,
      status: order.status.toLowerCase(),
      items: (order.items || []).map(item => ({
        product: { name: item.product_name },
        quantity: item.quantity,
        price: parseFloat(item.price_each) || 0
      }))
    }));
  } catch (error) {
    console.error('Failed to fetch orders:', error);
  } finally {
    loading.value = false;
  }
};

// Fetch orders when the component mounts
onMounted(() => {
  if (section.value === 'orders') {
    fetchOrders();
  }
});

// Shows number of distinct products in cart (not total quantity)
const cartItemCount = computed(() => {
  return cart.value.length; // ✅ Each item in cart = 1 product
});

const addToCart = async (product) => {
  const quantity = quantities.value[product.id];
  const unit = selectedUnits.value[product.id] || null;

  // Validation
  if (!quantity || quantity < 1) {
    showNotifications("Please enter a valid quantity.");
    return;
  }
  if (!unit) {
    showNotifications("Please select a unit.");
    return;
  }

  try {
    const res = await axios.post("/api/consumer/cart", {
      product_id: product.id,
      quantity,
      unit,
    });

    // ✅ SUCCESS: Backend should return updated product data or cart item
    const updatedProduct = res.data.product; // ← Expect this from backend
    if (updatedProduct && updatedProduct.quantity !== undefined) {
      product.quantity = updatedProduct.quantity; // Sync with backend
    } else {
      // Fallback: just subtract locally (less safe)
      product.quantity = Math.max(0, product.quantity - quantity);
    }

    // Optional: Show feedback
    showNotifications(`${quantity} ${unit} of ${product.name} added to cart!`);

    // Reset input
    quantities.value[product.id] = 0;

    // Optionally refresh cart
    await fetchCart();
  } catch (err) {
    console.error("Add to cart error:", err);
    globalError.value = handleApiError(err, "Add to cart");
  }
};


const removeFromCart = async (productId) => {
  const item = cart.value.find(i => i.product_id === productId);
  if (!item) {
    showNotifications('Item not found in cart.', 'error');
    return;
  }

  // Show custom confirmation modal
  showConfirm('Are you sure you want to remove this item from your cart?', async () => {
    try {
      await axios.delete(`/api/consumer/cart/${item.id}`);
      await fetchCart();
      showNotifications('Item removed from cart.', 'success');
    } catch (err) {
      globalError.value = handleApiError(err, 'Remove from cart');
      showNotifications('Failed to remove item.', 'error');
    }
  });
};

const updateCartQuantity = async (productId, newQty) => {
  if (newQty < 1) return await removeFromCart(productId)
  const item = cart.value.find(i => i.product_id === productId)
  if (!item) return
  try {
    await axios.put(`/api/consumer/cart/${item.id}`, { quantity: newQty })
    await fetchCart()
  } catch (err) {
    globalError.value = handleApiError(err, 'Update quantity')
  }
}

const clearCart = async () => {
  showConfirm('Clear entire cart?', async () => {
    try {
      await axios.delete('/api/consumer/cart/clear'); // ✅ Relative path
      cart.value = [];
      showNotifications('Cart cleared successfully.', 'success');
    } catch (err) {
      console.error('Clear cart error:', err);
      globalError.value = handleApiError(err, 'Clear cart');
    }
  });
};


const totalCartValue = computed(() => {
  return cart.value.reduce((sum, item) => {
    const unit = item.unit_price || 0
    const qty = item.quantity || 1
    return sum + unit * qty
  }, 0).toFixed(2)
})

const fetchCartTotal = async () => {
  try {
    const res = await axios.get('/api/cart/total', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    totalAmount.value = res.data.total
  } catch (err) {
    console.error('Cart total error:', err)
  }
}

// Checkout logic
function checkout() {
  section.value = 'payment'
}

// Initialization
onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('Please login first.')
    return router.push('/login')
  }

  setAuthToken()
  await fetchUserProfile()
  await fetchProducts()
  await fetchCart()
  await fetchOrders()
  await fetchCartTotal()
  await fetchCategories()
  await fetchUnits() // ✅ Only call once here

})

// Computed properties for dashboard
const pendingOrdersCount = computed(() => 
  orders.value.filter(order => order.status === 'pending').length
)

const totalSpent = computed(() => 
  orders.value
    .filter(order => order.status === 'delivered' || order.status === 'paid')
    .reduce((sum, order) => sum + parseFloat(order.total || 0), 0)
    .toFixed(2)
)

const averageRatingGiven = computed(() => {
  if (submittedReviews.value.length === 0) return '0.0'
  const sum = submittedReviews.value.reduce((acc, review) => acc + review.rating, 0)
  return (sum / submittedReviews.value.length).toFixed(1)
})

const recentOrders = computed(() => 
  orders.value
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 3)
)

const cartPreview = computed(() => 
  cart.value.slice(0, 3)
)

const recentReviews = computed(() => 
  submittedReviews.value
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 2)
)


// Fix the featuredProducts computed property
const featuredProducts = computed(() => {
  const productList = products.value || []  // ✅ Use a different variable name
  return productList
    .sort(() => 0.5 - Math.random())
    .slice(0, 4)
})

const goToProduct = (product) => {
  router.push(`/product/${product.id}`)
}

// --- EARLY REFS ---
const units = ref([]);
const availableUnits = ref([]);
const selectedUnits = ref({});

// ✅ DEFINE fetchUnits EARLY
const fetchUnits = async () => {
  try {
    const res = await axios.get('/api/units');
    units.value = res.data;
    availableUnits.value = res.data;
  } catch (err) {
    console.error('Failed to fetch units:', err);
  }
};
// Deduplicated list for display
const uniqueAvailableUnits = computed(() => {
  const seen = new Set();
  return availableUnits.value.filter(unit => {
    const key = `${unit.name}-${unit.abbreviation}`;
    if (seen.has(key)) {
      return false;
    }
    seen.add(key);
    return true;
  });
});

</script>

<style scoped>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .consumer-app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0;
            padding: 0;
        }

        /* Professional Subtle Background */
        .consumer-app::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.02) 0%, transparent 50%);
            z-index: -1;
        }




/* Header Container (unchanged) */
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: #ffffff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  position: relative; /* Needed for absolute positioning */
}

/* NEW: Centered Greeting Solution */
.greeting-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  width: max-content; /* Prevents stretching */
}

.greeting-center h2 {
  margin: 0;
  font-size: 1.6rem;
  color: #38b883b8;
}

.greeting-center p {
  margin: 0.25rem 0 0;
  color: #080808ff;
  font-size: 1rem;
}

/* Existing Avatar/Settings Styles (unchanged) */
.settings-wrapper {
  position: relative;
  cursor: pointer;
  margin-left: auto; /* Pushes to far right */
}

.profile-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.settings-menu {
  position: absolute;
  top: 65px;
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 150px;
  z-index: 100;
  border: 1px solid #e2e8f0;
}

.settings-menu ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.settings-menu li {
  padding: 12px 16px;
  cursor: pointer;
  color: #64748b;
  font-weight: 500;
}

.settings-menu li:hover {
  background: #375f9eff;
  color: white;
}

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            backdrop-filter: blur(20px);
            display: flex;
            justify-content: space-around;
            padding: 1rem 0;
            border-top: 1px solid #e2e8f0;
            z-index: 100;
            box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.1);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #1011107e;
            position: relative;
        }

        .nav-item.active {
            color: #101010ff;
            background: rgba(9, 186, 9, 0.35);
            transform: translateY(-2px);
        }

        .nav-item:hover {
            transform: translateY(-2px);
            color: #0ed64aac;
        }

        .nav-item svg, .nav-item span {
            position: relative;
            z-index: 1;
        }

        /* Main Content */
        .main-view {
            flex: 1;
            padding: 2rem;
            padding-bottom: 5rem;
            overflow-y: auto;
        }
        

        /* Search Bar */
        .search-bar {
            max-width: 600px;
            margin: 5rem auto 2rem; /* Increased top margin from 0 to 3rem */
        }

        .search-input-wrapper {
            background: #ffffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .search-input-wrapper:focus-within {
            border-color: #4e97366b;
            box-shadow: 0 0 0 3px rgba(8, 8, 8, 0.77);
        }

        .search-input-wrapper input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1rem;
            margin-left: 1rem;
            outline: none;
            color: #54c269ff;
        }

        .search-input-wrapper input::placeholder {
            color: #30b3786f;
        }

        .search-icon {
            color: #59cc8f74;
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            color: #000000ff;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            position: relative;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #0a996792;
            color: white;
            border-color: #0eb08233;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(6, 6, 6, 0);
        }

        .filter-btn span {
            position: relative;
            z-index: 1;
        }
 .no-products {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #64748b;
  width: 100%;
}
    /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            
        }

        .product-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;


        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.02);
        }

        .product-card h3 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #050505ff;
            margin-bottom: 0.5rem;
           font-family: 'Dancing Script', cursive;
        }

        .product-card p {
            color: #020202ff;
            margin-bottom: 0.5rem;
            line-height: 1.5;
            font-size: 1rem;
        }

        .cart-unit {
       margin-left: 6px;
      font-weight: 500;
      color: #444;
     }
/* --- Quantity + Unit + Add to Cart (Responsive) --- */
.quantity-cart {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
  width: 100%;
}

/* Quantity Input */
.quantity-cart input[type="number"] {
  flex: 1;
  min-width: 60px;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  text-align: center;
}

/* Unit Select */
.quantity-cart select {
  flex: 2;
  min-width: 100px;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  background-color: white;
  cursor: pointer;
}

/* Add to Cart Button */
.quantity-cart button {
  flex: 1;
  min-width: 100px;
  padding: 8px;
  background-color: #2563eb;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  text-align: center;
}

/* On very small screens (e.g., iPhone SE) */
@media (max-width: 480px) {
  .quantity-cart {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .quantity-cart input,
  .quantity-cart select,
  .quantity-cart button {
    width: 100%;
    min-width: auto;
    flex: none;
  }

  .quantity-cart button {
    padding: 10px;
    font-size: 16px;
  }
}


.message-farmer-btn {
  margin-top: 12px;
  padding: 8px 65px;
  background-color: #a8a00fff;; /* Emerald green */
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: background-color 0.2s ease;
}

.message-farmer-btn:hover {
  background-color: #0d9488;
}

/* On small screens */
@media (max-width: 600px) {
  .message-farmer-btn {
    font-size: 15px;
    padding: 10px 14px;
  }
}

    

        /* Review System */
        .review-btn {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.5rem;
            border: none;
            border-radius: 8px;
            background: #10b981;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .review-btn:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .review-box {
            background: rgba(248, 250, 252, 0.8);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }

        .star-rating {
            display: flex;
            gap: 0.2rem;
            margin-bottom: 1rem;
            justify-content: center;
        }

        .star {
            font-size: 1.5rem;
            color: #d1d5db;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star.active {
            color: #e5f50bff;
        }

        .star:hover {
            transform: scale(1.1);
        }

        .comment-box {
            display: flex;
            gap: 0.5rem;
        }

        .comment-box input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            transition: all 0.2s ease;
            
        }

        .comment-box input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .comment-box button {
            padding: 0.85rem 0.5rem;
            border: none;
            border-radius: 6px;
            background: #3b82f6;
            color: white;
            font-weight: 400;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .comment-box button:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }


 * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }

        .cart-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            margin-top: 40px;
            padding: 20px 30px;
            border-bottom: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-title {
            font-size: 28px;
            font-weight: 400;
            color: #0f1111;
            margin: 0;
        }

        .items-count {
            background: #e7f3ff;
            color: #0066c0;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .clear-cart-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fe0a0aff;
            color: #fbf4f4ff;
            border: 1px solid #fecaca;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .clear-cart-btn:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* Empty State */
        .empty-state {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .empty-content {
            text-align: center;
            max-width: 400px;
        }

        .empty-icon {
            margin-bottom: 20px;
            color: #9ca3af;
        }

        .empty-content h2 {
            font-size: 24px;
            color: #111827;
            margin-bottom: 10px;
            font-weight: 400;
        }

        .empty-content p {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .continue-shopping-btn {
            background: #ff9500;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 10.2s;
        }

        .continue-shopping-btn:hover {
            background: #e88500;
        }

        /* Cart Layout */
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            align-items: start;
        }

        /* Items List */
        .cart-items {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .items-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .items-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            color: #111827;
        }

        .items-list {
            padding: 0;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            padding: 25px 30px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .cart-item:hover {
            background: #fafafa;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-name {
            font-size: 16px;
            font-weight: 500;
            color: #0066c0;
            margin: 0;
            line-height: 1.3;
            cursor: pointer;
        }

        .item-name:hover {
            color: #c7511f;
            text-decoration: underline;
        }

        .item-description {
            color: #565959;
            font-size: 14px;
            margin: 0;
            line-height: 1.4;
        }

        .item-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 5px 0;
        }

        .item-price {
            font-size: 18px;
            font-weight: 700;
            color: #b12704;
        }

        .item-unit {
            color: #565959;
            font-size: 14px;
        }

        .item-availability {
            color: #007600;
            font-size: 14px;
            font-weight: 500;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 0px;
        }

        .quantity-selector label {
            font-size: 12px;
            color: #111827;
            font-weight: 100;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 2px;
            background: white;
        }

        .qty-btn {
            background: none;
            border: none;
            padding: 4px 2px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
            transition: background 0.2s;
        }

        .qty-btn:hover:not(:disabled) {
            background: #f3f4f6;
        }

        .qty-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .qty-input {
            border: none;
            width: 45px;
            text-align: center;
            padding: 6px 6px;
            font-size: 8px;
            background: none;
            outline: none;
        }

        .unit-label {
            font-size: 14px;
            color: #6b7280;
            margin-left: 5px;
        }

  

        .item-total {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .total-price {
            font-size: 18px;
            font-weight: 700;
            color: #b12704;
        }

        /* Order Summary */
        .order-summary {
            position: sticky;
            top: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .summary-title {
            font-size: 18px;
            font-weight: 500;
            color: #111827;
            margin: 0;
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .summary-details {
            padding: 20px 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-row span:first-child {
            color: #374151;
        }

        .summary-row span:last-child {
            font-weight: 500;
            color: #111827;
        }

        .summary-total {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-row span:first-child {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }

        .total-amount {
            font-size: 20px;
            font-weight: 700;
            color: #b12704;
        }

        .summary-actions {
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checkout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #ff9500;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }

        .checkout-btn:hover {
            background: #e88500;
        }

        .continue-btn {
            background: white;
            color: #0066c0;
            border: 1px solid #d5d9d9;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .continue-btn:hover {
            background: #f7f8f8;
            border-color: #adb1b8;
        }

        .trust-badges {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 12px;
            color: #565959;
        }

        .badge:last-child {
            margin-bottom: 0;
        }

        .badge svg {
            color: #007600;
        }

/* Remove Button */
.remove-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0rem 0rem;
  border-radius: 0px;
  margin-top: 0px;
  cursor: pointer;
  width: 10%;

}
.remove-btn:hover {
  background: #dc2626;
}

/* Responsive Remove Button */
.remove-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0rem 0rem; /* More touch-friendly on mobile */
  border-radius: 4px; /* Slightly rounded for better UX */
  cursor: pointer;
  width: auto; /* Don’t force 20%—use natural width or max-content */
  min-width: 40px; /* Ensure it’s tappable on mobile */
  font-size: 0.85rem; /* ~14px — readable on small screens */
  font-weight: 500;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25); /* Slightly softer shadow */
  transition: background 0.2s ease, transform 0.1s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.remove-btn:hover {
  background: #dc2626;
}

/* Better mobile tap feedback */
.remove-btn:active {
  transform: scale(0.96);
}

/* Optional: If you *must* limit width on small screens */
@media (max-width: 480px) {
  .remove-btn {
    padding: 0.3rem 0.1rem;
    font-size: 0.9rem;
    min-width: 40px;
  }
}

/* If used inside a flex/grid container, this prevents overflow */
.remove-btn {
  flex-shrink: 0;
}

/* ✅ CLEAN PAYMENT SECTION — JUST BELOW HEADER */
.payment-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 2rem;
  max-width: 500px;
  margin:2rem auto 3rem; /* No top margin — flows naturally after header */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  width: 100%;
}
.payment-section h2 {
  text-align: center;
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
}

/* Delivery Info */
.delivery-info label {
  display: block;
  margin: 1.25rem 0 0.5rem;
  font-weight: 600;
  color: #374151;
}
.address-input,
.delivery-select {
  width: 100%;
  padding: 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 1rem;
  background: #ffffff;
  transition: all 0.2s;
}
.address-input:focus,
.delivery-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

/* Order Summary */
.order-summary {
  margin: 1.5rem 0;
  padding-top: 1.5rem;
  border-top: 1px solid #f1f5f9;
}
.order-summary h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}
.summary-items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.summary-item {
  display: flex;
  justify-content: space-between;
  font-size: 1rem;
  color: #475569;
}
.summary-total {
  display: flex;
  justify-content: space-between;
  font-size: 1.25rem;
  font-weight: 700;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
  color: #1e293b;
}

/* Payment Methods */
.payment-methods h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 1.5rem 0 1rem;
}

/* Buttons & Errors */
.stripe-pay-button,
.pay-btn {
  width: 100%;
  padding: 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.stripe-pay-button:hover,
.pay-btn:hover {
  background: #2563eb;
}
.stripe-pay-button:disabled,
.pay-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.payment-error {
  background: #fee2e2;
  color: #b91c1c;
  padding: 0.75rem;
  border-radius: 8px;
  margin-top: 1rem;
  text-align: center;
  font-weight: 500;
}

.payment-processing {
  text-align: center;
  color: #3b82f6;
  font-weight: 500;
  margin-top: 0.5rem;
}

/* Different positioning options - use one of these: */

/* Close to top */
.payment-section.near-top {
    top: 20px;
}

/* A bit lower */
.payment-section.medium-top {
    top: 80px;
}

/* Much lower */
.payment-section.lower-top {
    top: 150px;
}

/* Center of screen */
.payment-section.center-screen {
    top: 50%;
    transform: translate(-50%, -50%);
}

/* Alternative: If you want it to be part of document flow but at very top */
.payment-section-static {
    background: #ffffff;
    border-radius: 0 0 12px 12px;
    padding: 2rem;
    max-width: 500px;
    margin: 0 auto 2rem; /* No top margin, bottom margin for spacing */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    border-top: none;
    position: relative;
}

.payment-section h2 {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
}

.order-summary {
    margin-bottom: 1.5rem;
}

.order-summary h3 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #374151;
    font-weight: 600;
}

.summary-items {
    margin: 1rem 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    font-size: 0.9rem;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    font-size: 1.1rem;
}

.payment-methods {
    margin-top: 1.5rem;
}

.payment-methods h3 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #374151;
    font-weight: 600;
}

.card-details input, .payment-section select {
    width: 100%;
    padding: 0.75rem;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.card-details input:focus, .payment-section select:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.stripe-pay-button,
.pay-now-btn {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.stripe-pay-button:hover,
.pay-now-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

/* Add padding to body to prevent content from hiding behind fixed payment section */
body.payment-active {
    padding-top: 200px; /* Adjust based on payment section height */
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .payment-section {
        max-width: 100%;
        margin: 0;
        border-radius: 0;
        padding: 1.5rem;
    }
    
    .payment-section-static {
        max-width: 100%;
        margin: 0 0 2rem;
        border-radius: 0;
        padding: 1.5rem;
    }
    
    body.payment-active {
        padding-top: 150px; /* Adjust for mobile */
    }
}

/* Disable main scroll when in messages */
.main-view {
  padding: 2rem;
  padding-bottom: 80px;
  overflow-y: auto;
  flex: 1;
}

.main-view.messages-active {
  overflow: hidden;
}

/* MESSAGING LAYOUT - Light Green & White Theme */
.messaging-section {
  position: fixed;
  width: 100%;
  top: 58px;
  bottom: 80px;
  left: 0;
  right: 0;
  background: #f0fdf4; /* Light green background */
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  z-index: 900;
  padding: 10px;
}

.messaging-container {
  display: flex;
  width: 100%;
  height: 85vh;
  background: #ffffff;
  border-radius: 0; /* Removed rounded corners */
  overflow: hidden;
  box-shadow: none; /* Removed shadow */
  margin-top: 60px;
}

/* LEFT SIDEBAR */
.conversation-list {
  width: 25%;
  background: #ffffff;
  border-right: 1px solid #dcfce7; /* Light green border */
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #166534;
}

.conversation-header {
  padding: 18px 16px;
  border-bottom: 1px solid #dcfce7;
  flex-shrink: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.conversation-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #166534;
}

.search-box {
  width: 75%;
  padding: 12px 16px;
  border-radius: 0; /* Removed rounded corners */
  border: 1px solid #bbf7d0;
  background: #ffffff;
  color: #166534;
  font-size: 0.9rem;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.search-box::placeholder {
  color: #86efac;
}

.search-box:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: none; /* Removed shadow */
}

.conversation-items {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
  flex: 1;
}

.conversation {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f0fdf4;
  transition: all 0.2s ease;
  position: relative;
}

.conversation:hover {
  background: #f0fdf4;
}

.conversation.active {
  background: #dcfce7;
  border-left: 3px solid #22c55e;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid #dcfce7;
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
  color: #166534;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.info .top small {
  font-size: 0.75rem;
  color: #86efac;
  flex-shrink: 0;
}

.last {
  font-size: 0.8rem;
  color: #4ade80;
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
  border-radius: 0; /* Removed rounded corners */
  font-weight: 600;
  flex-shrink: 0;
  box-shadow: none;
}

.chat-area {
  width: 75%;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  height: 100%;
  position: relative;
  color: #166534;
  padding-bottom: 70px;
}

.chat-header {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #ffffff;
  padding: 14px 20px;
  border-bottom: 1px solid #dcfce7;
  flex-shrink: 0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.chat-header .avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  border: 1px solid #dcfce7;
  box-shadow: none;
}

.chat-header h4 {
  color: #166534;
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-header small {
  color: #4ade80;
  font-size: 0.85rem;
  font-weight: 500;
}

.messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 12px;
  scroll-behavior: smooth;
  max-height: calc(100vh - 300px);
  position: relative;
  z-index: 5;
}

.messages-wrapper {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 12px;
  scroll-behavior: smooth;
}

.reply-indicator {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 0; /* Removed rounded corners */
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  box-shadow: none;
  margin: 0 24px 8px 24px;
  max-width: calc(100% - 48px);
  max-height: 80px;
  overflow: hidden;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .messaging-section {
    bottom: 90px;
  }
}

/* DATE HEADER */
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
  background: linear-gradient(to right, transparent, #dcfce7, transparent);
  z-index: 1;
}

.date-header span {
  background-color: #f0fdf4;
  padding: 8px 20px;
  border-radius: 0; /* Removed rounded corners */
  font-size: 0.85rem;
  color: #4ade80;
  font-weight: 500;
  position: relative;
  z-index: 2;
  box-shadow: none;
  border: 1px solid #dcfce7;
}

/* MESSAGE STYLING */
.message-group {
  display: flex;
  margin-bottom: 8px;
  align-items: flex-start;
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
}

.grouped-message:last-child {
  margin-bottom: 0;
}

.bubble {
  position: relative;
  max-width: 100%;
  margin-bottom: 0;
  padding: 12px 16px;
  font-size: 0.95rem;
  line-height: 1.4;
  word-wrap: break-word;
  border-radius: 0; /* Removed rounded corners */
  background: #22c55e;
  color: #fff;
  box-shadow: none;
}

.message.sent .bubble {
  background: #22c55e;
  color: #ffffff;
  border-radius: 0; /* Removed rounded corners */
  padding: 12px 16px;
  box-shadow: none;
  font-size: 0.95rem;
  line-height: 1.4;
  word-wrap: break-word;
}

.message.received .bubble {
  background: #f0fdf4;
  color: #166534;
  border-radius: 0; /* Removed rounded corners */
  padding: 12px 16px;
  box-shadow: none;
  font-size: 0.95rem;
  line-height: 1.4;
  word-wrap: break-word;
}

.timestamp {
  display: block;
  text-align: right;
  font-size: 0.75rem;
  color: #86efac;
  margin-top: 6px;
  font-weight: 500;
}

.message.sent .timestamp {
  color: rgba(255, 255, 255, 0.85);
}

.message.received .timestamp {
  color: #86efac;
  text-align: left;
}

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
  border-radius: 0; /* Removed rounded corners */
  object-fit: cover;
  box-shadow: none;
  border: 1px solid #dcfce7;
}

.file-attachment {
  background: #f0fdf4;
  color: #166534;
  padding: 8px 12px;
  border-radius: 0; /* Removed rounded corners */
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: 1px solid #dcfce7;
}

.message-options {
  position: absolute;
  top: 8px;
  right: 8px;
  cursor: pointer;
  font-size: 18px;
  opacity: 0;
  transition: opacity 0.2s ease;
  color: #4ade80;
  z-index: 20;
  background: none;
  border: none;
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
  background-color: #f0fdf4;
  color: #22c55e;
}

.options-menu {
  position: absolute;
  top: 28px;
  right: 0;
  background: #ffffff;
  border: 1px solid #dcfce7;
  border-radius: 0; /* Removed rounded corners */
  box-shadow: none;
  z-index: 1000;
  min-width: 140px;
  overflow: hidden;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease;
}

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
  color: #166534;
  transition: background 0.2s;
  font-weight: 500;
}

.options-menu button:hover {
  background: #f0fdf4;
  color: #22c55e;
}

.inline-reply-preview {
  background: #f0fdf4;
  border-left: 2px solid #22c55e;
  padding: 6px 10px;
  margin-bottom: 10px;
  border-radius: 0; /* Removed rounded corners */
  max-width: 100%;
}

.reply-label {
  font-size: 0.75rem;
  color: #22c55e;
  font-weight: 600;
  display: block;
  margin-bottom: 3px;
}

.reply-text {
  font-size: 0.85rem;
  color: #166534;
  margin: 0;
  line-height: 1.3;
  font-style: italic;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
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
  color: #86efac;
  padding: 0;
  border-radius: 0; /* Removed rounded corners */
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cancel-reply:hover {
  color: #22c55e;
  background-color: #f0fdf4;
}

.reply-content {
  font-size: 0.9rem;
  color: #166534;
  padding: 4px 0;
  border-top: 1px solid #dcfce7;
  margin-top: 2px;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-window {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.input-row {
  display: flex;
  align-items: center;
  padding: 16px 24px;
  background: #ffffff;
  border-top: 1px solid #dcfce7;
  position: relative;
  z-index: 10;
  flex-shrink: 0;
  min-height: 70px;
  gap: 12px;
  margin-top: auto;
}

.input-tools button {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #4ade80;
  padding: 10px;
  border-radius: 0; /* Removed rounded corners */
  transition: background 0.2s, color 0.2s;
}

.input-tools button:hover {
  background: #f0fdf4;
  color: #22c55e;
}

.input-row input {
  flex: 1;
  padding: 12px 14px;
  border-radius: 0; /* Removed rounded corners */
  border: 1px solid #bbf7d0;
  outline: none;
  background: #ffffff;
  color: #166534;
  font-size: 1rem;
  line-height: 1.4;
  box-shadow: none;
  min-height: 48px;
}

.input-row input::placeholder {
  color: #86efac;
}

.input-row input:focus {
  border-color: #22c55e;
  box-shadow: none;
}

.input-row button {
  background: #22c55e;
  color: white;
  border: none;
  border-radius: 0; /* Removed rounded corners */
  padding: 14px 24px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.2s ease;
  box-shadow: none;
  min-height: 48px;
}

.input-row button:hover {
  background: #16a34a;
  transform: none; /* Removed transform */
}

.input-row button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: #86efac;
}

/* Emoji Picker */
.emoji-picker {
  position: absolute;
  bottom: 100px;
  left: 24px;
  background: #ffffff;
  border: 1px solid #dcfce7;
  border-radius: 0; /* Removed rounded corners */
  padding: 14px;
  box-shadow: none;
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 10px;
  max-width: 240px;
  max-height: 180px;
  overflow-y: auto;
  z-index: 1000;
}

.emoji-option {
  text-align: center;
  cursor: pointer;
  font-size: 22px;
  padding: 6px;
  border-radius: 0; /* Removed rounded corners */
  transition: background 0.2s;
}

.emoji-option:hover {
  background: #f0fdf4;
}

/* Mobile view */
@media (max-width: 768px) {
  .messaging-section {
    position: fixed;
    top: 60px;
    bottom: 60px;
    left: 0;
    right: 0;
    height: calc(90vh - 60px);
    margin: 0;
    padding: 0;
  }

  .messaging-container {
    height: 100%;
    border-radius: 0;
    margin-top: 0;
    flex-direction: column;
    background: #ffffff;
  }

  .conversation-list {
    width: 100% !important;
    height: 100vh !important;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 20;
    background: #ffffff;
    border-right: none;
    border-bottom: 1px solid #dcfce7;
    transition: transform 0.3s ease;
  }

  .conversation-list.hidden {
    transform: translateX(-100%);
    pointer-events: none;
  }

  .chat-area {
    width: 100% !important;
    height: 90vh !important;
    background: #ffffff;
    position: relative;
    z-index: 10;
  }

  .chat-header {
    background: #ffffff;
    border-bottom: 1px solid #dcfce7;
  }

  .back-button {
    color: #22c55e;
    font-weight: bold;
  }

  .messages {
    max-height: calc(100vh - 220px);
    padding: 12px;
  }

  .input-row {
    padding: 12px;
    background: #ffffff;
    border-top: 1px solid #dcfce7;
  }

  .input-row input {
    padding: 10px 16px;
    font-size: 0.95rem;
    border-radius: 0;
  }

  .input-row button {
    padding: 10px 18px;
    font-size: 0.9rem;
  }
}

@media (max-width: 768px) {
  .conversation-list.mobile-hidden {
    display: none;
  }
}


/* Ensure modal appears above everything, even inside messaging section */
.custom-confirm-overlay {
  position: fixed; /* ← critical: not absolute */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000; /* above all messaging UI */
}

.custom-confirm-modal {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  max-width: 300px;
  width: 90%;
}


/* Title styling – moved down by top margin */
.card-section h2 {
  font-size: 1.6rem;
  font-weight: 600;
  margin-top: 3.5rem; /* Pushes it below any previous element like a nav */
  margin-bottom: 0.5rem;
  color: #1f2937;
}

/* Grid of order cards */
.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  width: 100%;
}

/* Individual order card */
.order-card {
  background: #f9f9f9;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  padding: 1rem;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Header row in each order card */
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.7rem;
}

/* Badge styling */
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
  border: 1px solid transparent;
}

.status-badge.pending {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.2);
}

.status-badge.paid {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-color: rgba(16, 185, 129, 0.2);
}

.status-badge.delivered {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border-color: rgba(59, 130, 246, 0.2);
}

/* Order details */
.order-date,
.order-total {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.order-items {
  list-style: none;
  padding-left: 0;
  margin: 0.5rem 0;
  font-size: 0.9rem;
}

/* Status message blocks */
.order-status-msg {
  margin-top: 0.8rem;
  padding: 0.5rem;
  border-radius: 6px;
  font-size: 0.9rem;
}

.order-status-msg.pending {
  background-color: #fff7ed;
  color: #b45309;
}

.order-status-msg.waiting {
  background-color: #eff6ff;
  color: #1d4ed8;
}

.order-status-msg.delivered {
  background-color: #ecfdf5;
  color: #047857;
}

/* Buttons */
.pay-now-btn {
  margin-top: 0.5rem;
  padding: 6px 10px;
  background-color: #22c55e;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.pay-now-btn:hover {
  background-color: #16a34a;
}

.delete-order-btn {
  margin-top: 10px;
  background: #e53e3e;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.delete-order-btn:hover {
  background-color: #c53030;
}

/* Empty state styling */
.empty-state {
  text-align: center;
  color: #6b7280;
  margin-top: 2rem;
  width: 100%;
}

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-badge.paid {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-badge.delivered {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* Reviews List */
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .review-item {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .review-product {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .star.active {
            color: #f59e0b;
        }

        .rating-number {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        .review-comment {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-style: italic;
        }

        .farmer-reply-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #059669;
            font-weight: 500;
        }

        .reply-icon {
            font-size: 0.9rem;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f1f5f9;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error Alert */
        .error-alert {
            background: #fdfcfcff;
            color: #fa0000ff;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(13, 13, 13, 0.56);
        }

        .close-error {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: auto;
        }

 
.feedback-section {
  margin-top: 2.5rem;
  padding: 1rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.2rem;
}

.feedback-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.feedback-card {
  background: #ffffff;
  border-radius: 8px;
  padding: 1.2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.feedback-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-name {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.stars {
  margin: 0.5rem 0;
}

.star {
  color: #cbd5e0;
  font-size: 1.2rem;
}

.star.active {
  color: #f59e0b;
}

.timestamp {
  display: block;
  font-size: 0.8rem;
  color: #718096;
  margin-bottom: 0.5rem;
}

.farmer-reply {
  background: #059669;
  color: white;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.no-feedback {
  font-style: italic;
  color: #718096;
  padding: 1rem;
}
/* Notification Toast */
.notification {
  position: fixed;
  top: 80px; /* Below header (your header is ~58px + some padding) */
  right: 20px;
  z-index: 9999;
  padding: 12px 20px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  max-width: 300px;
  word-wrap: break-word;
  animation: slideIn 0.3s ease forwards;
}

.notification.info {
  background-color: #3b82f6; /* blue */
}
.notification.success {
  background-color: #10b981; /* green */
}
.notification.error {
  background-color: #ef4444; /* red */
}
.notification.warning {
  background-color: #f59e0b; /* amber */
}

@keyframes slideIn {
  from {
    transform: translateX(120%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Mobile Styles */
@media (max-width: 768px) {
  /* Adjust main view padding */
  .main-view {
    padding: 1rem; /* Reduce padding */
    padding-bottom: 6rem; /* Increase bottom padding to account for potentially taller nav */
  }

  /* Adjust search bar */
  .search-bar {
    margin: 4rem 1rem 1rem; /* Reduce top margin, add side margins */
  }
  .search-input-wrapper {
    padding: 0.75rem; /* Reduce padding */
  }
  .search-input-wrapper input {
    font-size: 0.9rem; /* Slightly smaller font */
  }

  /* Adjust filter buttons */
  .filter-controls {
    gap: 0.5rem; /* Smaller gap */
  }
  .filter-btn {
    padding: 0.5rem 1rem; /* Smaller padding */
    font-size: 0.9rem; /* Smaller font */
  }

  /* Adjust product grid */
  .product-grid {
    grid-template-columns: 1fr; /* Force single column on small screens */
    gap: 1rem; /* Smaller gap */
  }


  /* Cart Layout */
  .cart-layout {
    grid-template-columns: 1fr; /* Stack items and summary */
    gap: 1.5rem; /* Adjust gap */
  }
  .order-summary {
    position: static; /* Remove sticky positioning */
  }

  /* Adjust cart item layout */
  .cart-item {
    grid-template-columns: 80px 1fr; /* Smaller image, stack details */
    gap: 10px;
    padding: 15px;
  }
  .item-image {
    width: 80px;
    height: 80px;
  }
  /* Hide unit label or adjust */
  .unit-label {
    font-size: 0.8rem; /* Smaller font */
  }

  /* Adjust header */
  .dashboard-header {
    padding: 0.75rem 1rem; /* Reduce padding */
  }
  .greeting-center h2 {
    font-size: 1.3rem; /* Smaller font */
  }
  .greeting-center p {
    font-size: 0.85rem; /* Smaller font */
  }
  .profile-avatar {
    width: 40px; /* Smaller avatar */
    height: 40px;
  }

  /* Adjust payment section */
  .payment-section {
    top: 60px; /* Adjust top position */
    padding: 1.5rem; /* Reduce padding */
    max-width: 100%; /* Full width */
    border-radius: 0; /* Or specific mobile radius */
  }

  /* Adjust order card layout */
  .order-card {
    padding: 0.75rem; /* Reduce padding */
  }
  .order-header {
    flex-direction: column; /* Stack order number and status */
    align-items: flex-start;
    gap: 0.5rem;
  }

  /* Adjust feedback card layout */
  .feedback-card {
    padding: 1rem; /* Reduce padding */
  }

  /* Adjust quantity cart buttons/inputs */
  .quantity-cart {
    flex-direction: column; /* Stack input, select, button */
    align-items: flex-start;
    gap: 5px;
  }
  .quantity-cart input,
  .quantity-cart select,
  .quantity-cart button {
    width: 100%; /* Full width */
    max-width: 100%; /* Ensure full width */
    padding: 0.5rem; /* Adjust padding */
  }

  /* Adjust message farmer button */
  .message-farmer-btn {
    padding: 0.5rem 1rem; /* Adjust padding */
  }

  /* Adjust review button */
  .review-btn {
    padding: 0.5rem; /* Adjust padding */
  }

  /* Adjust review box */
  .review-box {
    padding: 0.75rem; /* Reduce padding */
  }
  .comment-box {
    flex-direction: column; /* Stack input and button */
  }
  .comment-box input,
  .comment-box button {
    width: 100%; /* Full width */
  }

  /* Adjust star size */
  .star {
    font-size: 1.1rem; /* Smaller stars */
  }

  /* Adjust cart header */
  .cart-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 10px;
  }
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  .items-count {
    font-size: 0.9rem; /* Smaller font */
  }
  .clear-cart-btn {
    align-self: flex-end; /* Align button to the end */
  }

  /* Adjust order summary details */
  .summary-row {
    font-size: 0.85rem; /* Smaller font */
  }
  .total-row {
    font-size: 0.9rem; /* Smaller font */
  }

  /* Adjust messaging input */
  .input-row input {
    padding: 10px; /* Adjust padding */
    font-size: 0.9rem; /* Smaller font */
  }
  .input-row button {
    padding: 10px 15px; /* Adjust padding */
    font-size: 0.9rem; /* Smaller font */
  }

  /* Adjust messages container height */
  .messages {
    max-height: calc(100vh - 250px); /* Adjust based on header + input height on mobile */
  }
}

/* Tablet Styles (Optional, between mobile and desktop) */
@media (min-width: 769px) and (max-width: 1024px) {
  /* Add specific styles for tablets if needed */
  /* For example, maybe 2 columns in product grid instead of 1 or many */
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  }
  /* Maybe adjust messaging to show both panels but narrower */
  .messaging-container {
    height: 75vh; /* Adjust height */
  }
  .conversation-list {
    width: 30%; /* Adjust width */
  }
  .chat-area {
    width: 70%; /* Adjust width */
  }
  /* Adjust cart layout */
  .cart-layout {
    grid-template-columns: 2fr 1fr; /* Different ratio */
  }
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
.mobile-section-back-button {
  display: none;
  background: none;
  border: none;
  font-size: 16px;
  padding: 12px 16px;
  color: #007bff;
  font-weight: 600;
  cursor: pointer;
}

@media (max-width: 768px) {
  .mobile-section-back-button {
    display: block;
  }

  /* Optionally, hide default header when back button is visible */
  .conversation-header h3 {
    margin-top: 0;
  }
}
/* Custom Confirm Overlay */
.custom-confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.custom-confirm-modal {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  max-width: 320px;
  width: 90%;
  font-size: 1rem;
  color: #1e293b;
}

.custom-confirm-modal p {
  margin: 0 0 1.25rem;
  line-height: 1.5;
}

/* Confirm Buttons */
.confirm-buttons {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}

.confirm-buttons button {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  flex: 1;
}

.btn-cancel {
  background-color: #ed0f0fff;
  color: #64748b;
}

.btn-cancel:hover {
  background-color: #e61a1aff;
}

.btn-confirm {
  background-color: #1cf12eff; /* red for destructive action */
  color: white;
}

.btn-confirm:hover {
  background-color: #0aeb1dff;
}

/* Cart Tab Badge */
.cart-tab-icon {
  position: relative;
  display: inline-block;
}

.cart-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background-color: #ef4444; /* red */
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
    </style>