
<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  getCategories,
  getRecipes,
} from '../../services/recipeService'

// Data dari REST API Laravel
const recipes = ref([])
const categories = ref([])
const loading = ref(true)
const errorMessage = ref('')

// Tanggal saat ini
const today = new Date()

const sevenDaysAgo = new Date(today)
sevenDaysAgo.setDate(today.getDate() - 6)
sevenDaysAgo.setHours(0, 0, 0, 0)

// Format tanggal Indonesia
const dateFormat = new Intl.DateTimeFormat('id-ID', {
  day: 'numeric',
  month: 'long',
  year: 'numeric',
})

const shortDateFormat = new Intl.DateTimeFormat('id-ID', {
  day: 'numeric',
  month: 'short',
  year: 'numeric',
})

// Mengambil array dari respons Laravel
function extractArray(response) {
  if (Array.isArray(response)) {
    return response
  }

  if (Array.isArray(response?.data)) {
    return response.data
  }

  if (Array.isArray(response?.data?.data)) {
    return response.data.data
  }

  return []
}

// Mengubah data tanggal
function parseDate(value) {
  if (!value) return null

  const date = new Date(value)

  return Number.isNaN(date.getTime())
    ? null
    : date
}

function formatDate(value) {
  const date = parseDate(value)

  return date
    ? shortDateFormat.format(date)
    : '-'
}

// Mengambil nama kategori dari data API
function categoryName(recipe) {
  if (recipe.category?.name) {
    return recipe.category.name
  }

  const category = categories.value.find(
    (item) =>
      Number(item.id) === Number(recipe.category_id),
  )

  return category?.name || 'Tanpa Kategori'
}


const placeholderImage = '/images/default-recipe.svg'

function recipeImage(recipe) {
  return recipe.image_url?.trim() || placeholderImage
}

function useDefaultImage(event) {
  const image = event.target

  // Mencegah pemuatan gambar gagal secara berulang
  image.onerror = null

  if (!image.src.endsWith(placeholderImage)) {
    image.src = placeholderImage
  }
}


// Mengambil maksimal 4 resep terbaru
const recentRecipes = computed(() => {
  return [...recipes.value]
    .sort((a, b) => {
      const first =
        parseDate(a.created_at)?.getTime() || 0

      const second =
        parseDate(b.created_at)?.getTime() || 0

      return second - first
    })
    .slice(0, 4)
})

// Menghitung resep yang ditambahkan dalam 7 hari terakhir
const weeklyRecipes = computed(() => {
  return recipes.value.filter((recipe) => {
    const date = parseDate(recipe.created_at)

    return (
      date &&
      date >= sevenDaysAgo &&
      date <= today
    )
  }).length
})

// Menghitung jumlah resep berdasarkan kategori
const composition = computed(() => {
  return categories.value
    .map((category) => {
      const total = recipes.value.filter(
        (recipe) =>
          Number(recipe.category_id) ===
          Number(category.id),
      ).length

      return {
        name: category.name,
        total,
      }
    })
    .filter((category) => category.total > 0)
})

// Menampilkan komposisi koleksi
const compositionText = computed(() => {
  if (composition.value.length === 0) {
    return 'Belum ada resep dalam koleksi'
  }

  return composition.value
    .map((item) => `${item.name} ${item.total}`)
    .join(' · ')
})

