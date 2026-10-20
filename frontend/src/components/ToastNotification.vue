
<script setup>
import { toastState, closeToast } from '../services/toast'
</script>

<template>
  <Teleport to="body">
    <Transition name="toast">
      <div
        v-if="toastState.visible"
        class="toast-notification"
        :class="`toast-${toastState.type}`"
        role="status"
        aria-live="polite"
      >
        <div class="toast-icon">
          <svg
            v-if="toastState.type === 'success'"
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="12" cy="12" r="10" />
            <path d="m8 12 3 3 5-6" />
          </svg>

          <svg
            v-else
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="12" cy="12" r="10" />
            <path d="M12 8v4M12 16h.01" />
          </svg>
        </div>

        <div class="toast-content">
          <strong>
            {{ toastState.type === 'success'
              ? 'Berhasil!'
              : 'Terjadi Kesalahan!' }}
          </strong>

          <p>{{ toastState.message }}</p>
        </div>

        <button
          type="button"
          class="toast-close"
          aria-label="Tutup notifikasi"
          @click="closeToast"
        >
          ×
        </button>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.toast-notification {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 99999;

  display: flex;
  align-items: flex-start;
  gap: 14px;

  width: calc(100% - 32px);
  max-width: 380px;
  padding: 18px 20px;

  background: #ffffff;
  border: 1px solid #e2e8e1;
  border-left: 4px solid #315e43;
  border-radius: 12px;

  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.12);
}

.toast-success {
  border-left-color: #315e43;
}

.toast-error {
  border-left-color: #c94c4c;
}

.toast-icon {
  display: flex;
  flex-shrink: 0;
  color: #315e43;
}

.toast-error .toast-icon {
  color: #c94c4c;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-content strong {
  display: block;
  margin-bottom: 4px;
  color: #26352b;
  font-size: 14px;
  font-weight: 700;
}

.toast-content p {
  margin: 0;
  color: #738077;
  font-size: 13px;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.toast-close {
  flex-shrink: 0;
  padding: 0;
  border: none;
  background: transparent;
  color: #819087;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
}

.toast-close:hover {
  color: #26352b;
}

.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(24px);
}

@media (max-width: 480px) {
  .toast-notification {
    top: 16px;
    right: 16px;
    max-width: none;
  }
}
</style>
