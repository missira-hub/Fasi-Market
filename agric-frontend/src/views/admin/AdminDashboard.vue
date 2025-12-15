<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-hidden': sidebarHidden }">
      <div class="logo-container">
        <div class="logo">
          <i class="fas fa-leaf"></i>
          <span>FASI<span>MARKET</span></span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li 
            @click="switchSection('overview')" 
            :class="{ active: section === 'overview' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-tachometer-alt"></i></i>
            <span class="nav-text">Dashboard</span>
          </li>
          <li 
            @click="switchSection('users')" 
            :class="{ active: section === 'users' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-users"></i></i>
            <span class="nav-text">Users</span>
          </li>
          <li 
            @click="switchSection('products')" 
            :class="{ active: section === 'products' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-seedling"></i></i>
            <span class="nav-text">Products</span>
          </li>
          <li 
            @click="switchSection('feedback')" 
            :class="{ active: section === 'feedback' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-comments"></i></i>
            <span class="nav-text">Feedback</span>
          </li>
          <li 
            class="nav-item logout" 
            @click="handleLogout"
          >
            <i class="icon"><i class="fas fa-sign-out-alt"></i></i>
            <span class="nav-text">Logout</span>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer"></div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper" :style="{ marginLeft: sidebarHidden ? '0' : '280px' }">
      <!-- Dashboard Header -->
      <header class="dashboard-header">
        <button @click="toggleSidebar" class="hamburger-icon">☰</button>
        <div class="greeting">
          <h2>Hello, {{ currentUser?.name || 'Admin' }}</h2>
          <p>
            Welcome back to your admin panel 
            <span v-if="currentUser?.role">— Role: {{ currentUser.role }}</span>
          </p>
        </div>
        <div class="user-profile" @click="openProfileModal" title="Click to update your profile">
          <img
  v-if="currentUser?.avatar_url"
  :src="currentUser.avatar_url.startsWith('http') 
    ? currentUser.avatar_url 
    : 'http://localhost:8000' + currentUser.avatar_url + '?t=' + Date.now()"
  alt="Admin Avatar"
  class="profile-picture clickable"
/>
          <div v-else class="profile-picture placeholder">👤</div>
        </div>
      </header>

      <!-- Scrollable Main Content -->
      <ProfileModal
        v-if="profileModalOpen"
        @close="closeProfileModal"
        @updated="onProfileUpdated"
      />

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading dashboard data...</p>
      </div>

      <!-- Dashboard Content -->
      <div v-else class="content-area">
        <!-- Overview Section -->
        <section v-if="section === 'overview'" class="overview">
          <!-- Stats Cards -->
          <div class="stats-row">
            <div class="stat-card revenue" @click="switchSection('overview')">
              <div class="card-icon"><i class="fas fa-coins"></i></div>
              <div class="card-content">
                <h3>Recurring Revenue</h3>
                <p class="value">₺{{ formatNumber(monthlyRevenue) }}</p>
                <div class="trend-indicator" :class="revenueGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="revenueGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
            <div class="stat-card products" @click="switchSection('products')">
              <div class="card-icon"><i class="fas fa-box-open"></i></div>
              <div class="card-content">
                <h3>Products</h3>
                <p class="value">{{ productCount }}</p>
                <div class="trend-indicator" :class="accountGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="accountGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
            <div class="stat-card users" @click="switchSection('users')">
              <div class="card-icon"><i class="fas fa-user-friends"></i></div>
              <div class="card-content">
                <h3>Active Users</h3>
                <p class="value">{{ userCount }}</p>
                <div class="trend-indicator" :class="userGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="userGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="charts-row">
             
            <div class="chart-container health-chart">
              <div class="chart-header"><h3>Farm Health Status</h3></div>
              <div class="doughnut-wrapper">
                <div class="doughnut-chart" :style="`--value: ${healthStrongPercent}%`"></div>
                <div class="doughnut-label">
                  <span class="label-value">{{ healthStrongPercent }}%</span>
                  <span class="label-text">Healthy Farms</span>
                </div>
              </div>
              <div class="legend">
                <div class="legend-item"><span class="indicator strong"></span><span>Strong</span></div>
                <div class="legend-item"><span class="indicator weak"></span><span>Needs Attention</span></div>
              </div>
            </div>
          </div>

          <!-- Recent Activity Table -->
          <div class="table-section">
            <div class="table-header">
              <h3>Recent Activity</h3>
              <button class="view-all-btn">View All <i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="table-container">
              <table>
                <thead>
                  <tr><th>Name</th><th>Stage</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                  <tr v-for="activity in recentActivities" :key="activity.id">
                    <td>
                      <div class="user-info">
                        <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
                        <div class="user-details">
                          <p class="user-name">{{ activity.title || 'Unknown' }}</p>
                          <p class="user-role">Farmer</p>
                        </div>
                      </div>
                    </td>
                    <td>{{ activity.description || 'No stage' }}</td>
                    <td><span class="status-badge completed">Completed</span></td>
                    <td><button class="action-btn"><i class="fas fa-ellipsis-v"></i></button></td>
                  </tr>
                  <tr v-if="recentActivities.length === 0">
                    <td colspan="4" class="no-data"><i class="fas fa-inbox"></i><p>No activity found</p></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Dynamic Sections -->
        <section v-if="section === 'users'" class="section-container"><ManageUsers /></section>
        <section v-if="section === 'products'" class="section-container"><ManageProducts /></section>
        <section v-if="section === 'feedback'" class="section-container"><ViewFeedback /></section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import ManageUsers from './ManageUsers.vue'
