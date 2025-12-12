<template>
<div class="homepage font-poppins text-gray-800">
    <!-- Navbar -->
    <header class="navbar">
      <div class="navbar-container">
        <div class="logo-wrapper">
  <div class="background-blur"></div>
  <div class="logo">
    <img src="@/assets/logo3.png" alt="Logo" />
    <div>
      <h1 class="logo-title">FASI MARKET</h1>
      <p class="logo-subtitle">Agriculture Farming</p>
    </div>
  </div>
</div>

        <!-- Navigation Links -->
        <nav class="nav-links">
          <a href="#home" @click.prevent="scrollToSection('home')">Home</a>
          <a href="#about" @click.prevent="scrollToSection('about')">About</a>
        <a href="#offer-section" @click.prevent="scrollToSection('offer-section')">What We Offer</a>
         <a href="#services" @click.prevent="scrollToSection('services')">What We Do</a>
          <a href="#projects" @click.prevent="scrollToSection('projects')">Projects</a>
          <a href="#shop" @click.prevent="scrollToSection('shop')">Shop</a>
          <a href="#contact" @click.prevent="scrollToSection('contact')">Contact</a>
        </nav>

        <!-- Contact Info -->
        <div class="top-contact">
          <div class="contact-block">
            <img src="@/assets/phone-icon.png" alt="Phone" />
            <div>
              <p class="small-title">Call anytime</p>
              <p class="bold-info">+90 532 000 0000</p>
            </div>
          </div>
          <div class="contact-block">
            <img src="@/assets/email-icon.png" alt="Email" />
            <div>
              <p class="small-title">Send email</p>
              <p class="bold-info">support@fasi-market.com</p>
            </div>
          </div>
          <div class="contact-block">
            <img src="@/assets/location-icon.png" alt="Location" />
            <div>
              <p class="small-title">Our Location</p>
              <p class="bold-info">North Cyprus</p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
    
      <div class="hero-overlay">
        <div class="hero-content text-center">
          <h1 class="hero-slogan">
            Green life is<br />
            Harvesting Health<br />
            and Growing Trust
          </h1>

          <div class="hero-buttons">
            <a href="/login" class="login-btn">Login</a>
            <button class="discover-btn">Discover More</button>
            <a href="/register" class="register-btn">Register</a>
          </div>
        </div>
      </div>

<!-- Decorative Wave at Bottom -->
<div class="wave-bottom">
  <svg viewBox="0 0 1440 100" preserveAspectRatio="none">
    <path
      d="M0,50 
         C120,90 240,10 360,50 
         C480,90 600,10 720,50 
         C840,90 960,10 1080,50 
         C1200,90 1320,10 1440,50 
         L1440,100 L0,100 Z"
      fill="#f5f5f5"
    />
  </svg>
</div>

    </section>


<!-- MAIN CONTENT WITH WHITE BACKGROUND -->
    <div class="main-content">

<section id="offer-section" class="offer-section font-poppins">
  <h2 class="section-title">What We Offer</h2>
    <div class="offer-grid">
      <div
        class="offer-card"
        v-for="(item, index) in offers"
        :key="index"
      >
        <!-- Blurred image background -->
        <div
          class="offer-blur-bg"
          :style="{ backgroundImage: `url(${item.image})` }"
        ></div>

        <!-- Text overlay -->
        <div class="overlay">
          <h3 class="offer-title">{{ item.title }}</h3>
        </div>
      </div>
    </div>
  </section>


<section id="about" class="about-section font-poppins">
    <h2 class="section-title">About us</h2>
  <div class="about-wrapper">
    <!-- Left Image -->
    <div class="about-image">
      <img :src="image" alt="Farmer in barn" />
      <img :src="image2" alt="Farmer with crops" class="second-image" />
    </div>

    <!-- Right Content -->
    <div class="about-content">
      <h2 class="about-heading">Agriculture & Organic Product Farm</h2>
      <p class="about-description">
