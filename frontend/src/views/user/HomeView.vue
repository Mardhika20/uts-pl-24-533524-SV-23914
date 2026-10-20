
<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getRecipes, getCategories } from '../../services/recipeService'
import {
  getFavorites,
  addFavorite,
  removeFavorite,
} from '../../services/favoriteService'
import { getAuthUser } from '../../services/authService'

const router = useRouter()
const currentUser = getAuthUser()

const recipes = ref([])
const categories = ref([])
const favoriteIds = ref(new Set())
const processingFavorites = ref(new Set())
const failedImages = ref(new Set())

const loading = ref(true)
const errorMessage = ref('')
const favoriteError = ref('')
const searchQuery = ref('')

const firstName = computed(() => {
  return currentUser?.name?.trim().split(/\s+/)[0] || 'Pengguna'
})

function extractArray(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function normalizeFavorites(items) {
  return new Set(
    items
      .map((item) => item.recipe_id ?? item.recipe?.id ?? item.id)
      .filter((id) => id !== undefined && id !== null)
      .map(Number)
  )
}

const sortedRecipes = computed(() => {
  return [...recipes.value].sort((a, b) => {
    const dateA = new Date(a.created_at || 0).getTime()
    const dateB = new Date(b.created_at || 0).getTime()
    return dateB - dateA
  })
})

const latestRecipes = computed(() => sortedRecipes.value.slice(0, 4))

const recommendedRecipes = computed(() => {
  return sortedRecipes.value.slice(4, 8)
})

const heroRecipe = computed(() => {
  return (
    sortedRecipes.value.find((recipe) => recipe.image_url) ||
    sortedRecipes.value[0] ||
    null
  )
})

const categoryCards = computed(() => {
  return categories.value.map((category) => ({
    ...category,
    total: recipes.value.filter(
      (recipe) => Number(recipe.category_id) === Number(category.id)
    ).length,
  }))
})

function categoryName(recipe) {
  if (recipe.category?.name) return recipe.category.name

  const category = categories.value.find(
    (item) => Number(item.id) === Number(recipe.category_id)
  )

  return category?.name || 'Tanpa Kategori'
}

function recipeImage(recipe) {
  if (!recipe?.image_url || failedImages.value.has(recipe.id)) {
    return ''
  }

  return recipe.image_url.trim()
}

function handleImageError(recipeId) {
  const updated = new Set(failedImages.value)
  updated.add(recipeId)
  failedImages.value = updated
}

function isFavorite(recipeId) {
  return favoriteIds.value.has(Number(recipeId))
}

function isProcessing(recipeId) {
  return processingFavorites.value.has(Number(recipeId))
}

async function toggleFavorite(recipeId) {
  const id = Number(recipeId)

  if (isProcessing(id)) return

  favoriteError.value = ''

  const processing = new Set(processingFavorites.value)
  processing.add(id)
  processingFavorites.value = processing

  try {
    if (isFavorite(id)) {
      await removeFavorite(id)

      const updated = new Set(favoriteIds.value)
      updated.delete(id)
      favoriteIds.value = updated
    } else {
      await addFavorite(id)

      const updated = new Set(favoriteIds.value)
      updated.add(id)
      favoriteIds.value = updated
    }
  } catch (error) {
    favoriteError.value =
      error.response?.data?.message ||
      'Gagal memperbarui favorit. Silakan coba lagi.'
  } finally {
    const updated = new Set(processingFavorites.value)
    updated.delete(id)
    processingFavorites.value = updated
  }
}

function goToDetail(recipeId) {
  router.push(`/recipes/${recipeId}`)
}

function goToExplore(categoryId = null) {
  if (categoryId) {
    router.push({
      path: '/recipes',
      query: { category: String(categoryId) },
    })
    return
  }

  router.push('/recipes')
}

function handleSearch() {
  const keyword = searchQuery.value.trim()

  router.push({
    path: '/recipes',
    query: keyword ? { search: keyword } : {},
  })
}

async function loadHomeData() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [recipeResponse, categoryResponse, favoriteResponse] =
      await Promise.all([
        getRecipes(),
        getCategories(),
        getFavorites(),
      ])

    recipes.value = extractArray(recipeResponse)
    categories.value = extractArray(categoryResponse)
    favoriteIds.value = normalizeFavorites(
      extractArray(favoriteResponse)
    )
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Data Beranda gagal dimuat. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}

onMounted(loadHomeData)
</script>

