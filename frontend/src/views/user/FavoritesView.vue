
<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getFavorites, removeFavorite } from '../../services/favoriteService'
import { toastSuccess, toastError } from '../../services/toast'

const router = useRouter()

const favorites = ref([])
const loading = ref(true)
const error = ref('')
const removingId = ref(null)

function unwrapList(payload) {
  const value = payload?.data ?? payload

  if (Array.isArray(value)) return value
  if (Array.isArray(value?.data)) return value.data

  return []
}

function normalizeFavorite(item) {
  // Laravel dapat mengirim data favorit sebagai
  // { recipe: {...} } atau langsung sebagai objek resep.
  const recipe = item.recipe ?? item

  return {
    ...recipe,
    favoriteId: item.id,
    recipeId: recipe.id ?? item.recipe_id,
    savedAt: item.created_at ?? recipe.pivot?.created_at ?? null,
  }
}

const recipes = computed(() =>
  favorites.value
    .map(normalizeFavorite)
    .filter((recipe) => recipe.recipeId != null)
)

async function loadFavorites() {
  loading.value = true
  error.value = ''

  try {
    const response = await getFavorites()
    favorites.value = unwrapList(response)
  } catch (err) {
    console.error('Gagal memuat favorit:', err)

    error.value =
      err.response?.data?.message ||
      'Gagal memuat resep favorit. Silakan coba lagi.'

    // Toast gagal memuat favorit
    toastError(error.value)
  } finally {
    loading.value = false
  }
}

async function deleteFavorite(recipe) {
  if (removingId.value !== null) return

  removingId.value = recipe.recipeId
  error.value = ''

  try {
    // Hapus favorit melalui REST API Laravel
    await removeFavorite(recipe.recipeId)

    // Perbarui daftar favorit tanpa reload halaman
    favorites.value = favorites.value.filter((item) => {
      const current = normalizeFavorite(item)

      return String(current.recipeId) !== String(recipe.recipeId)
    })

    // Toast berhasil menghapus favorit
    toastSuccess(
      `${recipe.title} berhasil dihapus dari favorit!`
    )
  } catch (err) {
    console.error('Gagal menghapus favorit:', err)

    error.value =
      err.response?.data?.message ||
      'Gagal menghapus favorit. Silakan coba lagi.'

    // Toast gagal menghapus favorit
    toastError(error.value)
  } finally {
    removingId.value = null
  }
}

function detail(id) {
  router.push(`/recipes/${id}`)
}

function imageSource(recipe) {
  return recipe.image_url || ''
}

function categoryName(recipe) {
  return (
    recipe.category?.name ||
    recipe.category_name ||
    'Tanpa Kategori'
  )
}

function difficulty(recipe) {
  return recipe.difficulty || 'Tidak diketahui'
}

onMounted(loadFavorites)
</script>

<template>
  <main class="favorites-page">
    <div class="favorites-container">
      <div class="heading-row">
        <div>
          <p class="eyebrow">KOLEKSI PERSONAL ANDA</p>
          <h1>Favorit Saya</h1>
          <p class="subtitle">
            Resep yang ingin dimasak lagi, tersimpan di satu tempat.
          </p>
        </div>

        <RouterLink to="/recipes" class="explore-button">
          <span aria-hidden="true">➜</span>
          Jelajahi Resep
        </RouterLink>
      </div>

      <div class="count-line" aria-live="polite">
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.9"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path
            d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"
          />
        </svg>

        <strong>{{ recipes.length }} resep favorit</strong>
        <span class="separator">·</span>
        <span>Terbaru disimpan</span>
      </div>

      <!-- Pesan error di halaman -->
      <div v-if="error" class="error-message" role="alert">
        {{ error }}

        <button
          type="button"
          @click="loadFavorites"
        >
          Coba lagi
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="state-panel">
        Memuat resep favorit...
      </div>

      <!-- Favorit kosong -->
      <div
        v-else-if="recipes.length === 0"
        class="empty-state"
      >
        <div class="empty-heart">♡</div>

        <h2>Belum ada resep favorit</h2>

        <p>
          Temukan resep yang menarik dan simpan agar mudah
          ditemukan kembali.
        </p>

        <RouterLink to="/recipes" class="empty-link">
          Jelajahi Resep →
        </RouterLink>
      </div>

      <!-- Daftar resep favorit -->
      <div v-else class="recipe-grid">
        <article
          v-for="recipe in recipes"
          :key="recipe.recipeId"
          class="recipe-card"
        >
          <div
            class="recipe-image"
            role="button"
            tabindex="0"
            @click="detail(recipe.recipeId)"
            @keydown.enter="detail(recipe.recipeId)"
          >
            <img
              v-if="imageSource(recipe)"
              :src="imageSource(recipe)"
              :alt="recipe.title"
              loading="lazy"
              @error="($event.target.style.display = 'none')"
            />

            <div class="image-fallback">
              RecipeBox
            </div>

            <button
              type="button"
              class="heart-button"
              :disabled="removingId === recipe.recipeId"
              :aria-label="`Hapus ${recipe.title} dari favorit`"
              @click.stop="deleteFavorite(recipe)"
            >
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path
                  d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"
                />
              </svg>
            </button>
          </div>

          <div class="card-body">
            <span class="category-tag">
              {{ categoryName(recipe) }}
            </span>

            <h2>{{ recipe.title }}</h2>

            <div class="recipe-meta">
              <span>
                ◷ &nbsp;{{ recipe.cooking_time ?? '—' }} menit
              </span>

              <span class="meta-dot">·</span>

              <span>
                ▥ &nbsp;{{ difficulty(recipe) }}
              </span>
            </div>

            <div class="card-actions">
              <button
                type="button"
                class="detail-link"
                @click="detail(recipe.recipeId)"
              >
                Lihat Detail →
              </button>

              <button
                type="button"
                class="remove-link"
                :disabled="removingId === recipe.recipeId"
                @click="deleteFavorite(recipe)"
              >
                ♡ &nbsp;{{
                  removingId === recipe.recipeId
                    ? 'Menghapus...'
                    : 'Hapus favorit'
                }}
              </button>
            </div>
          </div>
        </article>
      </div>

      <p
        v-if="!loading && recipes.length > 0"
        class="helper-text"
      >
        <span aria-hidden="true">ⓘ</span>
        Menghapus dari favorit tidak menghapus resep.
        Anda bisa menyimpannya kembali kapan saja.
      </p>
    </div>
  </main>
