
<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { logoutUser } from '../services/authService'

const router = useRouter()

const menus = [
  { label: 'Beranda', path: '/home' },
  { label: 'Jelajahi Resep', path: '/recipes' },
  { label: 'Favorit Saya', path: '/favorites' },
]

// State modal logout
const showLogoutModal = ref(false)
const isLoggingOut = ref(false)

// Membuka modal
function openLogoutModal() {
  showLogoutModal.value = true
}

// Menutup modal
function closeLogoutModal() {
  if (isLoggingOut.value) return

  showLogoutModal.value = false
}

// Konfirmasi logout
async function handleLogout() {
  if (isLoggingOut.value) return

  isLoggingOut.value = true

  try {
    // Logout melalui Laravel Sanctum
    await logoutUser()
  } catch (error) {
    console.error('Logout API gagal:', error)
  } finally {
    // Hapus data autentikasi dari browser
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')

    showLogoutModal.value = false
    isLoggingOut.value = false

    // Kembali ke halaman login
    await router.replace('/login')
  }
}
</script>

<template>
  <div class="user-layout">
    <!-- NAVBAR -->
    <header class="navbar">
      <div class="container navbar-inner">
        <!-- Logo -->
        <RouterLink to="/home" class="user-logo">
          <img
            src="/recipebox-logo.svg"
            alt=""
            class="user-logo-icon"
          />
          <span>RecipeBox</span>
        </RouterLink>

        <!-- Menu Navigasi -->
        <nav
          class="user-navigation"
          aria-label="Navigasi Pengguna"
        >
          <RouterLink
            v-for="menu in menus"
            :key="menu.path"
            :to="menu.path"
            class="user-menu"
            :class="{
              'user-menu--active':
                menu.path === '/home'
                  ? $route.path === '/home'
                  : $route.path === menu.path ||
                    $route.path.startsWith(menu.path + '/')
            }"
          >
            {{ menu.label }}
          </RouterLink>

          <!-- Tombol Logout -->
          <button
            type="button"
            class="logout-button"
            @click="openLogoutModal"
          >
            <svg
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            <span>Keluar</span>
          </button>
        </nav>
      </div>
    </header>

    <!-- KONTEN HALAMAN -->
    <main class="user-main">
      <RouterView />
    </main>

    <!-- FOOTER -->
    <footer class="user-footer">
      <div class="container footer-inner">
        <span class="footer-brand">RecipeBox</span>
        <span>Temukan inspirasi resep untuk setiap hari.</span>
      </div>
    </footer>

    <!-- MODAL KONFIRMASI LOGOUT -->
    <div
      v-if="showLogoutModal"
      class="logout-overlay"
      @click.self="closeLogoutModal"
    >
      <div
        class="logout-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logout-title"
        aria-describedby="logout-description"
      >
        <!-- Tombol Tutup -->
        <button
          type="button"
          class="logout-close"
          aria-label="Tutup modal"
          :disabled="isLoggingOut"
          @click="closeLogoutModal"
        >
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            aria-hidden="true"
          >
            <path d="M18 6 6 18M6 6l12 12" />
          </svg>
        </button>

        <!-- Ikon Logout -->
        <div class="logout-modal-icon">
          <svg
            width="25"
            height="25"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
        </div>

        <!-- Judul -->
        <h2 id="logout-title">
          Keluar dari akun?
        </h2>

        <!-- Deskripsi -->
        <p
          id="logout-description"
          class="logout-description"
        >
          Anda akan diarahkan ke halaman Login setelah keluar.
          Resep dan favorit yang tersimpan tetap ada di akun Anda.
        </p>

        <!-- Tombol Aksi -->
        <div class="logout-actions">
          <button
            type="button"
            class="logout-cancel"
            :disabled="isLoggingOut"
            @click="closeLogoutModal"
          >
            Batal
          </button>

          <button
            type="button"
            class="logout-confirm"
            :disabled="isLoggingOut"
            @click="handleLogout"
          >
            <svg
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>

            <span>
              {{ isLoggingOut ? 'Memproses...' : 'Keluar' }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ==========================
   LAYOUT UTAMA
========================== */

.user-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ==========================
   NAVBAR
========================== */

.navbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  min-height: var(--navbar-height);
  max-width: 1312px;
  width: calc(100% - 64px);
  margin-inline: auto;
}