FASI Market is a modern agricultural marketplace that empowers local farmers by connecting them directly with consumers. Our platform eliminates middlemen, promotes fair trade, and ensures access to fresh, organic, and locally-grown products. Whether you're a farmer looking to sell your harvest or a consumer seeking quality food straight from the source, FASI Market provides a trusted space for transparent, one-on-one transactions.

Grow. Connect. Thrive — with FASI Market.      </p>

      <div class="about-stat">
        <span class="stat-value">25M</span>
        <span class="stat-label">Growth Tons<br />of Harvest</span>
      </div>

      <div class="about-features">
        <div class="feature-box yellow-box">
          <img :src="icon1" alt="icon" />
          <div>
            <h3>100% Guaranteed Organic Product</h3>
            <p>Always best farm buying the steering of moment.</p>
          </div>
        </div>

        <div class="feature-box green-box">
          <img :src="icon2" alt="icon" />
          <div>
            <h3>Top-Quality Healthy Foods Production</h3>
            <p>Majority have suffered alteration in some form by injected humour.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="services" class="services-section font-poppins">
  
  <div class="services-container">
    <h2 class="services-title">What We Do</h2>

    <div class="service-cards">
      <!-- Card 1: Direct Farmer Access -->
      <div class="service-card large-card">
        <img :src="service1" alt="Direct Farmer Access" />
        <div class="card-content">
          <h3>Direct Connections Between Farmers & Consumers</h3>
          <p>
            FASI Market bridges the gap between local farmers and buyers, enabling personal, fair, and transparent transactions without middlemen.
          </p>
          
        </div>
      </div>

      <!-- Card 2: Eco-Friendly Distribution -->
      <div class="service-card">
        <img :src="service2" alt="Eco-Friendly Distribution" />
        <div class="card-date">
          <span class="day">26</span>
          <span class="month">Aug, 2023</span>
        </div>
        <div class="card-content">
          <h4>Green & Local Product Delivery</h4>
          <p class="meta">Fasi-Market • 25 April, 2023</p>
          <p>
            We support sustainable transport and local logistics, ensuring your food travels responsibly from farm to table.
          </p>
          
        </div>
      </div>

      <!-- Card 3: Certified Organic Products -->
      <div class="service-card">
        <img :src="service3" alt="Certified Organic Products" />
        <div class="card-date green-bg">
          <span class="day">08</span>
          <span class="month">Dec, 2023</span>
        </div>
        <div class="card-content">
          <h4>Fresh & Verified Organic Produce</h4>
          <p class="meta">Fasi-Market • 25 April, 2023</p>
          <p>
            All products sold are vetted to meet organic farming standards — clean, chemical-free, and grown with care.
          </p>
          
        </div>
      </div>
    </div>
  </div>
</section>


<!-- PROJECTS SECTION -->
<section id="projects" class="projects-section font-poppins">
  <div class="projects-container">
    <h2 class="projects-title">Our Projects</h2>
    <p class="projects-intro">
      At Fasi-Market, we actively invest in community-driven agricultural innovation and education:
    </p>

    <!-- Horizontally scrollable project cards -->
    <div class="scroll-wrapper">
      <div class="project-cards horizontal-scroll">
        <!-- Project 1 -->
        <div class="project-card">
          <img :src="project1" alt="Organic Farm Training" />
          <div class="card-body">
            <h3>Organic Farm Training</h3>
            <p>Empowering farmers with modern organic techniques to grow healthy, sustainable crops.</p>
          </div>
        </div>

        <!-- Project 2 -->
        <div class="project-card">
          <img :src="project2" alt="Women in Agriculture" />
          <div class="card-body">
            <h3>Women in Agriculture</h3>
            <p>Mentoring and equipping female farmers with tools, resources, and direct market access.</p>
          </div>
        </div>

        <!-- Project 3 -->
        <div class="project-card">
          <img :src="project3" alt="School Garden Projects" />
          <div class="card-body">
            <h3>School Garden Projects</h3>
            <p>Teaching students the value of sustainability and nutrition through hands-on gardening.</p>
          </div>
        </div>

        <!-- Project 4 -->
        <div class="project-card">
          <img :src="project4" alt="Tech for Farming" />
          <div class="card-body">
            <h3>Tech for Farming</h3>
            <p>Providing mobile tools and apps to help farmers manage pricing, inventory, and logistics.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- Shop Section -->
 <section id="shop" class="section ask-section">
    <div class="shop-container">
      <h1 class="shop-title">Shop Fresh Products</h1>
      <p class="shop-intro">Buy direct from farmers — fresh, organic, and fairly priced.</p>

      <div class="shop-grid">
        <div class="product-card">
          <h3>🥦 Vegetables</h3>
          <p>Pesticide-free greens and root crops.</p>
        </div>
        <div class="product-card">
          <h3>🍎 Fruits</h3>
          <p>Seasonal fruits from trusted farms.</p>
        </div>
        <div class="product-card">
          <h3>🧀 Dairy</h3>
          <p>Fresh milk, yogurt, and cheese.</p>
        </div>
        <div class="product-card">
          <h3>🌿 Fertilizers</h3>
          <p>Organic and natural soil boosters.</p>
        </div>
      </div>
    </div>
    
  </section>

    <!-- Eco Message Banner (Minimal) -->
