
<script setup>
defineProps({
  message: {
    type: String,
    default: 'Memuat data...',
  },
})
</script>

<template>
  <div class="loading-container" role="status" aria-live="polite">
    <div class="loading-spinner" aria-hidden="true"></div>
    <p class="loading-message">{{ message }}</p>
  </div>
</template>

<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  min-height: 180px;
  padding: 32px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--color-primary-soft);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loading-message {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 13px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
