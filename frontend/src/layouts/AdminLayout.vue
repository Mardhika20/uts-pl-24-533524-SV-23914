
<script setup>
import { computed, ref } from 'vue'
import {
  RouterLink,
  RouterView,
  useRoute,
  useRouter,
} from 'vue-router'
import {
  getAuthUser,
  logoutUser,
  clearAuth,
} from '../services/authService'

const route = useRoute()
const router = useRouter()

const currentUser = getAuthUser()

const showLogoutModal = ref(false)
const isLoggingOut = ref(false)

const menus = [
  { label: 'Dashboard', path: '/admin' },
  { label: 'Kelola Resep', path: '/admin/recipes' },
  { label: 'Tambah Resep', path: '/admin/recipes/create' },
]

function isMenuActive(path) {
  if (path === '/admin') {
    return route.path === '/admin'
  }

  if (path === '/admin/recipes') {
    return (
      route.path === '/admin/recipes' ||
      (
        route.path.startsWith('/admin/recipes/') &&
        route.path !== '/admin/recipes/create'
      )
    )
  }

  return route.path === path
}

const adminName = computed(() => {
  return currentUser?.name || 'Admin RecipeBox'
})

const adminInitials = computed(() => {
  return adminName.value
    .trim()
    .split(/\s+/)
    .map((word) => word[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

const breadcrumbTitle = computed(() => {
  if (route.path === '/admin') return 'Dashboard'
  if (route.path === '/admin/recipes') return 'Kelola Resep'
  if (route.path === '/admin/recipes/create') return 'Tambah Resep'
  if (route.path.endsWith('/edit')) return 'Edit Resep'
  if (route.path.startsWith('/admin/recipes/')) return 'Detail Resep'

  return 'Panel Admin'
})

// Membuka konfirmasi logout
function openLogoutModal() {
  showLogoutModal.value = true
}

// Membatalkan logout
function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutModal.value = false
}

// Melakukan logout setelah dikonfirmasi
async function confirmLogout() {
  if (isLoggingOut.value) return

  isLoggingOut.value = true

  try {
    await logoutUser()
  } catch (error) {
    console.error('Logout gagal:', error)
  } finally {
    clearAuth()
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')

    showLogoutModal.value = false
    isLoggingOut.value = false

    await router.replace('/login')
  }
}
</script>


<template>
  <div class="admin-layout">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
      <RouterLink to="/admin" class="admin-brand">
        <img
          src="/recipebox-logo.svg"
          alt="Logo RecipeBox"
          class="admin-logo-icon"
        />

        <span>RecipeBox</span>
      </RouterLink>

      <p class="sidebar-caption">MENU UTAMA</p>

      <nav class="admin-navigation" aria-label="Navigasi Admin">
        <RouterLink
          v-for="menu in menus"
          :key="menu.path"
          :to="menu.path"
          class="admin-menu"
          :class="{
            'admin-menu--active': isMenuActive(menu.path)
          }"
        >
          <svg
            v-if="menu.path === '/admin'"
            xmlns="http://www.w3.org/2000/svg"
            width="19"
            height="19"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>

          <svg
            v-else-if="menu.path === '/admin/recipes'"
            xmlns="http://www.w3.org/2000/svg"
            width="19"
            height="19"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <rect x="6" y="3" width="14" height="18" rx="2" />
            <path d="M10 8h6M10 12h6M10 16h4" />
            <path d="M3 7h3M3 12h3M3 17h3" />
          </svg>

          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            width="19"
            height="19"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v8M8 12h8" />
          </svg>

          <span>{{ menu.label }}</span>
        </RouterLink>
      </nav>

      <!-- LOGOUT -->
      <div class="sidebar-bottom">
        <button
          type="button"
          class="admin-logout"
          @click="openLogoutModal"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="19"
            height="19"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>

          <span>Keluar</span>
        </button>

        <p class="sidebar-footer">
          RecipeBox / Panel Admin
          <span>Kelola resep dengan lebih rapi.</span>
        </p>
      </div>

    </aside>

    <!-- KONTEN UTAMA -->
    <div class="admin-main">
      <header class="admin-header">
        <div class="header-breadcrumb">
          <span>RecipeBox</span>
          <span class="breadcrumb-divider">/</span>
          <strong>
            {{
              route.path === '/admin'
                ? 'Dashboard'
                : route.path === '/admin/recipes/create'
                  ? 'Tambah Resep'
                  : route.path.includes('/edit')
                    ? 'Edit Resep'
                    : route.path === '/admin/recipes'
                      ? 'Kelola Resep'
                      : 'Detail Resep'
            }}
          </strong>
        </div>

        <div class="admin-profile">
          <div class="admin-profile-info">
            <strong>{{ currentUser?.name || 'Admin RecipeBox' }}</strong>
            <span>Administrator</span>
          </div>

          <div class="admin-avatar">
            {{
              (currentUser?.name || 'Admin')
                .trim()
                .split(/\s+/)
                .map((word) => word[0])
                .slice(0, 2)
                .join('')
                .toUpperCase()
            }}
          </div>
        </div>
      </header>

      <main class="admin-content">
        <RouterView />
      </main>
    </div>
  </div>

      <!-- MODAL KONFIRMASI LOGOUT -->
      <div
        v-if="showLogoutModal"
        class="logout-overlay"
        @click.self="cancelLogout"
      >
        <div
          class="logout-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="logout-title"
          aria-describedby="logout-description"
        >
          <button
            type="button"
            class="logout-close"
            aria-label="Tutup"
            :disabled="isLoggingOut"
            @click="cancelLogout"
          >
            ×
          </button>

          <div class="logout-icon">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="25"
              height="25"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
          </div>

          <h2 id="logout-title">Keluar dari akun?</h2>

          <p id="logout-description">
            Anda akan keluar dari Panel Admin dan diarahkan ke
            halaman Login. Semua data resep yang tersimpan tetap ada.
          </p>

          <div class="logout-actions">
            <button
              type="button"
              class="logout-cancel"
              :disabled="isLoggingOut"
              @click="cancelLogout"
            >
              Batal
            </button>

            <button
              type="button"
              class="logout-confirm"
              :disabled="isLoggingOut"
              @click="confirmLogout"
            >
              {{ isLoggingOut ? 'Memproses...' : 'Keluar' }}
            </button>
          </div>
        </div>
      </div>


</template>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f8f7f3;
}

/* SIDEBAR */
.admin-sidebar {
  position: sticky;
  top: 0;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  width: 260px;
  height: 100vh;
  box-sizing: border-box;
  padding: 32px 24px 24px;
  background: #203e2d;
  color: #fff;
}

.admin-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 62px;
  color: #fff;
  text-decoration: none;
  font-family: var(--font-heading, Georgia, serif);
  font-size: 26px;
  font-weight: 600;
  letter-spacing: -0.8px;
}