<section class="eco-banner" :style="{ backgroundImage: `url(${ecoBanner})` }">
  <div class="eco-box">
    <h2 class="eco-title">Eco-Friendly from the Ground Up 🌱</h2>
    <p class="eco-sub">Sustainable practices for a better tomorrow.</p>
  </div>
</section>

<!-- Compact Contact Section -->
<section id="contact" class="section contact-section">
  <h2 class="section-title">Send Us a Message</h2>
  <form class="contact-form">
    <div class="form-group">
      <input type="text" placeholder="Your name" />
      <input type="email" placeholder="Email address" />
    </div>
    <textarea placeholder="Your message"></textarea>
    <button type="submit">Send</button>
  </form>
</section>

  </div>
    </div>
</template>

<script setup>
import agriculture from '@/assets/agricultural products.jpg';
import organic from '@/assets/organic.jpg';
import dairy from '@/assets/dairy-prod.png';
import vegetable from '@/assets/vegetable2.jpg';
import fruitBasket from '@/assets/fruits.png';
import fertilizers from '@/assets/fertilizers.jpg';

const offers = [
  { image: agriculture, title: 'Agriculture Products', description: 'Fresh and quality products from trusted farmers.' },
  { image: organic, title: 'Organic Products', description: 'Certified organic food grown sustainably.' },
  { image: dairy, title: 'Dairy Products', description: 'Milk, cheese, and other fresh dairy goods.' },
  { image: vegetable, title: 'Fresh Vegetables', description: 'Locally grown and pesticide-free vegetables.' },
  { image: fruitBasket, title: 'Seasonal Fruits', description: 'Delicious fruits harvested at peak ripeness.' },
  { image: fertilizers, title: 'Natural Fertilizers', description: 'Eco-friendly compost and soil boosters for farmers.' },
];

import image from '@/assets/image.jpg';
import image2 from '@/assets/image2.jpg'
import icon1 from '@/assets/icon1.png';
import icon2 from '@/assets/icon2.png';
import service1 from '@/assets/service1.jpg';
import service2 from '@/assets/service11.jpg';
import service3 from '@/assets/service22.jpg';
import project1 from '@/assets/project-organic.jpg';
import project2 from '@/assets/project-women.jpg';
import project3 from '@/assets/project-school.jpg';
import project4 from '@/assets/project-tech.jpg';

import ecoBanner from '@/assets/eco-banner.jpg';
const scrollToSection = (id) => {
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<style scoped>
/* === Base Reset === */
html, body {
  margin: 0 !important;
  padding: 0 !important;
  box-sizing: border-box;
}

#app {
  margin: 0;
}

body {
  background-color: rgba(247, 249, 247, 0.48);
  font-family: 'Poppins', sans-serif;
  color: #334155;
}

.homepage {
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
  margin: 0;
  padding: 0;
}

.main-content {
  background-color: white;
  position: relative;
  width: 100%;
  z-index: 1;
}

