
<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { getRecipes, getCategories } from '../../services/recipeService'
import { deleteRecipe } from '../../services/recipeService'


const showDeleteModal = ref(false)
const selectedRecipe = ref(null)
const deleting = ref(false)
const deleteError = ref('')

function confirmDelete(recipe) {
  selectedRecipe.value = recipe
  deleteError.value = ''
  showDeleteModal.value = true
}

function closeDeleteModal() {
  if (deleting.value) return

  showDeleteModal.value = false
  selectedRecipe.value = null
  deleteError.value = ''
}

async function handleDelete() {
  if (!selectedRecipe.value || deleting.value) return

  deleting.value = true
  deleteError.value = ''

  try {
    await deleteRecipe(selectedRecipe.value.id)

    // Perbarui daftar lokal setelah Laravel berhasil menghapus.
    recipes.value = recipes.value.filter(
      (recipe) => recipe.id !== selectedRecipe.value.id,
    )

    // Pastikan halaman pagination tetap valid.
    if (currentPage.value > totalPages.value) {
      currentPage.value = totalPages.value
    }

    showDeleteModal.value = false
    selectedRecipe.value = null
  } catch (error) {
    deleteError.value =
      error.response?.data?.message ||
      'Gagal menghapus resep. Silakan coba lagi.'
  } finally {
    deleting.value = false
  }
}

const recipes = ref([])
const categories = ref([])
const loading = ref(true)
const errorMessage = ref('')

const search = ref('')
const selectedCategory = ref('')
const currentPage = ref(1)
const perPage = 6

function extractArray(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function formatDate(value) {
  if (!value) return '-'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'

  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

function categoryName(recipe) {
  if (recipe.category?.name) return recipe.category.name

  const category = categories.value.find(
    (item) => Number(item.id) === Number(recipe.category_id),
  )

  return category?.name || 'Tanpa Kategori'
}


const placeholderImage = '/images/default-recipe.svg'

function recipeImage(recipe) {
  return recipe.image_url?.trim() || placeholderImage
}

function handleImageError(event) {
  const image = event.target

  // Mencegah pemuatan ulang gambar yang terus gagal
  image.onerror = null

  if (!image.src.endsWith(placeholderImage)) {
    image.src = placeholderImage
  }
}

const filteredRecipes = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return recipes.value
    .filter((recipe) => {
      const matchesSearch = (recipe.title || '')
        .toLowerCase()
        .includes(keyword)

      const matchesCategory =
        !selectedCategory.value ||
        String(recipe.category_id) === selectedCategory.value

      return matchesSearch && matchesCategory
    })
    .sort((a, b) => {
      const first = new Date(a.created_at || 0).getTime() || 0
      const second = new Date(b.created_at || 0).getTime() || 0
      return second - first
    })
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredRecipes.value.length / perPage)),
)

const paginatedRecipes = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredRecipes.value.slice(start, start + perPage)
})

const startItem = computed(() =>
  filteredRecipes.value.length === 0
    ? 0
    : (currentPage.value - 1) * perPage + 1,
)

const endItem = computed(() =>
  Math.min(currentPage.value * perPage, filteredRecipes.value.length),
)

watch([search, selectedCategory], () => {
  currentPage.value = 1
})

async function loadRecipes() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [recipeResponse, categoryResponse] = await Promise.all([
      getRecipes(),
      getCategories(),
    ])

    recipes.value = extractArray(recipeResponse)
    categories.value = extractArray(categoryResponse)
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Gagal mengambil data resep dari Laravel.'
  } finally {
    loading.value = false
  }
}

onMounted(loadRecipes)
</script>