// Mengambil data dari Laravel
async function loadDashboard() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [
      recipeResponse,
      categoryResponse,
    ] = await Promise.all([
      getRecipes(),
      getCategories(),
    ])

    recipes.value = extractArray(recipeResponse)
    categories.value = extractArray(categoryResponse)
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Gagal memuat dashboard. Periksa koneksi API Laravel.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <div class="dashboard-page">

    <!-- Judul Dashboard -->
    <div class="dashboard-heading">
      <div>
        <h1>Dashboard</h1>
        <p>
          Selamat datang, Admin.
          Ini ringkasan koleksi resep Anda.
        </p>
      </div>

      <RouterLink
        to="/admin/recipes/create"
        class="primary-button"
      >
        <span class="plus-icon">+</span>
        Tambah Resep
      </RouterLink>
    </div>

    <!-- Pesan Kesalahan -->
    <div
      v-if="errorMessage"
      class="error-box"
    >
      <span>{{ errorMessage }}</span>

      <button
        type="button"
        @click="loadDashboard"
      >
        Coba Lagi
      </button>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="loading-box"
    >
      Memuat data dashboard...
    </div>

    <template v-else>

      <!-- Ringkasan Koleksi -->
      <div class="section-top">
        <h2>RINGKASAN KOLEKSI</h2>

        <span>
          Data per {{ dateFormat.format(today) }}
        </span>
      </div>

      <!-- Kartu Statistik -->
      <div class="stats-grid">

        <!-- Total Resep -->
        <div class="stat-card">
          <div class="stat-top">
            <span>Total Resep</span>
            <span class="stat-icon">▤</span>
          </div>

          <strong>
            {{ recipes.length }}
          </strong>

          <p>Seluruh resep dalam koleksi</p>
        </div>

        <!-- Total Kategori -->
        <div class="stat-card">
          <div class="stat-top">
            <span>Total Kategori</span>
            <span class="stat-icon">▦</span>
          </div>

          <strong>
            {{ categories.length }}
          </strong>

          <p>Kategori resep yang tersedia</p>
        </div>

        <!-- Resep Terbaru -->
        <div class="stat-card">
          <div class="stat-top">
            <span>Resep Terbaru</span>
            <span class="stat-icon">▣</span>
          </div>

          <strong>
            {{ weeklyRecipes }}
          </strong>

          <p>Ditambahkan dalam 7 hari terakhir</p>
        </div>

      </div>

      <!-- Judul Tabel -->
      <div class="recipes-heading">
        <div>
          <h2>Resep Terbaru</h2>

          <p>
            Menampilkan maksimal 4 resep
            yang terakhir ditambahkan.
          </p>
        </div>

        <RouterLink
          to="/admin/recipes"
          class="outline-button"
        >
          <span>→</span>
          Kelola Resep
        </RouterLink>
      </div>

      <!-- Tabel Resep -->
      <div class="table-wrapper">
        <table class="recipe-table">

          <thead>
            <tr>
              <th>Foto</th>
              <th>Nama Resep</th>
              <th>Kategori</th>
              <th>Waktu</th>
              <th>Kesulitan</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="recipe in recentRecipes"
              :key="recipe.id"
            >
              <!-- Gambar -->
              <td>
                <img
                  class="recipe-photo"
                  :src="recipeImage(recipe)"
                  :alt="recipe.title"
                  @error="useDefaultImage"
                />
              </td>

              <!-- Nama Resep -->
              <td>
                <RouterLink
                  :to="`/admin/recipes/${recipe.id}`"
                  class="recipe-title"
                >
                  {{ recipe.title }}
                </RouterLink>

                <span class="recipe-date">
                  {{ formatDate(recipe.created_at) }}
                </span>
              </td>

              <!-- Kategori -->
              <td>
                <span class="category-pill">
                  {{ categoryName(recipe) }}
                </span>
              </td>

              <!-- Waktu -->
              <td>
                {{ recipe.cooking_time ?? '-' }}
                {{ recipe.cooking_time != null ? 'menit' : '' }}
              </td>

              <!-- Kesulitan -->
              <td>
                {{ recipe.difficulty || '-' }}
              </td>
            </tr>

            <!-- Jika Belum Ada Resep -->
            <tr v-if="recentRecipes.length === 0">
              <td
                colspan="5"
                class="empty-state"
              >
                Belum ada resep.
                Klik Tambah Resep untuk mulai
                mengisi koleksi.
              </td>
            </tr>

          </tbody>
        </table>
      </div>

      <!-- Komposisi Koleksi -->
      <div class="composition-banner">

        <div class="composition-symbol">
          ♧
        </div>

        <div class="composition-description">
          <strong>Komposisi koleksi</strong>

          <p>
            {{ compositionText }}
          </p>
        </div>

        <strong class="composition-total">
          {{ recipes.length }} resep /
          {{ categories.length }} kategori
        </strong>

      </div>

      <!-- Footer -->
      <footer class="dashboard-footer">
        © {{ today.getFullYear() }}
        RecipeBox · Panel Admin
      </footer>

    </template>
  </div>
</template>

<style scoped>
.dashboard-page {
  padding: 40px;
  background: #f8f7f3;
  min-height: calc(100vh - 80px);
  color: #27332b;
}

.dashboard-heading,
.section-top,
.recipes-heading,
.stat-top,
.composition-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-heading {
  margin-bottom: 30px;
  gap: 20px;
}

.dashboard-heading h1 {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px;
}

.dashboard-heading p,
.recipes-heading p {
  color: #7a857d;
  font-size: 14px;
  margin: 0;
}