/* === Navbar === */
.navbar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background-color: transparent;
  color: white;
}

.navbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
  min-height: 80px;
}

/* Logo */
.logo-wrapper {
  position: relative;
    max-height: 80px;
    max-weight:60px;
  display: flex;
  align-items: center;
  font-family: 'Dancing Script', cursive;
}

.background-blur {
  position: absolute;
  inset: 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: 0;
  border-radius: 10px;
}

.logo {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo img {
  max-height: 60px;
  display: block;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
  padding: 0;
  margin: 0;
}

.logo-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  font-family: 'Dancing Script', cursive;
}

.logo-subtitle {
  font-size: 0.75rem;
  color: rgb(255, 252, 252);
  margin: 0;
  font-family: 'Dancing Script', cursive;
}

/* Nav Links */
.nav-links {
  display: flex;
  gap: 3rem;
  margin: 0;
  font-weight: 600;
  font-size: 17px;
  font-family: 'Dancing Script', cursive;
}

.nav-links a {
  color: white;
  text-decoration: none;
  transition: color 0.3s;
  white-space: nowrap;
}

.nav-links a:hover {
  color: rgb(7, 194, 7);
}

/* Contact Info */
.top-contact {
  display: flex;
  gap: 1.4rem;
  align-items: center;
  margin: 0;
  font-family: 'Dancing Script', cursive;
}

.contact-block {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.contact-block img {
  width: 18px;
  height: 18px;
}

.small-title {
  font-size: 0.7rem;
  color: #cbd5e1;
  margin: 0;
}

.bold-info {
  font-weight: 600;
  font-size: 0.85rem;
  margin: 0;
  color: white;
}

/* === Hero Section === */
.hero-section {
  position: relative;
  width: 100vw;
  height: 120vh;
  overflow: hidden;
}

.hero-section::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-image: url('@/assets/background-image.jpg');
  background-size: cover;
  background-position: center;
  filter: contrast(135%) brightness(60%) saturate(95%);
  z-index: -1;
}

.hero-overlay {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 12rem 2rem 2rem;
  text-align: center;
  color: white;
}

.hero-slogan {
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1.2;
  letter-spacing: 1px;
  text-transform: uppercase;
  background: white;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-family: 'Dancing Script', cursive;
}

/* Equal-size action buttons in hero section */
.hero-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  justify-content: center;
  flex-wrap: wrap;
}

.hero-buttons a,
.hero-buttons button {
  /* Same size for all buttons */
  width: 180px; /* Fixed width for consistency */
  padding: 0.85rem 0.5rem; /* Equal vertical padding */
  font-size: 1rem;
  font-weight: 700;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: background-color 0.3s ease;
  background-color: rgba(65, 231, 14, 1);
  font-family: 'Dancing Script', cursive;
  box-sizing: border-box;
}

.hero-buttons a:hover,
.hero-buttons button:hover {
  background-color: rgba(242, 242, 12, 1);
}

.wave-bottom {
  position: absolute;
  bottom: -1px;
  width: 100%;
  height: 120px;
  overflow: hidden;
  z-index: 2;
}

.wave-bottom svg {
  display: block;
  width: 100%;
  height: 100%;
}

/* === Shared Section Styles === */
.section {
  padding: 4rem 1rem;
  max-width: 1280px;
  margin: auto;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin: -1.5rem auto 2rem;
  position: relative;
  font-family: 'Dancing Script', cursive;
}

/* === What We Offer === */
.offer-section {
  padding: 80px 20px;
  background-color: #f8f9fa;
  text-align: center;
}

.offer-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2px;
  max-width: 1200px;
  margin: 0 auto;
}

.offer-card {
  height: 360px;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: flex-end;
  cursor: pointer;
}

.offer-blur-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  filter: contrast(115%) brightness(70%) saturate(95%);
  transform: scale(1.05);
  z-index: 0;
}

.overlay {
  position: relative;
  z-index: 1;
  width: 100%;
  padding: 1rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
}

