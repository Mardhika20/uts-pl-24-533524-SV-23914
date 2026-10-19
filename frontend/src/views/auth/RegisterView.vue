
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { registerUser } from '../../services/authService'
import { toastSuccess, toastError } from '../../services/toast'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showConfirmation = ref(false)

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

function showValidationError(message) {
  errorMessage.value = message
  toastError(message)
}

async function handleRegister() {
  if (loading.value) return

  errorMessage.value = ''
  successMessage.value = ''

  // Validasi nama lengkap
  if (!name.value.trim()) {
    showValidationError('Nama lengkap wajib diisi.')
    return
  }

  // Validasi email
  if (!email.value.trim()) {
    showValidationError('Email wajib diisi.')
    return
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!emailPattern.test(email.value.trim())) {
    showValidationError('Format email tidak valid.')
    return
  }

  // Validasi password
  if (!password.value) {
    showValidationError('Password wajib diisi.')
    return
  }

  if (password.value.length < 8) {
    showValidationError('Password minimal 8 karakter.')
    return
  }

  // Validasi konfirmasi password
  if (!passwordConfirmation.value) {
    showValidationError('Konfirmasi password wajib diisi.')
    return
  }

  if (password.value !== passwordConfirmation.value) {
    showValidationError('Konfirmasi password tidak sesuai.')
    return
  }

  loading.value = true

  try {
    // Mengirim data registrasi ke REST API Laravel
    const data = await registerUser({
      name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
      passwordConfirmation: passwordConfirmation.value,
    })

    // Notifikasi registrasi berhasil
    successMessage.value =
      data?.message || 'Registrasi berhasil. Silakan login.'

    toastSuccess(successMessage.value)

    // Mengarahkan pengguna ke halaman Login
    await router.replace('/login')

  } catch (error) {
    console.error('Registrasi gagal:', error)

    const errors = error.response?.data?.errors

    if (errors) {
      errorMessage.value = Object.values(errors)
        .flat()
        .join(' ')
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        error.message ||
        'Registrasi gagal. Silakan coba lagi.'
    }

    // Notifikasi registrasi gagal
    toastError(errorMessage.value)

  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-form-container">
    <div class="auth-form">
      <h1>Buat Akun Baru</h1>

      <p class="auth-subtitle">
        Daftar untuk mulai menjelajahi berbagai resep.
      </p>

      <form novalidate @submit.prevent="handleRegister">

        <!-- Pesan error -->
        <div
          v-if="errorMessage"
          class="error-message"
          role="alert"
        >
          {{ errorMessage }}
        </div>

        <!-- Pesan berhasil -->
        <div
          v-if="successMessage"
          class="success-message"
          role="status"
        >
          {{ successMessage }}
        </div>

        <!-- Nama Lengkap -->
        <div class="form-group">
          <label for="name">Nama Lengkap</label>

          <input
            id="name"
            v-model="name"
            type="text"
            placeholder="Masukkan nama lengkap"
            autocomplete="name"
            required
            :disabled="loading"
          />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="email">Email</label>

          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="Masukkan email"
            autocomplete="email"
            required
            :disabled="loading"
          />
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password">Password</label>

          <div class="password-wrapper">
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Minimal 8 karakter"
              autocomplete="new-password"
              minlength="8"
              required
              :disabled="loading"
            />

            <button
              type="button"
              class="password-toggle"
              :disabled="loading"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? 'Sembunyikan' : 'Lihat' }}
            </button>
          </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group">
          <label for="confirmation">Konfirmasi Password</label>

          <div class="password-wrapper">
            <input
              id="confirmation"
              v-model="passwordConfirmation"
              :type="showConfirmation ? 'text' : 'password'"
              placeholder="Ulangi password"
              autocomplete="new-password"
              minlength="8"
              required
              :disabled="loading"
            />

            <button
              type="button"
              class="password-toggle"
              :disabled="loading"
              @click="showConfirmation = !showConfirmation"
            >
              {{ showConfirmation ? 'Sembunyikan' : 'Lihat' }}
            </button>
          </div>
        </div>

        <!-- Tombol Daftar -->
        <button
          type="submit"
          class="submit-button"
          :disabled="loading"
        >
          {{ loading ? 'Memproses...' : 'Daftar' }}
        </button>
      </form>

      <p class="auth-switch">
        Sudah punya akun?

        <RouterLink to="/login">
          Masuk
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-form-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100%;
  padding: 40px 24px;
}

.auth-form {
  width: 100%;
  max-width: 420px;
}

h1 {
  margin-bottom: 10px;
  color: #242e27;
  font-family: 'Lora', serif;
  font-size: 32px;
  font-weight: 600;
}

.auth-subtitle {
  margin-bottom: 30px;
  color: #758078;
  font-size: 14px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #242e27;
  font-size: 13px;
  font-weight: 600;
}

input {
  width: 100%;
  padding: 13px 14px;
  border: 1px solid #e1e5dd;
  border-radius: 8px;
  background: white;
  color: #242e27;
  font: inherit;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #315e43;
}

input:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.password-wrapper {
  position: relative;
}

.password-wrapper input {
  padding-right: 110px;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: #315e43;
  font-size: 12px;
  cursor: pointer;
}

.password-toggle:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.submit-button {
  width: 100%;
  margin-top: 8px;
  padding: 14px;
  border: none;
  border-radius: 8px;
  background: #315e43;
  color: white;
  font-weight: 600;
  cursor: pointer;
}

.submit-button:hover:not(:disabled) {
  background: #254c35;
}

.submit-button:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.error-message,
.success-message {
  margin-bottom: 18px;
  padding: 12px;
  border-radius: 8px;
  font-size: 13px;
}

.error-message {
  background: #fff0ec;
  color: #b84236;
}

.success-message {
  background: #eaf0e8;
  color: #315e43;
}

.auth-switch {
  margin-top: 24px;
  text-align: center;
  color: #758078;
  font-size: 13px;
}

.auth-switch a {
  color: #315e43;
  font-weight: 600;
  text-decoration: none;
}
</style>