import ManageProducts from './ManageProducts.vue'
import ViewFeedback from './ViewFeedback.vue'
import { useAuthStore } from '@/stores/auth'
import ProfileModal from '@/components/ProfileModal.vue'

// === State ===
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user)
const section = ref('overview')
const loading = ref(false)
const profileModalOpen = ref(false)
const sidebarHidden = ref(false)

// Dashboard data refs
const userCount = ref(0)
const productCount = ref(0)
const monthlyRevenue = ref(0)
const revenueGrowth = ref(0)
const accountGrowth = ref(0)
const userGrowth = ref(0)
const recentActivities = ref([])
const healthStrongPercent = ref(75)
const mrrChartCanvas = ref(null)
let mrrChart = null
const chartLabels = ref([])
const chartData = ref([])

Chart.register(...registerables)

// === Methods ===
const toggleSidebar = () => {
  sidebarHidden.value = !sidebarHidden.value
}
const isMobile = computed(() => window.innerWidth <= 768)
const switchSection = (newSection) => {
  section.value = newSection
if (isMobile.value) sidebarHidden.value = true  

nextTick(() => {
    document.querySelector('.content-area')?.scrollTo({ top: 0, behavior: 'smooth' })
  })
}

const getAuthHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json'
})

const formatNumber = (num) => Number(num || 0).toLocaleString('tr-TR')

// --- Fetch functions ---
const fetchDashboardData = async () => {
  loading.value = true
  try {
    const month = new Date().toISOString().slice(0, 7) // YYYY-MM
    const res = await axios.get(`http://127.0.0.1:8000/api/admin/dashboard-stats?month=${month}`, { headers: getAuthHeaders() })
    const d = res.data
    userCount.value = d.user_count || 0
    productCount.value = d.product_count || 0
    monthlyRevenue.value = d.monthly_revenue || 0
    revenueGrowth.value = d.revenue_growth_percentage || 0
    accountGrowth.value = d.account_growth_percentage || 0
    userGrowth.value = d.user_growth_percentage || 0
    recentActivities.value = Array.isArray(d.recent_activities) ? d.recent_activities : []
    healthStrongPercent.value = d.health_strong_percentage || 75
  } catch (err) {
    console.error('Dashboard fetch error:', err)
    alert('Could not load dashboard data.')
  } finally {
    loading.value = false
  }
}

const fetchChartData = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/admin/mrr-over-time', { headers: getAuthHeaders() })
    const data = res.data?.data || []
    if (data.length > 0) {
      chartLabels.value = data.map(item => item.month)
      chartData.value = data.map(item => item.value)
    } else {
      chartLabels.value = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
      chartData.value = [32000,34000,38000,40000,39000,42000,0,0,0,0,0,0]
    }
    await nextTick()
    renderChart()
  } catch (err) {
    console.error('Chart fetch error:', err)
  }
}

const renderChart = () => {
  if (mrrChart) mrrChart.destroy()
  const ctx = mrrChartCanvas.value?.getContext('2d')
  if (!ctx) return

  const gradient = ctx.createLinearGradient(0, 0, 0, 300)
  gradient.addColorStop(0, 'rgba(76, 175, 80, 0.8)')
  gradient.addColorStop(1, 'rgba(76, 175, 80, 0.2)')

  mrrChart = new Chart(ctx, {
    type: 'bar',
    data: { labels: chartLabels.value, datasets: [{ label: 'MRR (₺)', data: chartData.value, backgroundColor: gradient }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: true, position: 'top' },
        tooltip: { callbacks: { label: (ctx) => `₺${ctx.parsed.y.toLocaleString('tr-TR')}` } }
      },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { callback: (v) => `₺${v.toLocaleString('tr-TR')}` } }
      }
    }
  })
}