.offer-title {
  color: #fff;
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  text-align: left;
  font-family: 'Dancing Script', cursive;
}

/* === About Section === */
.about-section {
  padding: 40px 40px;
  background-color: #f9fafb;
}

.about-wrapper {
  display: flex;
  gap: 2.9rem;
  align-items: center;
  justify-content: center;
  max-width: 1200px;
  margin: auto;
}

.about-image img {
  width: 100%;
  max-width: 500px;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  filter: contrast(135%) brightness(80%) saturate(95%);
}

.second-image {
  margin-top: 2rem;
  width: 100%;
  filter: contrast(115%) brightness(70%) saturate(95%);
}

.about-content {
  flex: 100px 100px 500px;
  padding: 10px;
  font-family: 'Dancing Script', cursive;
}

.about-heading {
  font-size: 2.25rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1a202c;
  font-family: 'Dancing Script', cursive;
}

.about-description {
  font-size: 1rem;
  color: #4a5568;
  line-height: 1.8;
  margin-bottom: 2rem;
  font-family: 'Dancing Script', cursive;
}

.about-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  font-family: 'Dancing Script', cursive;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #38a169;
}

.stat-label {
  font-size: 0.9rem;
  color: #718096;
}

.about-features {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  font-family: 'Dancing Script', cursive;
}

.feature-box {
  display: flex;
  align-items: flex-start;
  background-color: #ffffff;
  border-radius: 15px;
  padding: 1rem;
  gap: 1rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.feature-box img {
  width: 40px;
  height: 40px;
}

.feature-box h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.feature-box p {
  font-size: 0.9rem;
  color: #4a5568;
}

.yellow-box { border-left: 6px solid #f6ad55; }
.green-box { border-left: 6px solid #48bb78; }

/* === Services === */
.services-section {
  background-color: #fff;
  padding: 4rem 2rem;
}

.services-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 2.5rem;
  text-align: center;
  color: #1a1a1a;
  font-family: 'Dancing Script', cursive;
}

.service-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  justify-content: center;
}

.service-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  max-width: 300px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  position: relative;
  transition: transform 0.3s ease;
}

.service-card:hover { transform: translateY(-5px); }

.service-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  filter: contrast(135%) brightness(40%) saturate(95%);
}

.large-card {
  flex: 1;
  width: 100%;
  display: flex;
  flex-direction: column;
}

.large-card .card-content { padding: 2.5rem; }
.large-card h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1f2937;
}

.service-card .card-content { padding: 1rem 1.2rem 1.5rem; }
.service-card h4 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0.8rem 0;
  color: #1f2937;
}

.meta { font-size: 0.85rem; color: #6b7280; margin-bottom: 1rem; }

.card-date {
  position: absolute;
  top: 12px;
  left: 12px;
  background-color: #facc15;
  padding: 0.5rem 0.8rem;
  border-radius: 6px;
  text-align: center;
  color: #000;
  font-weight: 700;
}

.card-date.green-bg { background-color: #bbf7d0; }
.card-date .day { font-size: 1.25rem; line-height: 1.2; }
.card-date .month { font-size: 0.75rem; }

/* === Projects === */
.projects-section {
  background-color: #f0fdf4;
  padding: 4rem 1rem;
  text-align: center;
}

.projects-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
}

.projects-intro {
  font-size: 1rem;
  color: #4a5568;
  max-width: 700px;
  margin: 0 auto 3rem;
}

.scroll-wrapper {
  overflow-x: auto;
  padding-bottom: 1rem;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.scroll-wrapper::-webkit-scrollbar {
  height: 8px;
}
.scroll-wrapper::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.scroll-wrapper::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.project-cards {
  display: flex;
  flex-wrap: nowrap;
  gap: 1.2rem;
  padding: 0 0.5rem;
}

.project-card {
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
  flex: 0 0 auto;
  width: 280px;
  text-align: left;
}

.project-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  filter: contrast(135%) brightness(40%) saturate(95%);
}

.card-body { padding: 1.25rem; }
.card-body h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.5rem;
}
.card-body p { font-size: 0.95rem; color: #4a5568; }

/* === Shop === */
.shop-section {
  background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
  padding: 3rem 1.5rem;
  color: #1f2937;
}

.shop-container {
  max-width: 960px;
  margin: 0 auto;
}

.shop-title {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1.5rem;
  text-align: center;
}

.shop-intro {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 1rem;
  color: #374151;
}

.shop-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(2, 1fr);
}