<template>
  <main class="recipe-page">
    <div class="page-heading">
      <div>
        <h1>Kelola Resep</h1>
        <p>Lihat, ubah, dan kelola seluruh resep dalam satu tempat.</p>
      </div>

      <RouterLink to="/admin/recipes/create" class="primary-button">
        <span class="plus">+</span>
        Tambah Resep
      </RouterLink>
    </div>

    <section class="recipe-panel">
      <div class="filter-row">
        <div class="search-wrapper">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" aria-hidden="true">
            <circle cx="10.8" cy="10.8" r="6.5" />
            <path d="m16 16 5 5" />
          </svg>

          <input
            v-model="search"
            type="search"
            placeholder="Cari nama resep..."
            aria-label="Cari nama resep"
          />
        </div>

        <select
          v-model="selectedCategory"
          aria-label="Filter kategori"
          class="category-select"
        >
          <option value="">Semua kategori</option>
          <option
            v-for="category in categories"
            :key="category.id"
            :value="String(category.id)"
          >
            {{ category.name }}
          </option>
        </select>
      </div>

      <div class="list-summary">
        <strong>{{ filteredRecipes.length }} resep tersedia</strong>
        <span>Urutkan: terbaru ditambahkan</span>
      </div>

      <div v-if="loading" class="state-message">
        Memuat daftar resep...
      </div>

      <div v-else-if="errorMessage" class="state-message error">
        {{ errorMessage }}
        <button type="button" @click="loadRecipes">Coba Lagi</button>
      </div>

      <template v-else>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Foto</th>
                <th>Nama Resep</th>
                <th>Kategori</th>
                <th>Waktu Memasak</th>
                <th>Tingkat Kesulitan</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="recipe in paginatedRecipes" :key="recipe.id">
                <td>
                  <img
                    :src="recipeImage(recipe)"
                    :alt="recipe.title"
                    class="recipe-photo"
                    @error="handleImageError"
                  />
                </td>

                <td>
                  <span class="recipe-name">{{ recipe.title }}</span>
                </td>

                <td>
                  <span class="category-badge">
                    {{ categoryName(recipe) }}
                  </span>
                </td>

                <td>
                  {{ recipe.cooking_time ?? '-' }}
                  {{ recipe.cooking_time != null ? 'menit' : '' }}
                </td>

                <td>{{ recipe.difficulty || '-' }}</td>

                <td class="date-cell">
                  {{ formatDate(recipe.created_at) }}
                </td>

                <td>
                  <div class="action-buttons">
                    <RouterLink
                      :to="`/admin/recipes/${recipe.id}`"
                      class="action-button"
                      title="Lihat Detail"
                      aria-label="Lihat Detail"
                    >
                      <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" />
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                    </RouterLink>

                    <RouterLink
                      :to="`/admin/recipes/${recipe.id}/edit`"
                      class="action-button"
                      title="Edit Resep"
                      aria-label="Edit Resep"
                    >
                      <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <path d="M4 20h4l11-11-4-4L4 16v4Z" />
                        <path d="m13 7 4 4" />
                      </svg>
                    </RouterLink>

                    <button
                    type="button"
                    class="action-button delete-button"
                    title="Hapus Resep"
                    :aria-label="`Hapus ${recipe.title}`"
                    @click="confirmDelete(recipe)"
                    >
                      <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13" />
                        <path d="M10 11v6M14 11v6" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="paginatedRecipes.length === 0">
                <td colspan="7" class="empty-state">
                  {{
                    recipes.length === 0
                      ? 'Belum ada resep yang ditambahkan.'
                      : 'Tidak ada resep yang sesuai dengan pencarian.'
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pagination-row">
          <span>
            Menampilkan {{ startItem }}–{{ endItem }}
            dari {{ filteredRecipes.length }} resep
          </span>

          <div class="pagination-controls">
            <button
              type="button"
              class="page-navigation"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              ← Sebelumnya
            </button>

            <button
              v-for="page in totalPages"
              :key="page"
              type="button"
              class="page-number"
              :class="{ active: currentPage === page }"
              @click="currentPage = page"
            >
              {{ page }}
            </button>

            <button
              type="button"
              class="page-navigation"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Berikutnya →
            </button>
          </div>
        </div>
      </template>
    </section>

    <div class="action-legend">
      <span>Aksi resep:</span>
      <span>◉ Lihat Detail</span>
      <span>✎ Edit</span>
      <span class="legend-delete">♜ Hapus</span>
    </div>

    
    <div
    v-if="showDeleteModal"
    class="modal-overlay"
    @click.self="closeDeleteModal"
    >
    <div
        class="delete-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-title"
    >
        <div class="delete-icon">!</div>

        <h2 id="delete-title">Hapus Resep?</h2>

        <p>
        Apakah Anda yakin ingin menghapus resep
        <strong>{{ selectedRecipe?.title }}</strong>?
        Tindakan ini tidak dapat dibatalkan.
        </p>

        <p v-if="deleteError" class="delete-error">
        {{ deleteError }}
        </p>

        <div class="modal-actions">
        <button
            type="button"
            class="modal-cancel"
            :disabled="deleting"
            @click="closeDeleteModal"
        >
            Batal
        </button>

        <button
            type="button"
            class="modal-confirm"
            :disabled="deleting"
            @click="handleDelete"
        >
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus Resep' }}
        </button>
        </div>
    </div>
    </div>


    <footer class="page-footer">
      © {{ new Date().getFullYear() }} RecipeBox · Panel Admin
    </footer>
  </main>
</template>

<style scoped>
.recipe-page {
  min-height: calc(100vh - 80px);
  padding: 40px;
  background: #f8f7f3;
  color: #27332b;
}

.page-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 28px;
}

.page-heading h1 {
  font-size: 32px;
  margin: 0 0 8px;
  font-weight: 700;
}

.page-heading p {
  margin: 0;
  color: #7b867e;
  font-size: 14px;
}

.primary-button {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 13px 20px;
  background: #315e43;
  color: #fff;
  text-decoration: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.primary-button:hover {
  background: #203e2d;
}

.plus {
  font-size: 23px;
  line-height: 1;
}

.recipe-panel {
  background: #fff;
  border: 1px solid #e1e5dd;
  border-radius: 16px;
  padding: 22px;
}

.filter-row {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}

.search-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid #dfe5dc;
  border-radius: 10px;
  padding: 0 14px;
  min-width: 0;
}

.search-wrapper svg {
  width: 20px;
  height: 20px;
  color: #78847a;
  flex-shrink: 0;
}

.search-wrapper input {
  border: 0;
  outline: 0;
  width: 100%;
  padding: 14px 0;
  font: inherit;
  font-size: 14px;
  background: transparent;
  color: #27332b;
}

.category-select {
  min-width: 190px;
  border: 1px solid #dfe5dc;
  border-radius: 10px;
  background: #fff;
  padding: 12px 14px;
  font: inherit;
  font-size: 14px;
  color: #27332b;
}

.list-summary {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  margin-bottom: 22px;
  font-size: 13px;
}

.list-summary span {
  color: #88948a;
}

.table-wrapper {
  border: 1px solid #e1e5dd;
  border-radius: 12px;
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13px;
}

thead {
  background: #f0f3ee;
}

th {
  padding: 16px 14px;
  color: #78847a;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

td {
  padding: 14px;
  border-top: 1px solid #e1e5dd;
  vertical-align: middle;
}

.recipe-photo {
  width: 52px;
  height: 52px;
  object-fit: cover;
  border-radius: 8px;
  background: #eaf0e8;
}

.recipe-name {
  font-weight: 600;
  white-space: nowrap;
}

.category-badge {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 20px;
  background: #eaf0e8;
  color: #315e43;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.date-cell {
  color: #89958b;
  white-space: nowrap;
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
}

.action-button {
  width: 24px;
  height: 28px;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  border: 0;
  background: transparent;
  color: #315e43;
  cursor: pointer;
}

.action-button svg {
  width: 19px;
  height: 19px;
}

.action-button:hover {
  color: #203e2d;
}

.delete-button {
  color: #b84236;
}

.delete-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.empty-state,
.state-message {
  text-align: center;
  color: #7b867e;
  padding: 30px;
}

.error {
  color: #b84236;
}

.error button {
  margin-left: 12px;
  color: #315e43;
  background: transparent;
  border: 0;
  cursor: pointer;
  font-weight: 600;
}

.pagination-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding-top: 28px;
  color: #7b867e;
  font-size: 13px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-controls button {
  font: inherit;
  cursor: pointer;
  border: 0;
}

.page-navigation {
  background: transparent;
  color: #315e43;
  padding: 10px 6px;
}

.page-navigation:disabled {
  color: #a4ada6;
  cursor: not-allowed;
}

.page-number {
  min-width: 36px;
  height: 36px;
  border-radius: 8px;
  background: transparent;
  color: #27332b;
}

.page-number.active {
  background: #315e43;
  color: white;
}

.action-legend {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 24px;
  color: #7b867e;
  font-size: 12px;
  margin-top: 26px;
}

.action-legend span:not(:first-child) {
  color: #315e43;
}

.action-legend .legend-delete {
  color: #b84236 !important;
}

.page-footer {
  color: #89958b;
  font-size: 12px;
  margin-top: 28px;
}

@media (max-width: 900px) {
  .recipe-page {
    padding: 24px;
  }

  .pagination-row {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 600px) {
  .page-heading,
  .filter-row {
    flex-direction: column;
    align-items: stretch;
  }

  .page-heading h1 {
    font-size: 27px;
  }

  .category-select {
    width: 100%;
  }

  .recipe-panel {
    padding: 14px;
  }

  .list-summary {
    align-items: flex-start;
    flex-direction: column;
  }

  .pagination-controls {
    flex-wrap: wrap;
  }
}


/* Menjaga ukuran halaman tetap stabil */
.recipe-page {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  overflow-x: hidden;
}

/* Mencegah tabel mengubah lebar layout */
.recipe-panel {
  min-width: 0;
  max-width: 100%;
  box-sizing: border-box;
}

.table-wrapper {
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
}

/* Menjaga ukuran baris tabel */
.recipe-table,
table {
  border-collapse: collapse;
}

tbody tr {
  height: 76px;
}

/* Mencegah gambar mengubah ukuran baris */
.recipe-photo {
  display: block;
  width: 52px;
  height: 52px;
  min-width: 52px;
  min-height: 52px;
  object-fit: cover;
}

/* Menjaga elemen pagination tetap stabil */
.pagination-row {
  min-height: 60px;
}


.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(25, 40, 30, 0.48);
}

.delete-modal {
  width: 100%;
  max-width: 420px;
  padding: 28px;
  box-sizing: border-box;
  background: #fff;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
}

.delete-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  margin: 0 auto 18px;
  border-radius: 50%;
  background: #fff0ed;
  color: #b84236;
  font-size: 27px;
  font-weight: 700;
}

.delete-modal h2 {
  margin: 0 0 12px;
  color: #27332b;
  font-size: 21px;
}

.delete-modal p {
  color: #7b867e;
  font-size: 13px;
  line-height: 1.7;
}

.delete-modal p strong {
  color: #27332b;
}

.delete-error {
  color: #b84236 !important;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.modal-cancel,
.modal-confirm {
  flex: 1;
  padding: 12px 14px;
  border-radius: 9px;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.modal-cancel {
  border: 1px solid #dfe5dc;
  background: #fff;
  color: #27332b;
}

.modal-confirm {
  border: 1px solid #b84236;
  background: #b84236;
  color: white;
}

.modal-confirm:disabled,
.modal-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

</style>