<template>
  <div class="home-page">
    <div v-if="errorMessage" class="alert-error">
      {{ errorMessage }}
      <button type="button" @click="loadHomeData">
        Coba Lagi
      </button>
    </div>

    <div v-if="favoriteError" class="alert-error">
      {{ favoriteError }}
    </div>

    <!-- HERO -->
    <section class="hero-section">
      <div class="hero-content">
        <p class="hero-eyebrow">
          ☼ &nbsp; HAI {{ firstName.toUpperCase() }}, MAU MASAK APA HARI INI?
        </p>

        <h1>Ide masakan baru, rasa yang selalu dekat.</h1>

        <p class="hero-description">
          Dari sarapan sederhana hingga hidangan keluarga.
          Temukan resep Indonesia untuk setiap hari Anda.
        </p>

        <form class="hero-search" @submit.prevent="handleSearch">
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Cari soto, nasi goreng, atau camilan..."
            aria-label="Cari resep"
          />

          <button type="submit">
            Cari Resep
          </button>
        </form>
      </div>

      <div class="hero-visual">
        <img
          v-if="heroRecipe && recipeImage(heroRecipe)"
          :src="recipeImage(heroRecipe)"
          :alt="heroRecipe.title"
          @error="handleImageError(heroRecipe.id)"
        />

        <div v-else class="image-placeholder">
          <span>RecipeBox</span>
        </div>

        <button
          v-if="heroRecipe"
          type="button"
          class="hero-recipe"
          @click="goToDetail(heroRecipe.id)"
        >
          <span class="hero-recipe-icon">♨</span>

          <span>
            <small>HANGAT DARI DAPUR</small>
            <strong>{{ heroRecipe.title }}</strong>
            <small>
              {{ heroRecipe.cooking_time }} menit ·
              {{ heroRecipe.difficulty || 'Mudah' }}
              <template v-if="heroRecipe.servings">
                · {{ heroRecipe.servings }} porsi
              </template>
            </small>
          </span>
        </button>
      </div>
    </section>

    <!-- KATEGORI -->
    <section class="home-section">
      <div class="section-heading">
        <h2>Selera Anda, pilihan Anda</h2>

        <button
          type="button"
          class="section-link"
          @click="goToExplore()"
        >
          Jelajahi semua →
        </button>
      </div>

      <div v-if="loading" class="empty-state">
        Memuat kategori resep...
      </div>

      <div v-else-if="categoryCards.length" class="category-grid">
        <button
          v-for="category in categoryCards"
          :key="category.id"
          type="button"
          class="category-card"
          @click="goToExplore(category.id)"
        >
          <span class="category-icon">♨</span>

          <span class="category-info">
            <strong>{{ category.name }}</strong>
            <small>{{ category.total }} resep</small>
          </span>
        </button>
      </div>

      <div v-else class="empty-state">
        Belum ada kategori resep.
      </div>
    </section>

    <!-- RESEP TERBARU -->
    <section class="home-section">
      <div class="section-heading">
        <div>
          <h2>Resep Terbaru</h2>
          <p>Inspirasi yang baru ditambahkan ke dapur RecipeBox.</p>
        </div>

        <button
          type="button"
          class="section-link"
          @click="goToExplore()"
        >
          Lihat semua resep →
        </button>
      </div>

      <div v-if="loading" class="empty-state">
        Memuat resep terbaru...
      </div>

      <div v-else-if="latestRecipes.length" class="recipe-grid">
        <article
          v-for="recipe in latestRecipes"
          :key="recipe.id"
          class="recipe-card"
        >
          <div class="recipe-image">
            <img
              v-if="recipeImage(recipe)"
              :src="recipeImage(recipe)"
              :alt="recipe.title"
              loading="lazy"
              @error="handleImageError(recipe.id)"
              @click="goToDetail(recipe.id)"
            />

            <div
              v-else
              class="image-placeholder"
              @click="goToDetail(recipe.id)"
            >
              RecipeBox
            </div>

            <button
              type="button"
              class="favorite-button"
              :class="{ active: isFavorite(recipe.id) }"
              :disabled="isProcessing(recipe.id)"
              :aria-label="
                isFavorite(recipe.id)
                  ? 'Hapus dari favorit'
                  : 'Tambahkan ke favorit'
              "
              @click="toggleFavorite(recipe.id)"
            >
              {{ isFavorite(recipe.id) ? '♥' : '♡' }}
            </button>
          </div>

          <div
            class="recipe-info"
            @click="goToDetail(recipe.id)"
          >
            <span class="recipe-category">
              {{ categoryName(recipe) }}
            </span>

            <h3>{{ recipe.title }}</h3>

            <p class="recipe-meta">
              ◷ {{ recipe.cooking_time }} menit
              <span>·</span>
              {{ recipe.difficulty || 'Mudah' }}
            </p>
          </div>
        </article>
      </div>

      <div v-else class="empty-state">
        Belum ada resep terbaru.
      </div>
    </section>

    <!-- REKOMENDASI -->
    <section class="home-section">
      <div class="section-heading">
        <div>
          <h2>Rekomendasi Resep</h2>
          <p>Pilihan hangat untuk melengkapi menu harian Anda.</p>
        </div>
      </div>

      <div v-if="loading" class="empty-state">
        Memuat rekomendasi resep...
      </div>

      <div
        v-else-if="recommendedRecipes.length"
        class="recipe-grid"
      >
        <article
          v-for="recipe in recommendedRecipes"
          :key="recipe.id"
          class="recipe-card"
        >
          <div class="recipe-image">
            <img
              v-if="recipeImage(recipe)"
              :src="recipeImage(recipe)"
              :alt="recipe.title"
              loading="lazy"
              @error="handleImageError(recipe.id)"
              @click="goToDetail(recipe.id)"
            />

            <div
              v-else
              class="image-placeholder"
              @click="goToDetail(recipe.id)"
            >
              RecipeBox
            </div>

            <button
              type="button"
              class="favorite-button"
              :class="{ active: isFavorite(recipe.id) }"
              :disabled="isProcessing(recipe.id)"
              @click="toggleFavorite(recipe.id)"
            >
              {{ isFavorite(recipe.id) ? '♥' : '♡' }}
            </button>
          </div>

          <div
            class="recipe-info"
            @click="goToDetail(recipe.id)"
          >
            <span class="recipe-category">
              {{ categoryName(recipe) }}
            </span>

            <h3>{{ recipe.title }}</h3>

            <p class="recipe-meta">
              ◷ {{ recipe.cooking_time }} menit
              <span>·</span>
              {{ recipe.difficulty || 'Mudah' }}
            </p>
          </div>
        </article>
      </div>

      <div v-else class="empty-state">
        Belum ada rekomendasi lainnya.
      </div>

      <div class="all-recipes-action">
        <button type="button" @click="goToExplore()">
          → &nbsp; Lihat Semua Resep
        </button>
      </div>
    </section>

    <!-- FAVORIT CTA -->
    <section class="favorite-banner">
      <span class="favorite-banner-icon">♡</span>

      <div class="favorite-banner-text">
        <h2>Resep enak layak disimpan.</h2>
        <p>
          Ketuk ikon hati pada resep kesukaan. Temukan lagi
          dengan mudah di Favorit Saya.
        </p>
      </div>

      <RouterLink to="/favorites" class="favorite-banner-link">
        Buka Favorit Saya
      </RouterLink>
    </section>
  </div>
