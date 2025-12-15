<template>
  <div class="payment-cancel">
    <h1>❌ Payment Cancelled</h1>
    <p>Your payment was cancelled. No charges were made.</p>
    <p>Redirecting you back to your dashboard...</p>

    <button @click="goBack">Go to Dashboard Now</button>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const goBack = () => {
  // Redirect based on user role if needed, or use a generic dashboard route
  // For now, we'll go to the consumer dashboard (most common case)
  router.push('/consumer/dashboard');
};

onMounted(() => {
  setTimeout(goBack, 3000); // Redirect after 3 seconds
});
</script>

<style scoped>
.payment-cancel {
  padding: 2rem;
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.payment-cancel h1 {
  margin-bottom: 1rem;
  color: #d32f2f;
}

.payment-cancel button {
  margin-top: 1.5rem;
  padding: 0.5rem 1.5rem;
  background-color: #1976d2;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.payment-cancel button:hover {
  background-color: #1565c0;
}
</style>