@media (max-width: 639px) {
  .shop-grid {
    grid-template-columns: 1fr;
  }
}

.product-card {
  background-color: white;
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(22, 101, 52, 0.1);
  text-align: center;
  transition: box-shadow 0.3s ease;
}

.product-card:hover {
  box-shadow: 0 8px 20px rgba(22, 101, 52, 0.15);
}

.product-card h3 {
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0.5rem;
  font-size: 1.125rem;
}

.product-card p {
  font-size: 0.9rem;
  color: #4b5563;
  margin: 0;
}

/* === Eco Banner === */
.eco-banner {
  position: relative;
  height: 40vh;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: white;
  padding: 2rem 1rem;
  z-index: 1;
  font-family: 'Dancing Script', cursive;
}

.eco-banner::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 0;
}

.eco-box {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: auto;
}

.eco-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  font-family: 'Dancing Script', cursive;
}

.eco-sub {
  font-size: 1.125rem;
  font-weight: 400;
  font-family: 'Dancing Script', cursive;
}

/* === Contact === */
.contact-section {
  padding: 3rem 1rem;
  max-width: 640px;
  margin: auto;
  text-align: center;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  font-family: 'Dancing Script', cursive;
}

.form-group {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-group input {
  flex: 1 1 240px;
  padding: 0.65rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.875rem;
  width: 100%;
}

.contact-form textarea {
  padding: 0.65rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.875rem;
  width: 100%;
  min-height: 100px;
  resize: vertical;
}

.contact-form button {
  align-self: center;
  background-color: #15803d;
  color: white;
  padding: 0.6rem 1.5rem;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.contact-form button:hover {
  background-color: #166534;
}
.scroll-wrapper {
  overflow-x: auto;
  padding-bottom: 1rem;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  display: flex;
  justify-content: center; /* ← ADD THIS */
}

.project-cards {
  display: flex;
  flex-wrap: nowrap;
  gap: 1.2rem;
  min-width: fit-content; /* ← ADD THIS */
  padding: 0 0.5rem;
}

/* ========================================
   MOBILE: COMPACT, UNIFORM NAVBAR WITH HORIZONTAL SCROLL
   ======================================== */
@media (max-width: 768px) {
  .navbar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 0;
    z-index: 50;
    /* Allow overflow for scrolling */
    overflow: visible;
  }

  /* Make navbar container horizontally scrollable */
  .navbar-container {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.6rem 1rem;
    min-height: 60px;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(4px);
    border-radius: 0 0 12px 12px;
    /* Enable horizontal scroll */
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    /* Prevent scrollbar from taking space */
    scrollbar-width: none; /* Firefox */
  }

  /* Hide scrollbar visually */
  .navbar-container::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Edge */
  }

  /* Allow children to shrink but not wrap */
  .logo-wrapper,
  .nav-links,
  .top-contact {
    flex-shrink: 0;
    white-space: nowrap;
  }

  /* --- Logo: Reduced size, no blur, pushed left --- */
  .logo-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .background-blur {
    display: none;
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
  }

  .logo img {
    max-height: 32px;
    width: auto;
  }

  .logo-title {
    font-size: 0.9rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .logo-subtitle {
    font-size: 0.55rem;
    white-space: nowrap;
  }

  /* --- Nav Links --- */
  .nav-links {
    display: flex;
    gap: 0.7rem;
    font-size: 0.85rem;
    margin: 0;
    white-space: nowrap;
  }

  .nav-links a {
    padding: 0.3rem 0.4rem;
    font-size: 0.85rem;
    color: white;
    border-radius: 4px;
    transition: background-color 0.2s;
  }

  .nav-links a:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }

  @media (max-width: 480px) {
  .contact-section {
    padding: 1.7rem 0.8rem;
    max-width: 320px;
  }

  .contact-form {
    gap: 0.6rem;
  }

  .form-group {
    gap: 0.6rem;
  }

  .form-group input {
    flex: 1 1 200px;
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
  }

  .contact-form textarea {
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
    min-height: 70px;
  }

  .contact-form button {
    padding: 0.4rem 1rem;
    font-size: 0.95rem;
  }
}
  /* On very small screens (<480px): hide location */
  @media (max-width: 480px) {
    .top-contact > :nth-child(3) {
      display: none;
    }
    .logo img { max-height: 30px; }
    .logo-title { font-size: 0.85rem; }
    .nav-links { gap: 0.5rem; }
    .nav-links a { font-size: 0.8rem; padding: 0.25rem 0.3rem; }
    .contact-block img { width: 14px; height: 14px; }
    .small-title, .bold-info { font-size: 0.55rem; }
  }

  /* === Rest of your mobile styles (unchanged) === */
  .hero-overlay {
    padding: 9rem 1.2rem 2rem;
    justify-content: center;
  }

  .hero-slogan {
    font-size: 1.9rem;
    line-height: 1.25;
    text-align: center;
  }

  .hero-buttons {
    flex-direction: column;
    gap: 0.8rem;
    width: 100%;
    max-width: 220px;
    margin: 0 auto;
  }

  .hero-buttons a,
  .hero-buttons button {
    width: 100%;
    text-align: center;
    padding: 0.8rem;
    font-size: 1rem;
  }

  .offer-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }

  @media (max-width: 500px) {
    .offer-grid {
      grid-template-columns: 1fr;
    }
  }

  .about-wrapper {
    flex-direction: column;
    text-align: center;
  }

  .about-image {
    width: 100%;
    margin-bottom: 1.5rem;
  }

  .second-image {
    width: 90%;
    margin: 1.5rem auto 0;
  }

  .about-stat {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.5rem;
  }

  .service-cards {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }

  .service-card {
    width: 100%;
  }

 /* On small screens: reduce card width further */