.primary-button {
  background: #315e43;
  color: white;
  border-radius: 10px;
  padding: 14px 20px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.primary-button:hover {
  background: #203e2d;
}

.plus-icon {
  font-size: 22px;
  font-weight: 400;
  line-height: 1;
}

.section-top {
  margin-bottom: 24px;
}

.section-top h2 {
  font-size: 12px;
  font-weight: 700;
  margin: 0;
}

.section-top span {
  color: #7a857d;
  font-size: 12px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
  margin-bottom: 28px;
}

.stat-card {
  background: #fff;
  border: 1px solid #e1e5dd;
  border-radius: 16px;
  padding: 24px;
  min-height: 180px;
}

.stat-top {
  gap: 12px;
  color: #78847a;
  font-size: 14px;
}

.stat-icon {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: 10px;
  background: #eaf0e8;
  color: #315e43;
  font-size: 23px;
}

.stat-card > strong {
  display: block;
  font-size: 38px;
  line-height: 1.2;
  margin-top: 18px;
}

.stat-card p {
  color: #7a857d;
  font-size: 12px;
  margin: 12px 0 0;
}

.recipes-heading {
  gap: 16px;
  margin-bottom: 20px;
}

.recipes-heading h2 {
  font-size: 22px;
  margin: 0 0 6px;
}

.outline-button {
  border: 1px solid #dfe5dc;
  background: white;
  color: #315e43;
  padding: 12px 20px;
  border-radius: 10px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.outline-button span {
  margin-right: 8px;
  font-size: 18px;
}

.table-wrapper {
  background: white;
  border: 1px solid #dfe5dc;
  border-radius: 12px;
  overflow-x: auto;
}

.recipe-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13px;
}

.recipe-table thead {
  background: #f0f3ee;
}

.recipe-table th {
  color: #78847a;
  font-weight: 600;
  font-size: 12px;
  padding: 17px 18px;
}

.recipe-table td {
  padding: 15px 18px;
  border-top: 1px solid #e1e5dd;
  vertical-align: middle;
}

.recipe-table th:first-child,
.recipe-table td:first-child {
  width: 80px;
}

.recipe-photo {
  width: 52px;
  height: 52px;
  border-radius: 8px;
  object-fit: cover;
  background: #eaf0e8;
}

.recipe-title {
  display: block;
  font-weight: 600;
  color: #27332b;
  text-decoration: none;
  margin-bottom: 5px;
}

.recipe-title:hover {
  color: #315e43;
  text-decoration: underline;
}

.recipe-date {
  display: block;
  color: #88948a;
  font-size: 12px;
}

.category-pill {
  display: inline-block;
  background: #eaf0e8;
  color: #315e43;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.empty-state {
  text-align: center;
  color: #7a857d;
  padding: 35px !important;
}

.composition-banner {
  background: #eaf0e8;
  border-radius: 16px;
  padding: 24px;
  margin-top: 28px;
  gap: 20px;
}

.composition-symbol {
  font-size: 32px;
  color: #315e43;
}

.composition-description {
  flex: 1;
  min-width: 0;
}

.composition-description strong,
.composition-total {
  color: #315e43;
  font-size: 14px;
}

.composition-description p {
  font-size: 12px;
  margin: 8px 0 0;
  color: #55715d;
}

.composition-total {
  white-space: nowrap;
}

.dashboard-footer {
  color: #89958b;
  font-size: 12px;
  margin-top: 28px;
}

.loading-box,
.error-box {
  background: white;
  border: 1px solid #e1e5dd;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
}

.error-box {
  color: #b84236;
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.error-box button {
  cursor: pointer;
  border: none;
  background: transparent;
  color: #315e43;
  font-weight: 600;
}

@media (max-width: 1100px) {
  .dashboard-page {
    padding: 28px;
  }

  .stats-grid {
    gap: 12px;
  }

  .stat-card {
    padding: 18px;
  }
}

@media (max-width: 760px) {
  .dashboard-page {
    padding: 20px;
  }

  .dashboard-heading,
  .recipes-heading,
  .composition-banner {
    align-items: flex-start;
    flex-direction: column;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stat-card {
    min-height: auto;
  }

  .composition-total {
    white-space: normal;
  }
}


/* Menjaga layout dashboard tetap stabil */
.dashboard-page {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  overflow-x: hidden;
}

.table-wrapper {
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
}

/* Menjaga tinggi baris agar tidak berubah */
.recipe-table tbody tr {
  height: 80px;
}

/* Menjaga gambar tetap stabil */
.recipe-photo {
  display: block;
  width: 52px;
  height: 52px;
  min-width: 52px;
  min-height: 52px;
  object-fit: cover;
  border-radius: 8px;
  background: #eaf0e8;
}

/* Menjaga posisi tulisan */
.recipe-table td {
  vertical-align: middle;
}

.recipe-title {
  display: block;
  line-height: 1.4;
}

.recipe-date {
  display: block;
  line-height: 1.4;
  margin-top: 4px;
}

.category-pill {
  display: inline-block;
  white-space: nowrap;
}

</style>
