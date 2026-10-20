
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginUser, saveAuth } from '../../services/authService'
import { toastSuccess, toastError } from '../../services/toast'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function handleLogin() {
  if (loading.value) return

  errorMessage.value = ''
  successMessage.value = ''

  // Validasi email dan password
  if (!email.value.trim()) {
    errorMessage.value = 'Email wajib diisi.'
    toastError(errorMessage.value)
    return
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!emailPattern.test(email.value.trim())) {
    errorMessage.value = 'Format email tidak valid.'
    toastError(errorMessage.value)
    return
  }

  if (!password.value) {
    errorMessage.value = 'Password wajib diisi.'
    toastError(errorMessage.value)
    return
  }

  loading.value = true

  try {
    // Mengirim email dan password ke REST API Laravel
    const data = await loginUser(
      email.value.trim(),
      password.value
    )

    // Memastikan respons login memiliki data yang diperlukan
    if (!data?.token || !data?.user) {
      throw new Error('Data login dari server tidak lengkap.')
    }

    // Menentukan halaman tujuan berdasarkan role
    let destination = ''

    if (data.user.role === 'admin') {
      destination = '/admin'
    } else if (data.user.role === 'user') {
      destination = '/home'
    } else {
      errorMessage.value = 'Role pengguna tidak dikenali.'
      toastError(errorMessage.value)
      return
    }

    // Menyimpan token dan data pengguna
    saveAuth(data.token, data.user)

    // Notifikasi login berhasil
    successMessage.value = 'Login berhasil!'
    toastSuccess('Login berhasil! Selamat datang di RecipeBox.')

    // Mengarahkan pengguna sesuai role
    await router.replace(destination)

  } catch (error) {
    console.error('Login gagal:', error)

    const errors = error.response?.data?.errors

    if (errors) {
      errorMessage.value = Object.values(errors)
        .flat()
        .join(' ')
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        error.message ||
        'Login gagal. Periksa email dan password.'
    }

    // Notifikasi login gagal
    toastError(errorMessage.value)

  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-form-container">
    <div class="auth-form">
      <h1>Selamat Datang Kembali</h1>

      <p class="auth-subtitle">
        Masuk untuk melanjutkan perjalanan memasakmu.
      </p>

      <form novalidate @submit.prevent="handleLogin">

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
              placeholder="Masukkan password"
              autocomplete="current-password"
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

        <!-- Tombol login -->
        <button
          type="submit"
          class="submit-button"
          :disabled="loading"
        >
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <p class="auth-switch">
        Belum punya akun?

        <RouterLink to="/register">
          Daftar Sekarang
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
