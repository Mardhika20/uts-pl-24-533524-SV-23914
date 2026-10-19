
<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  message: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'success',
  },
})

const emit = defineEmits(['close'])

const isError = computed(() => props.type === 'error')
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="toast"
      :class="isError ? 'toast-error' : 'toast-success'"
      role="status"
      aria-live="polite"
    >
      <span class="toast-icon">
        {{ isError ? '!' : '✓' }}
      </span>

      <span class="toast-message">
        {{ message }}
      </span>

      <button
        type="button"
        class="toast-close"
        aria-label="Tutup notifikasi"
        @click="emit('close')"
      >
        ×
      </button>
    </div>
  </Teleport>
</template>

<style scoped>
.toast {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 1100;
  display: flex;
  align-items: center;
  gap: 12px;
  width: min(400px, calc(100vw - 40px));
  padding: 14px 16px;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  background: var(--color-surface);
  box-shadow: var(--shadow-modal);
}

.toast-success {
  border-left: 4px solid var(--color-primary);
}

.toast-error {
  border-left: 4px solid var(--color-danger);
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--color-primary-soft);
  color: var(--color-primary);
  font-weight: 600;
}

.toast-error .toast-icon {
  background: var(--color-danger-soft);
  color: var(--color-danger);
}

.toast-message {
  flex: 1;
  font-size: 13px;
}

.toast-close {
  border: 0;
  background: transparent;
  color: var(--color-text-muted);
  font-size: 22px;
}
</style>