const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('No token')
    
    const res = await axios.get('/api/user', {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    // ✅ Use res.data directly (it's the user object)
    authStore.setUser(res.data)
  } catch (error) {
    console.error('Profile fetch error:', error)
    localStorage.removeItem('token')
    window.location.href = '/login'
  }
}

// === Modal Handlers ===
const openProfileModal = () => profileModalOpen.value = true
const closeProfileModal = () => profileModalOpen.value = false
const onProfileUpdated = (data) => {
  authStore.setUser(data)
  closeProfileModal()
}

const handleLogout = async () => {
  try {
    await axios.post('http://127.0.0.1:8000/api/logout', {}, { headers: getAuthHeaders() })
  } catch (e) {}
  localStorage.clear()
  window.location.href = '/login'
}

// === Lifecycle ===
onMounted(() => {
  fetchProfile()
  fetchDashboardData()
  fetchChartData()
})
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

/* === Base Reset === */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: #f8faf7;
  color: #2d3748;
  line-height: 1.6;
  overflow-x: hidden;
}

/* === Layout === */
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background: #f8faf7;
}

/* === Sidebar === */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
  color: white;
  height: 100%;
  position: fixed;
top: 85px;
  left: 0;
  bottom:1000px;
  z-index: 1000;
  overflow-y: auto;
  box-shadow: 4px 0 20px rgba(46, 125, 50, 0.15);
  transition: transform 0.3s ease;
}
.sidebar.sidebar-hidden {
  transform: translateX(-100%);
}

.logo-container {
  padding: 1.5rem 1.25rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 700;
  font-size: 1.5rem;
  color: white;
}
.logo i { font-size: 1.8rem; color: #a5d6a7; }
.logo span span { color: #a5d6a7; font-weight: 300; }

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 0;
}
.sidebar-nav ul {
  list-style: none;
  padding: 0 0.75rem;
}
.nav-item {
  padding: 0.9rem 1rem;
  cursor: pointer;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #c8e6c9;
  transition: all 0.3s ease;
  margin-bottom: 0.4rem;
  font-weight: 500;
}
.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(4px);
}
.nav-item.active {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
}
.nav-item.logout {
  margin-top: 1rem;
  color: #ffcdd2;
}
.nav-item.logout:hover {
  background: rgba(255, 87, 87, 0.1);
  color: #ef5350;
}
.icon {
  font-size: 1.1rem;
  min-width: 24px;
  text-align: center;
}
.nav-text { font-size: 0.95rem; }