</template>

<style scoped>
.favorites-page {
  background: #f8f7f3;
  min-height: calc(100vh - 250px);
  padding: 52px 0 95px;
  color: #26352d;
}

.favorites-container {
  width: min(1312px, calc(100% - 88px));
  margin: auto;
}

.heading-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
}

.eyebrow {
  color: #315f44;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.03em;
  margin: 0 0 15px;
}

h1,
h2 {
  font-family: Georgia, 'Times New Roman', serif;
  font-weight: 400;
}

h1 {
  font-size: 46px;
  line-height: 1.15;
  margin: 0 0 13px;
}

.subtitle {
  font-size: 15px;
  color: #7e8980;
  margin: 0;
}

.explore-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  text-decoration: none;
  color: #26352d;
  border: 1px solid #dce3da;
  border-radius: 12px;
  padding: 14px 20px;
  background: #fff;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.explore-button span {
  font-size: 20px;
  color: #315f44;
}

.count-line {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 32px 0 29px;
  font-size: 13px;
  color: #89948a;
}

.count-line svg {
  width: 20px;
  height: 20px;
  color: #315f44;
}

.count-line strong {
  font-size: 14px;
  color: #315f44;
}

.separator {
  color: #bdc6bb;
}

.recipe-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 24px;
}

.recipe-card {
  background: white;
  border: 1px solid #dce3da;
  border-radius: 16px;
  overflow: hidden;
  min-width: 0;
}

.recipe-image {
  height: 190px;
  position: relative;
  background: #edf0e9;
  cursor: pointer;
  overflow: hidden;
}

.recipe-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: relative;
  z-index: 1;
}

.image-fallback {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #8a9c8c;
  font-family: Georgia, serif;
  font-size: 23px;
}

.heart-button {
  position: absolute;
  z-index: 2;
  right: 13px;
  top: 13px;
  border: 0;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  background: #315f44;
  color: white;
  cursor: pointer;
}

.heart-button svg {
  width: 20px;
  height: 20px;
}

.heart-button:disabled,
.remove-link:disabled {
  opacity: 0.55;
  cursor: wait;
}

.card-body {
  padding: 20px;
}

.category-tag {
  display: inline-block;
  background: #eaf1e9;
  color: #315f44;
  font-size: 11px;
  font-weight: 600;
  border-radius: 20px;
  padding: 5px 11px;
}

.card-body h2 {
  font-size: 23px;
  line-height: 1.25;
  margin: 13px 0 10px;
  overflow-wrap: anywhere;
}

.recipe-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #839086;
  font-size: 12px;
}

.meta-dot {
  color: #c6cec5;
}

.card-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-top: 21px;
}

.card-actions button {
  border: 0;
  background: none;
  cursor: pointer;
  font-size: 12px;
  color: #315f44;
  padding: 0;
}

.detail-link {
  font-weight: 700;
}

.helper-text {
  color: #87938a;
  font-size: 13px;
  margin-top: 32px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.helper-text span {
  font-size: 18px;
}

.state-panel,
.empty-state {
  background: #fff;
  border: 1px solid #dce3da;
  border-radius: 16px;
  text-align: center;
  padding: 65px 20px;
  color: #7e8980;
}

.empty-heart {
  font-size: 50px;
  color: #315f44;
}

.empty-state h2 {
  font-size: 28px;
  color: #26352d;
  margin: 10px 0;
}

.empty-state p {
  margin-bottom: 25px;
}

.empty-link {
  display: inline-block;
  background: #315f44;
  color: white;
  text-decoration: none;
  padding: 12px 20px;
  border-radius: 9px;
}

.error-message {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  background: #fff0ee;
  color: #a3332c;
  padding: 13px 16px;
  border-radius: 9px;
  margin-bottom: 20px;
}

.error-message button {
  border: 0;
  background: none;
  color: inherit;
  text-decoration: underline;
  cursor: pointer;
}

@media (max-width: 1000px) {
  .recipe-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .favorites-page {
    padding: 30px 0 65px;
  }

  .favorites-container {
    width: calc(100% - 32px);
  }

  .heading-row {
    align-items: flex-start;
    flex-direction: column;
  }

  h1 {
    font-size: 38px;
  }

  .recipe-grid {
    grid-template-columns: 1fr;
  }

  .recipe-image {
    height: 210px;
  }

  .count-line {
    margin: 26px 0 22px;
  }
}
</style>
