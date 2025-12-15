import { createRouter, createWebHistory } from 'vue-router'

import Home from '../views/Homepage.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'

import ConsumerDashboard from '../views/consumer/ConsumerDashboard.vue'
import ProductList from '../views/consumer/ProductList.vue'
import CartView from '../views/consumer/cartview.vue'
import Messages from '../views/consumer/Messages.vue'

import About from '../views/About.vue'
import Services from '../views/Services.vue'
import Projects from '../views/Projects.vue'
import News from '../views/News.vue'
import Shop from '../views/Shop.vue'
import Contact from '../views/Contact.vue'

// Farmer views
import FarmerDashboard from '../views/farmer/FarmerDashboard.vue'

// Admin views
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import ManageUsers from '../views/admin/ManageUsers.vue'
import ManageProducts from '../views/admin/ManageProducts.vue'
import ViewOrders from '../views/admin/ViewOrders.vue'
import ViewFeedback from '../views/admin/ViewFeedback.vue'

import Unauthorized from '../views/Unauthorized.vue'

// Lazy-loaded Payment views
const PaymentSuccess = () => import('../views/PaymentSuccess.vue')
const PaymentCancel = () => import('../views/PaymentCancel.vue')

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/about', name: 'About', component: About },
  { path: '/services', name: 'Services', component: Services },
  { path: '/projects', name: 'Projects', component: Projects },
  { path: '/news', name: 'News', component: News },
  { path: '/shop', name: 'Shop', component: Shop },
  { path: '/contact', name: 'Contact', component: Contact },

  { path: '/login', name: 'Login', component: Login },
    { path: '/forgot-password', component: ForgotPassword },

  { path: '/register', name: 'Register', component: Register },

  // Consumer routes
  {
    path: '/consumer/dashboard',
    name: 'ConsumerDashboard',  // <--- Added name here
    component: ConsumerDashboard,
    meta: { requiresAuth: true, role: 'consumer' }
  },
  { path: '/products', name: 'ProductList', component: ProductList },
  {
    path: '/consumer/cart',
    name: 'CartView',
    component: CartView,
    meta: { requiresAuth: true, role: 'consumer' }
  },
  {
    path: '/consumer/messages',
    name: 'Messages',
    component: Messages,
    meta: { requiresAuth: true, role: 'consumer' }
  },

  // Farmer route
  {
    path: '/farmer/dashboard',
    name: 'FarmerDashboard',
    component: FarmerDashboard,
    meta: { requiresAuth: true, role: 'farmer' }
  },

  // Payment Stripe callback routes
  { path: '/payment-success', name: 'PaymentSuccess', component: PaymentSuccess },
  { path: '/payment-cancel', name: 'PaymentCancel', component: PaymentCancel },

  // Unauthorized route
  { path: '/unauthorized', name: 'Unauthorized', component: Unauthorized },
]

// Admin routes with nested children (using named routes for children as well)
routes.push({
  path: '/admin/dashboard',
  name: 'AdminDashboard',
  component: AdminDashboard,
  meta: { requiresAuth: true, role: 'admin' },
  children: [
    { path: 'users', name: 'ManageUsers', component: ManageUsers },
    { path: 'products', name: 'ManageProducts', component: ManageProducts },
    { path: 'orders', name: 'ViewOrders', component: ViewOrders },
    { path: 'feedback', name: 'ViewFeedback', component: ViewFeedback },
  ],
})

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Global route guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const role = localStorage.getItem('role') // Ensure role stored on login

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.role && role !== to.meta.role) {
    return next('/unauthorized')
  }

  next()
})

export default router