/* === Header === */
.dashboard-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 90px;
  background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
  padding: 1.6rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: white;
  z-index: 90;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.hamburger-icon {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  margin-right: 1rem;
}
.greeting h2 {
  margin: 0;
  font-size: 1.7rem;
  font-weight: 600;
}
.greeting p {
  margin: 0.25rem 0 0 0;
  color: rgba(255,255,255,0.8);
  font-size: 1rem;
}
.user-profile {
  width: 60px;
  height: 50px;
  border-radius: 100%;
  overflow: hidden;
  cursor: pointer;
  border: 3px solid rgba(255,255,255,0.3);
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

/* === Main Content Wrapper === */
.main-wrapper {
  flex: 1;
  transition: margin-left 0.3s ease;
}
.content-area {
  padding: 2rem;
  padding-top: 100px; /* below fixed header */
  background: rgba(248, 249, 249, 0.9);
  min-height: calc(100vh - 85px);
}

/* === Stats Cards === */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
  position: relative;
  overflow: hidden;
}
.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(46, 125, 50, 0.12);
}
.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  border-radius: 0 16px 16px 0;
}
.stat-card.revenue::before { background: linear-gradient(to bottom, #4caf50, #2e7d32); }
.stat-card.products::before { background: linear-gradient(to bottom, #ff9800, #f57c00); }
.stat-card.users::before { background: linear-gradient(to bottom, #2196f3, #1976d2); }

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}
.stat-card.revenue .card-icon { background: linear-gradient(135deg, #4caf50, #2e7d32); }
.stat-card.products .card-icon { background: linear-gradient(135deg, #ff9800, #f57c00); }
.stat-card.users .card-icon { background: linear-gradient(135deg, #2196f3, #1976d2); }

.card-content h3 {
  font-size: 0.9rem;
  color: #718096;
  font-weight: 600;
  margin: 0 0 0.4rem 0;
}
.card-content .value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.4rem;
}
.trend-indicator {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 20px;
  width: fit-content;
}
.trend-indicator.up { background: #e8f5e8; color: #2e7d32; }
.trend-indicator.down { background: #ffebee; color: #c62828; }

/* === Charts === */
.charts-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.chart-container {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
}
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.chart-header h3 {
  font-size: 1.1rem;
  color: #2d3748;
  font-weight: 600;
}
.chart-actions { display: flex; gap: 0.5rem; }
.chart-action-btn {
  background: #f1f8e9;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4caf50;
  cursor: pointer;
  transition: all 0.2s ease;
}
.chart-action-btn:hover {
  background: #4caf50;
  color: white;
}
.chart-wrapper { height: 250px; }

/* Health Chart */
.health-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}
.doughnut-wrapper {
  width: 140px;
  height: 140px;
  margin: 0.5rem auto 1.5rem;
  position: relative;
}
.doughnut-chart {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: conic-gradient(#4caf50 0% var(--value), #ffcc80 var(--value) 100%);
  box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2);
}
.doughnut-chart::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80px;
  height: 80px;
  background: white;
  border-radius: 50%;
  box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
}
.doughnut-label {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  z-index: 1;
}
.label-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1b5e20;
  display: block;
}
.label-text {
  font-size: 0.8rem;
  color: #718096;
  font-weight: 500;
}
.legend {
  display: flex;
  justify-content: center;
  gap: 1.2rem;
  font-size: 0.85rem;
  color: #718096;
  margin-top: 0.5rem;
}
.legend-item { display: flex; align-items: center; gap: 0.4rem; }
.indicator {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
}
.indicator.strong { background: #4caf50; }
.indicator.weak { background: #ffcc80; }

/* === Table === */
.table-section {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
}
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
}
.table-header h3 {
  font-size: 1.1rem;
  color: #2d3748;
  font-weight: 600;
}
.view-all-btn {
  background: none;
  border: none;
  color: #4caf50;
  font-size: 0.9rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: all 0.2s ease;
}
.view-all-btn:hover {
  color: #2e7d32;
  gap: 0.6rem;
}
.table-container {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #e8f5e8;
}
table { width: 100%; border-collapse: collapse; }
th, td {
  padding: 1rem 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e8f5e8;
}
th {
  color: #2e7d32;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #f1f8e9;
  white-space: nowrap;
}
td {
  color: #2d3748;
  font-weight: 500;
}
.user-info { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar { font-size: 1.8rem; color: #c8e6c9; }
.user-details { display: flex; flex-direction: column; }
.user-name { font-weight: 600; color: #2d3748; margin: 0; }
.user-role { font-size: 0.8rem; color: #718096; margin: 0; }
.status-badge {
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  display: inline-block;
}
.status-badge.completed {
  background: #e8f5e8;
  color: #2e7d32;
}
.action-btn {
  background: #f8faf7;
  border: 1px solid #e8f5e8;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #718096;
  cursor: pointer;
  transition: all 0.2s ease;
}
.action-btn:hover {
  background: #4caf50;
  color: white;
  border-color: #4caf50;
}
.no-data {
  text-align: center;
  padding: 2rem;
  color: #a0aec0;
}
.no-data i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  opacity: 0.5;
}
.no-data p {
  margin: 0;
  font-size: 0.9rem;
}

/* === Loading === */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #1b5e20;
}
.spinner {
  border: 4px solid rgba(76, 175, 80, 0.2);
  border-top: 4px solid #4caf50;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* === Section Container === */
.section-container {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
}

/* === Responsive === */
@media (max-width: 768px) {
  .hamburger-icon { display: block; }
  .sidebar { height: calc(100vh - 85px); top: 85px; }
  .main-wrapper { margin-left: 0 !important; }
  .content-area { padding: 1rem; padding-top: 95px; }
  .stats-row { grid-template-columns: 1fr; gap: 1rem; }
  .charts-row { grid-template-columns: 1fr; }
  .chart-wrapper { height: 200px; }
  .table-container { font-size: 0.85rem; }
  th, td { padding: 0.75rem 0.5rem; }
}
</style>