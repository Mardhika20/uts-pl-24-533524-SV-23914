
<script setup>
import { RouterView } from 'vue-router'
import ToastNotification from './components/ToastNotification.vue'
</script>

<template>
  <RouterView />

  <!-- Notifikasi global Admin dan User -->
  <ToastNotification />
</template>