.user-navigation {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 32px;
}

.user-menu {
  padding: 10px 0;
  color: var(--color-text-muted);
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
}

.user-menu:hover,
.user-menu--active {
  color: var(--color-primary);
}

.user-menu--active {
  font-weight: 600;
}

.user-main {
  flex: 1;
  width: 100%;
}

/* ==========================
   LOGO
========================== */

.user-logo {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  color: var(--color-primary-dark);
  font-family: var(--font-heading);
  font-size: 24px;
  font-weight: 600;
  letter-spacing: -0.8px;
  text-decoration: none;
}

.user-logo-icon {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}

/* ==========================
   TOMBOL LOGOUT NAVBAR
========================== */

.logout-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 18px;
  border: 1px solid var(--color-primary);
  border-radius: 8px;
  background: transparent;
  color: var(--color-primary);
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-button:hover {
  background: var(--color-primary);
  color: #ffffff;
}

/* ==========================
   FOOTER
========================== */

.user-footer {
  padding: 32px 0;
  background: var(--color-primary-dark);
  color: #e1e9e1;
}

.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  font-size: 13px;
}

.footer-brand {
  color: #ffffff;
  font-family: var(--font-heading);
  font-size: 22px;
  font-weight: 600;
}

/* ==========================
   MODAL OVERLAY
========================== */

.logout-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(26, 43, 32, 0.43);
}

/* ==========================
   MODAL LOGOUT
========================== */

.logout-modal {
  position: relative;
  width: 100%;
  max-width: 520px;
  padding: 28px;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.12);
}

/* Tombol X */
.logout-close {
  position: absolute;
  top: 24px;
  right: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  padding: 0;
  border: none;
  background: transparent;
  color: #819087;
  cursor: pointer;
}

.logout-close:hover {
  color: #26352b;
}

/* Ikon logout */
.logout-modal-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  margin-bottom: 22px;
  border-radius: 50%;
  background: #eaf1e9;
  color: #315e43;
}

/* Judul */
.logout-modal h2 {
  margin: 0 0 14px;
  color: #26352b;
  font-family: var(--font-body);
  font-size: 24px;
  font-weight: 700;
  line-height: 1.4;
}

/* Deskripsi */
.logout-description {
  margin: 0;
  color: #7b8980;
  font-family: var(--font-body);
  font-size: 14px;
  line-height: 1.8;
}

/* ==========================
   TOMBOL MODAL
========================== */

.logout-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.logout-cancel,
.logout-confirm {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 46px;
  padding: 0 20px;
  border-radius: 10px;
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-cancel {
  border: 1px solid #dce2da;
  background: #ffffff;
  color: #26352b;
}

.logout-cancel:hover {
  background: #f5f7f4;
}

.logout-confirm {
  border: 1px solid #315e43;
  background: #315e43;
  color: #ffffff;
}

.logout-confirm:hover {
  background: #254a34;
}

.logout-cancel:disabled,
.logout-confirm:disabled,
.logout-close:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ==========================
   RESPONSIVE
========================== */

@media (max-width: 768px) {
  .navbar-inner {
    flex-direction: column;
    align-items: flex-start;
    padding-block: 16px;
  }

  .user-navigation {
    gap: 20px;
  }

  .footer-inner {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .navbar-inner {
    width: calc(100% - 32px);
  }

  .user-navigation {
    gap: 12px;
  }

  .user-menu {
    font-size: 12px;
  }

  .logout-button {
    padding: 8px 12px;
    font-size: 12px;
  }

  .logout-modal {
    padding: 24px;
    border-radius: 16px;
  }

  .logout-modal h2 {
    font-size: 21px;
  }

  .logout-actions {
    flex-wrap: wrap;
  }
}
</style>
