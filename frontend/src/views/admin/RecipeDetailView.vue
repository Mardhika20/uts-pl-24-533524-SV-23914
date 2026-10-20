
<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getRecipeById } from '../../services/recipeService'

const route = useRoute()
const router = useRouter()

const recipe = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const imageFailed = ref(false)

function extractRecipe(response) {
  const value = response?.data ?? response
  return value?.data && !Array.isArray(value.data)
    ? value.data
    : value
}

function formatDate(value) {
  if (!value) return '-'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'

  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function parseIngredients(value) {
  if (Array.isArray(value)) {
    return value.map(String)
  }

  if (typeof value === 'string') {
    return value
      .split(/\r?\n/)
      .map((item) => item.trim())
      .filter(Boolean)
  }

  return []
}

function parseInstructions(value) {
  if (Array.isArray(value)) {
    return value.map(String)
  }

  if (typeof value === 'string') {
    return value
      .split(/\r?\n/)
      .map((item) =>
        item.replace(/^\s*\d+[.)]\s*/, '').trim(),
      )
      .filter(Boolean)
  }

  return []
}

const ingredients = ref([])
const instructions = ref([])

async function loadRecipe() {
  loading.value = true
  errorMessage.value = ''
  imageFailed.value = false

  try {
    const response = await getRecipeById(route.params.id)
    const data = extractRecipe(response)

    if (!data?.id) {
      throw new Error('Data resep tidak ditemukan.')
    }

    recipe.value = data
    ingredients.value = parseIngredients(data.ingredients)
    instructions.value = parseInstructions(data.instructions)
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      error.message ||
      'Gagal mengambil detail resep.'
  } finally {
    loading.value = false
  }
}

function goBack() {
  router.push('/admin/recipes')
}

function goEdit() {
  router.push(`/admin/recipes/${route.params.id}/edit`)
}

onMounted(loadRecipe)
</script>

<template>
  <main class="detail-page">
    <div class="page-heading">
      <div>
        <h1>Detail Resep</h1>
        <p>Informasi lengkap resep dalam koleksi RecipeBox.</p>
      </div>

      <button type="button" class="back-button" @click="goBack">
        ← Kelola Resep
      </button>
    </div>

    <div v-if="loading" class="state-box">
      Memuat detail resep...
    </div>

    <div v-else-if="errorMessage" class="state-box error">
      {{ errorMessage }}
    </div>

    <template v-else-if="recipe">
      <section class="recipe-card">
        <div class="recipe-overview">
          <div class="image-container">
            <img
              v-if="recipe.image_url && !imageFailed"
              :src="recipe.image_url"
              :alt="recipe.title"
              @error="imageFailed = true"
            />

            <div v-else class="image-placeholder">
              Foto resep tidak tersedia
            </div>
          </div>

          <div class="recipe-information">
            <span class="category-badge">
              {{ recipe.category?.name || 'Tanpa Kategori' }}
            </span>

            <h2>{{ recipe.title }}</h2>

            <p class="description">
              {{ recipe.description }}
            </p>

            <div class="recipe-stats">
              <div>
                <span>Waktu Memasak</span>
                <strong>{{ recipe.cooking_time ?? '-' }} menit</strong>
              </div>

              <div>
                <span>Tingkat Kesulitan</span>
                <strong>{{ recipe.difficulty || '-' }}</strong>
              </div>

              <div>
                <span>Jumlah Porsi</span>
                <strong>{{ recipe.servings ?? '-' }} porsi</strong>
              </div>
            </div>

            <p class="created-date">
              Dibuat pada {{ formatDate(recipe.created_at) }}
            </p>

            <button type="button" class="edit-button" @click="goEdit">
              ✎ Edit Resep
            </button>
          </div>
        </div>
      </section>

      <div class="content-grid">
        <section class="content-card">
          <div class="section-heading">
            <span class="section-number">01</span>
            <h3>Bahan-bahan</h3>
          </div>

          <ol v-if="ingredients.length">
            <li
              v-for="(ingredient, index) in ingredients"
              :key="index"
            >
              {{ ingredient }}
            </li>
          </ol>

          <p v-else class="empty-text">
            Belum ada bahan yang tersedia.
          </p>
        </section>

        <section class="content-card">
          <div class="section-heading">
            <span class="section-number">02</span>
            <h3>Langkah Memasak</h3>
          </div>

          <ol v-if="instructions.length">
            <li
              v-for="(instruction, index) in instructions"
              :key="index"
            >
              {{ instruction }}
            </li>
          </ol>

          <p v-else class="empty-text">
            Belum ada langkah memasak.
          </p>
        </section>
      </div>
    </template>

    <footer class="page-footer">
      © {{ new Date().getFullYear() }} RecipeBox · Panel Admin
    </footer>
  </main>
