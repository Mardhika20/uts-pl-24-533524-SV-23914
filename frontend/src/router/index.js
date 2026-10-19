
import { createRouter, createWebHistory } from 'vue-router'

// Layout
import AuthLayout from '../layouts/AuthLayout.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import UserLayout from '../layouts/UserLayout.vue'

// Halaman autentikasi
import LoginView from '../views/auth/LoginView.vue'
import RegisterView from '../views/auth/RegisterView.vue'

// Halaman Admin
import AdminDashboardView from '../views/admin/AdminDashboardView.vue'
import RecipeListView from '../views/admin/RecipeListView.vue'
import RecipeCreateView from '../views/admin/RecipeCreateView.vue'
import RecipeEditView from '../views/admin/RecipeEditView.vue'
import AdminRecipeDetailView from '../views/admin/RecipeDetailView.vue'

// Halaman Pengguna
import HomeView from '../views/user/HomeView.vue'
import ExploreView from '../views/user/ExploreView.vue'
import UserRecipeDetailView from '../views/user/RecipeDetailView.vue'
import FavoritesView from '../views/user/FavoritesView.vue'

// Halaman pengujian REST API
import ApiTestView from '../views/ApiTestView.vue'

const routes = [
  // Halaman awal
  {
    path: '/',
    redirect: '/login',
  },

  // ==========================
  // AUTENTIKASI
  // ==========================
  {
    path: '/',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'Login',
        component: LoginView,
      },
      {
        path: 'register',
        name: 'Register',
        component: RegisterView,
      },
    ],
  },

  // ==========================
  // HALAMAN ADMIN
  // ==========================
  {
    path: '/admin',
    component: AdminLayout,
    meta: {
      requiresAuth: true,
      role: 'admin',
    },
    children: [
      {
        path: '',
        name: 'AdminDashboard',
        component: AdminDashboardView,
      },
      {
        path: 'recipes',
        name: 'AdminRecipes',
        component: RecipeListView,
      },
      {
        path: 'recipes/create',
        name: 'AdminRecipeCreate',
        component: RecipeCreateView,
      },
      {
        path: 'recipes/:id/edit',
        name: 'AdminRecipeEdit',
        component: RecipeEditView,
      },
      {
        path: 'recipes/:id',
        name: 'AdminRecipeDetail',
        component: AdminRecipeDetailView,
      },
      {
        path: 'api-test',
        name: 'AdminApiTest',
        component: ApiTestView,
      },
    ],
  },

  // ==========================
  // HALAMAN PENGGUNA
  // ==========================
  {
    path: '/',
    component: UserLayout,
    meta: {
      requiresAuth: true,
      role: 'user',
    },
    children: [
      // a. Beranda
      {
        path: 'home',
        name: 'Home',
        component: HomeView,
      },

      // b. Jelajahi Resep, Pencarian, dan Filter
      {
        path: 'recipes',
        name: 'ExploreRecipes',
        component: ExploreView,
      },

      // c. Detail Resep
      {
        path: 'recipes/:id',
        name: 'UserRecipeDetail',
        component: UserRecipeDetailView,
      },

      // d. Favorit Saya
      {
        path: 'favorites',
        name: 'Favorites',
        component: FavoritesView,
      },

      // Pengujian REST API
      {
        path: 'api-test',
        name: 'UserApiTest',
        component: ApiTestView,
      },
    ],
  },

  // ==========================
  // HALAMAN TIDAK DITEMUKAN
  // ==========================
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ==========================
// NAVIGATION GUARD
// ==========================
router.beforeEach((to) => {
  const token = localStorage.getItem('auth_token')
  const savedUser = localStorage.getItem('auth_user')

  let user = null

  try {
    user = savedUser ? JSON.parse(savedUser) : null
  } catch {
    user = null
  }

  const role = user?.role

  // Mengambil meta dari seluruh route yang cocok,
  // termasuk parent layout.
  const requiresAuth = to.matched.some(
    (record) => record.meta.requiresAuth
  )

  const requiredRole = [...to.matched]
    .reverse()
    .find((record) => record.meta.role)
    ?.meta.role

  // Jika belum login, arahkan ke login
  if (requiresAuth && (!token || !user)) {
    return {
      name: 'Login',
      query: { redirect: to.fullPath },
    }
  }

  // Jika role tidak sesuai
  if (requiredRole && role !== requiredRole) {
    if (token && role === 'admin') {
      return { name: 'AdminDashboard' }
    }

    if (token && role === 'user') {
      return { name: 'Home' }
    }

    return { name: 'Login' }
  }

  // Jika sudah login, jangan kembali ke login/register
  if (
    (to.name === 'Login' || to.name === 'Register') &&
    token &&
    user
  ) {
    if (role === 'admin') {
      return { name: 'AdminDashboard' }
    }

    if (role === 'user') {
      return { name: 'Home' }
    }
  }

  return true
})

export default router