</template>

<style scoped>
.home-page {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 32px 32px 60px;
  box-sizing: border-box;
  color: #293b2e;
}

/* HERO */
.hero-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 360px;
  overflow: hidden;
  border-radius: 18px;
  background: #f0ece3;
}

.hero-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 48px 42px;
}

.hero-eyebrow {
  margin: 0 0 22px;
  color: #3b7051;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.3px;
}

.hero-content h1 {
  max-width: 520px;
  margin: 0;
  font-family: Georgia, serif;
  font-size: clamp(32px, 3vw, 46px);
  font-weight: 400;
  line-height: 1.15;
}

.hero-description {
  max-width: 460px;
  margin: 20px 0;
  color: #859087;
  font-size: 13px;
  line-height: 1.8;
}

.hero-search {
  display: flex;
  gap: 8px;
  width: 100%;
}

.hero-search input {
  flex: 1;
  min-width: 0;
  padding: 13px 16px;
  border: 1px solid #e1e5dd;
  border-radius: 8px;
  background: #fff;
  font: inherit;
  font-size: 12px;
}

.hero-search button {
  padding: 0 20px;
  border: 0;
  border-radius: 8px;
  background: #315e43;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.hero-visual {
  position: relative;
  min-height: 360px;
  background: #e5e8df;
}

.hero-visual > img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  inset: 0;
}

.hero-recipe {
  position: absolute;
  bottom: 20px;
  left: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: calc(100% - 40px);
  padding: 12px 16px;
  border: 0;
  border-radius: 10px;
  background: #fff;
  text-align: left;
  cursor: pointer;
}

.hero-recipe-icon {
  color: #315e43;
  font-size: 24px;
}