</template>

<style scoped>
.detail-page {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  min-height: calc(100vh - 80px);
  padding: 36px 40px;
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
  margin: 0 0 8px;
  font-size: 30px;
}

.page-heading p {
  margin: 0;
  color: #7b867e;
  font-size: 14px;
}

.back-button {
  padding: 12px 16px;
  background: white;
  color: #315e43;
  border: 1px solid #dfe5dc;
  border-radius: 9px;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
}

.recipe-card,
.content-card {
  background: #fff;
  border: 1px solid #e1e5dd;
  border-radius: 15px;
}

.recipe-card {
  padding: 24px;
}

.recipe-overview {
  display: grid;
  grid-template-columns: minmax(260px, 1fr) minmax(0, 1.3fr);
  gap: 30px;
  align-items: start;
}

.image-container {
  width: 100%;
  aspect-ratio: 4 / 3;
  border-radius: 12px;
  overflow: hidden;
  background: #eaf0e8;
}

.image-container img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #315e43;
  font-size: 13px;
}

.category-badge {
  display: inline-block;
  padding: 7px 12px;
  background: #eaf0e8;
  color: #315e43;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.recipe-information h2 {
  font-size: 29px;
  margin: 16px 0;
}

.description {
  color: #7b867e;
  font-size: 14px;
  line-height: 1.8;
}

.recipe-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
  margin-top: 28px;
}

.recipe-stats div {
  background: #f8f7f3;
  border-radius: 10px;
  padding: 16px;
}

.recipe-stats span {
  display: block;
  font-size: 11px;
  color: #7b867e;
  margin-bottom: 8px;
}

.recipe-stats strong {
  font-size: 13px;
}

.created-date {
  color: #89958b;
  font-size: 12px;
  margin-top: 22px;
}

.edit-button {
  margin-top: 12px;
  padding: 12px 18px;
  background: #315e43;
  color: white;
  border: 0;
  border-radius: 9px;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
  margin-top: 24px;
}

.content-card {
  min-width: 0;
  padding: 24px;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
}

.section-heading h3 {
  font-size: 17px;
  margin: 0;
}

.section-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: #eaf0e8;
  color: #315e43;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 700;
}

.content-card ol {
  padding-left: 24px;
  margin: 0;
}

.content-card li {
  padding-left: 8px;
  margin-bottom: 14px;
  font-size: 13px;
  line-height: 1.8;
}

.empty-text {
  color: #89958b;
  font-size: 13px;
}

.state-box {
  padding: 24px;
  background: white;
  border-radius: 12px;
  color: #7b867e;
}

.error {
  color: #b84236;
}

.page-footer {
  margin-top: 28px;
  color: #89958b;
  font-size: 12px;
}

@media (max-width: 850px) {
  .recipe-overview,
  .content-grid {
    grid-template-columns: 1fr;
  }

  .detail-page {
    padding: 24px;
  }
}

@media (max-width: 550px) {
  .page-heading {
    flex-direction: column;
    align-items: flex-start;
  }

  .recipe-stats {
    grid-template-columns: 1fr;
  }
}
</style>