@media (max-width: 480px) {
  .project-card {
    width: 190px; /* Fits better on iPhone SE */
  }
}

/* Image inside card */
.project-card img {
  width: 100%;
  height: 200px; /* Reduced from 200px for tighter fit */
  object-fit: cover;
  filter: contrast(135%) brightness(40%) saturate(95%);
}

/* Card body: responsive text */
.card-body {
  padding: 1.25rem; /* Reduced from 1.25rem for tighter fit */
}

.card-body h3 {
  font-size: 1.5rem; /* Reduced from 1.25rem */
  font-weight: 700;
  color: #0a0a0bff;
  margin-bottom: 0.5rem;
  line-height: 1.3;
  word-wrap: break-word;
}

.card-body p {
  font-size: 0.85rem; /* Reduced from 0.95rem */
  color: #09090aff;
  line-height: 1.4;
  word-wrap: break-word;
}

/* Optional: Add hover effect on larger screens */
@media (min-width: 768px) {
  .project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
  }
}

  .shop-grid {
    grid-template-columns: 1fr;
  }

  .eco-title {
    font-size: 1.7rem;
    text-align: center;
  }

  .eco-sub {
    font-size: 1rem;
    text-align: center;
  }

  .form-group {
    flex-direction: column;
    gap: 1rem;
  }

  .form-group input {
    width: 100%;
  }

  .section-title,
  .about-heading,
  .services-title,
  .projects-title,
  .shop-title {
    margin-top: 1rem !important;
    font-size: 1.9rem;
    text-align: center;
  }

  @media (max-width: 480px) {
    .hero-slogan { font-size: 1.6rem; }
    .section-title,
    .services-title,
    .projects-title,
    .shop-title { font-size: 1.7rem; }
    .about-heading { font-size: 1.75rem; }
  }

  body, html, .homepage {
    overflow-x: hidden;
  }

  img {
    max-width: 100%;
    height: auto;
  }
}

</style>