.admin-logo-icon {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
}

.sidebar-caption {
  margin: 0 0 18px;
  color: #b6c7b9;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.8px;
}

.admin-navigation {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.admin-menu {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 13px 16px;
  border-radius: 10px;
  color: #c5d2c9;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.15s ease;
}

.admin-menu svg {
  flex-shrink: 0;
}

.admin-menu:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.admin-menu--active {
  background: #365b46;
  color: #fff;
  font-weight: 600;
}

/* LOGOUT */
.sidebar-bottom {
  margin-top: auto;
}

.admin-logout {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  padding: 18px 16px;
  border: 0;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  background: transparent;
  color: #c5d2c9;
  font: inherit;
  font-size: 14px;
  text-align: left;
  cursor: pointer;
}

.admin-logout:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 8px;
}

.admin-logout:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.sidebar-footer {
  margin: 20px 0 0;
  color: #b6c7b9;
  font-size: 11px;
  line-height: 1.7;
}

.sidebar-footer span {
  display: block;
}

/* KONTEN UTAMA */
.admin-main {
  flex: 1;
  min-width: 0;
}

.admin-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  min-height: 80px;
  box-sizing: border-box;
  padding: 0 40px;
  background: #fff;
  border-bottom: 1px solid #e1e5dd;
}

.header-breadcrumb {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #7b867e;
  font-size: 13px;
}

.header-breadcrumb strong {
  color: #27332b;
  font-weight: 500;
}

.breadcrumb-divider {
  color: #aab3aa;
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.admin-profile-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.admin-profile-info strong {
  color: #27332b;
  font-size: 12px;
  font-weight: 600;
}

.admin-profile-info span {
  color: #89958b;
  font-size: 11px;
}

.admin-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  border-radius: 50%;
  background: #eaf0e8;
  color: #315e43;
  font-size: 12px;
  font-weight: 700;
}

.admin-content {
  min-width: 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .admin-layout {
    display: block;
  }

  .admin-sidebar {
    position: static;
    width: 100%;
    height: auto;
    padding: 20px;
  }

  .admin-brand {
    margin-bottom: 24px;
  }

  .admin-navigation {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .sidebar-bottom {
    margin-top: 20px;
  }

  .sidebar-footer {
    display: none;
  }

  .admin-header {
    min-height: 64px;
    padding: 0 20px;
  }

  .admin-profile-info {
    display: none;
  }
}

/* MODAL KONFIRMASI LOGOUT */
.logout-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(20, 37, 27, 0.42);
  box-sizing: border-box;
}

.logout-modal {
  position: relative;
  width: 100%;
  max-width: 506px;
  box-sizing: border-box;
  padding: 28px;
  border-radius: 20px;
  background: #ffffff;
  color: #27332b;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
}

.logout-close {
  position: absolute;
  top: 20px;
  right: 22px;
  border: 0;
  background: transparent;
  color: #7d8a80;
  font-size: 26px;
  cursor: pointer;
}

.logout-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  margin-bottom: 22px;
  border-radius: 50%;
  background: #eaf0e8;
  color: #315e43;
}

.logout-modal h2 {
  margin: 0 0 12px;
  color: #27332b;
  font-size: 23px;
  font-weight: 700;
}

.logout-modal p {
  margin: 0;
  color: #7d8a80;
  font-size: 14px;
  line-height: 1.8;
}

.logout-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.logout-cancel,
.logout-confirm {
  min-height: 44px;
  padding: 0 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.logout-cancel {
  border: 1px solid #e1e5dd;
  background: #fff;
  color: #27332b;
}

.logout-confirm {
  border: 1px solid #315e43;
  background: #315e43;
  color: #fff;
}

.logout-confirm:hover { background: #244c34; }
.logout-cancel:disabled,
.logout-confirm:disabled,
.logout-close:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 480px) {
  .logout-modal { padding: 24px; }
  .logout-modal h2 { font-size: 20px; }
}
</style>
