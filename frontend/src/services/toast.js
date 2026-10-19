
import { reactive } from 'vue'

export const toastState = reactive({
  visible: false,
  type: 'success',
  message: '',
})

let toastTimer = null

export function showToast(message, type = 'success') {
  if (toastTimer) {
    clearTimeout(toastTimer)
  }

  toastState.message = message
  toastState.type = type
  toastState.visible = true

  toastTimer = setTimeout(() => {
    toastState.visible = false
    toastTimer = null
  }, 3500)
}

export function toastSuccess(message) {
  showToast(message, 'success')
}

export function toastError(message) {
  showToast(message, 'error')
}

export function closeToast() {
  if (toastTimer) {
    clearTimeout(toastTimer)
    toastTimer = null
  }

  toastState.visible = false
}