.hero-recipe > span:last-child {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.hero-recipe small {
  color: #879187;
  font-size: 10px;
}

.hero-recipe strong {
  color: #293b2e;
  font-family: Georgia, serif;
  font-size: 17px;
  font-weight: 400;
}

/* SECTIONS */
.home-section {
  margin-top: 48px;
}

.section-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 20px;
}

.section-heading h2 {
  margin: 0;
  font-family: Georgia, serif;
  font-size: 28px;
  font-weight: 400;
}

.section-heading p {
  margin: 7px 0 0;
  color: #879187;
  font-size: 12px;
}

.section-link {
  flex-shrink: 0;
  padding: 8px 0;
  border: 0;
  background: transparent;
  color: #315e43;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

/* KATEGORI */
.category-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 14px;
}

.category-card {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 80px;
  padding: 16px;
  border: 1px solid #e1e5dd;
  border-radius: 12px;
  background: #fff;
  text-align: left;
  cursor: pointer;
}

.category-card:hover {
  border-color: #92ae9a;
}

.category-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  border-radius: 50%;
  background: #eaf0e8;
  color: #315e43;
  font-size: 22px;
}

.category-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 0;
}

.category-info strong {
  color: #293b2e;
  font-size: 12px;
}

.category-info small {
  color: #879187;
  font-size: 11px;
}

/* RECIPE CARDS */
.recipe-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.recipe-card {
  overflow: hidden;
  border: 1px solid #e1e5dd;
  border-radius: 12px;
  background: #fff;
}

.recipe-image {
  position: relative;
  height: 185px;
  background: #eaf0e8;
}

.recipe-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  cursor: pointer;
}

.favorite-button {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 0;
  border-radius: 50%;
  background: #fff;
  color: #7c8e80;
  font-size: 24px;
  cursor: pointer;
}

.favorite-button.active {
  color: #315e43;
}

.favorite-button:disabled {
  opacity: 0.6;
  cursor: wait;
}

.recipe-info {
  padding: 15px 16px;
  cursor: pointer;
}

.recipe-category {
  display: inline-block;
  padding: 5px 9px;
  border-radius: 20px;
  background: #eaf0e8;
  color: #315e43;
  font-size: 10px;
  font-weight: 600;
}

.recipe-info h3 {
  margin: 10px 0;
  font-family: Georgia, serif;
  font-size: 19px;
  font-weight: 400;
  line-height: 1.3;
}

.recipe-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  color: #879187;
  font-size: 11px;
}

.all-recipes-action {
  display: flex;
  justify-content: center;
  margin-top: 28px;
}

.all-recipes-action button {
  padding: 12px 20px;
  border: 1px solid #e1e5dd;
  border-radius: 8px;
  background: #fff;
  color: #293b2e;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

/* FAVORIT */
.favorite-banner {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 48px;
  padding: 24px 28px;
  border-radius: 14px;
  background: #eaf0e8;
}

.favorite-banner-icon {
  color: #315e43;
  font-size: 38px;
}

.favorite-banner-text {
  flex: 1;
}

.favorite-banner-text h2 {
  margin: 0 0 5px;
  font-family: Georgia, serif;
  font-size: 22px;
  font-weight: 400;
}

.favorite-banner-text p {
  margin: 0;
  color: #879187;
  font-size: 12px;
}

.favorite-banner-link {
  padding: 12px 18px;
  border: 1px solid #e1e5dd;
  border-radius: 8px;
  background: #fff;
  color: #293b2e;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
}

.image-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: #eaf0e8;
  color: #315e43;
  font-family: Georgia, serif;
  cursor: pointer;
}

.empty-state {
  padding: 32px;
  border: 1px dashed #d5ddd4;
  border-radius: 12px;
  color: #879187;
  text-align: center;
  font-size: 13px;
}

.alert-error {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  padding: 14px 18px;
  border-radius: 8px;
  background: #fff1ef;
  color: #a33e35;
  font-size: 13px;
}

.alert-error button {
  border: 0;
  background: transparent;
  color: inherit;
  font-weight: 600;
  cursor: pointer;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .category-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .recipe-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .hero-content {
    padding: 32px;
  }
}

@media (max-width: 600px) {
  .home-page {
    padding: 20px 16px 40px;
  }

  .hero-section {
    grid-template-columns: 1fr;
  }

  .hero-content {
    padding: 32px 24px;
  }

  .hero-visual {
    min-height: 250px;
  }

  .category-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .recipe-grid {
    grid-template-columns: 1fr;
  }

  .section-heading h2 {
    font-size: 24px;
  }

  .favorite-banner {
    flex-wrap: wrap;
  }

  .favorite-banner-link {
    width: 100%;
    text-align: center;
  }
}
</style>
