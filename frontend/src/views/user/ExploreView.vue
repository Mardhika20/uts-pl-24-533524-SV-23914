<template>
  <div class="explore-page">
    <main class="container">
      <div class="intro">
        <p class="eyebrow">DARI DAPUR INDONESIA</p>
        <h1>Jelajahi Resep</h1>
        <p>Temukan masakan yang pas untuk hari ini. Cari berdasarkan nama, kategori, atau tingkat kesulitannya.</p>
      </div>

      <section class="filters" aria-label="Pencarian dan filter resep">
        <label class="field search-field">
          <span>Nama masakan</span>
          <div class="search-input"><span aria-hidden="true">⌕</span><input v-model="search" type="search" placeholder="Cari nama masakan..." /></div>
        </label>
        <label class="field">
          <span>Kategori</span>
          <select v-model="selectedCategory"><option value="">Semua kategori</option><option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.name }}</option></select>
        </label>
        <label class="field">
          <span>Tingkat kesulitan</span>
          <select v-model="selectedDifficulty"><option value="">Semua tingkat</option><option value="Mudah">Mudah</option><option value="Sedang">Sedang</option><option value="Sulit">Sulit</option></select>
        </label>
      </section>

      <div class="results-heading"><strong>{{ filteredRecipes.length }} resep untuk dijelajahi</strong><span>↧ &nbsp; Terbaru ditambahkan</span></div>
      <p v-if="error" class="notice error">{{ error }} <button @click="loadData">Coba lagi</button></p>
      <p v-if="loading" class="notice">Memuat resep...</p>
      <template v-else>
        <div v-if="paginatedRecipes.length" class="recipe-grid">
          <article v-for="recipe in paginatedRecipes" :key="recipe.id" class="recipe-card">
            <div class="photo" @click="openRecipe(recipe.id)">
              <img v-if="recipe.image_url" :src="recipe.image_url" :alt="recipe.title" @error="onImageError" />
              <div v-else class="photo-placeholder">🍲</div>
              <button class="favorite-button" :class="{ active: favoriteIds.has(Number(recipe.id)) }" :aria-label="favoriteIds.has(Number(recipe.id)) ? 'Hapus dari favorit' : 'Tambah ke favorit'" :disabled="busyIds.has(Number(recipe.id))" @click.stop="toggleFavorite(recipe)">{{ favoriteIds.has(Number(recipe.id)) ? '♥' : '♡' }}</button>
            </div>
            <div class="card-body" @click="openRecipe(recipe.id)">
              <span class="category-tag">{{ recipe.category?.name || categoryName(recipe.category_id) }}</span>
              <h2>{{ recipe.title }}</h2>
              <div class="meta"><span>◷ &nbsp;{{ recipe.cooking_time ?? '—' }} menit</span><span>·</span><span>▥ &nbsp;{{ recipe.difficulty || '—' }}</span></div>
            </div>
          </article>
        </div>
        <div v-else-if="!error" class="empty-state"><h2>Resep tidak ditemukan</h2><p>Coba kata kunci atau filter yang berbeda.</p><button @click="resetFilters">Reset filter</button></div>
        <div v-if="filteredRecipes.length" class="pagination"><span>Menampilkan {{ pageStart }}–{{ pageEnd }} dari {{ filteredRecipes.length }} resep</span><div class="page-buttons"><button :disabled="currentPage === 1" @click="currentPage--">← Sebelumnya</button><button v-for="page in totalPages" :key="page" :class="{ selected: page === currentPage }" @click="currentPage = page">{{ page }}</button><button :disabled="currentPage === totalPages" @click="currentPage++">Berikutnya →</button></div></div>
      </template>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCategories, getRecipes } from '../../services/recipeService'
import { getFavorites, addFavorite, removeFavorite } from '../../services/favoriteService'

const router = useRouter()
const route = useRoute()
const recipes = ref([])
const categories = ref([])
const favorites = ref([])
const loading = ref(true)
const error = ref('')
const search = ref(String(route.query.search || ''))
const selectedCategory = ref(String(route.query.category || ''))
const selectedDifficulty = ref(String(route.query.difficulty || ''))
const currentPage = ref(1)
const busyIds = ref(new Set())
const pageSize = 8

function extractArray(response) {
  const data = response?.data ?? response
  if (Array.isArray(data)) return data
  if (Array.isArray(data?.data)) return data.data
  return []
}
const favoriteIds = computed(() => new Set(favorites.value.map(item => Number(item.recipe_id ?? item.recipe?.id ?? item.id))))
const filteredRecipes = computed(() => {
  const keyword = search.value.trim().toLocaleLowerCase('id')
  return recipes.value.filter(recipe => {
    const matchesSearch = !keyword || String(recipe.title || '').toLocaleLowerCase('id').includes(keyword)
    const matchesCategory = !selectedCategory.value || String(recipe.category_id ?? recipe.category?.id) === selectedCategory.value
    const matchesDifficulty = !selectedDifficulty.value || String(recipe.difficulty || '').toLowerCase() === selectedDifficulty.value.toLowerCase()
    return matchesSearch && matchesCategory && matchesDifficulty
  }).sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
})
const totalPages = computed(() => Math.max(1, Math.ceil(filteredRecipes.value.length / pageSize)))
const paginatedRecipes = computed(() => filteredRecipes.value.slice((currentPage.value - 1) * pageSize, currentPage.value * pageSize))
const pageStart = computed(() => (currentPage.value - 1) * pageSize + 1)
const pageEnd = computed(() => Math.min(currentPage.value * pageSize, filteredRecipes.value.length))

watch([search, selectedCategory, selectedDifficulty], () => { currentPage.value = 1 })
watch(() => route.query, query => {
  search.value = String(query.search || '')
  selectedCategory.value = String(query.category || '')
  selectedDifficulty.value = String(query.difficulty || '')
})
function categoryName(id) { return categories.value.find(category => String(category.id) === String(id))?.name || 'Tanpa kategori' }
function resetFilters() { search.value = ''; selectedCategory.value = ''; selectedDifficulty.value = ''; currentPage.value = 1 }
function openRecipe(id) { router.push(`/recipes/${id}`) }
function onImageError(event) { event.target.style.display = 'none'; event.target.parentElement.classList.add('image-failed') }

async function loadData() {
  loading.value = true
  error.value = ''
  try {
    const [recipeResponse, categoryResponse, favoriteResponse] = await Promise.allSettled([getRecipes(), getCategories(), getFavorites()])
    if (recipeResponse.status !== 'fulfilled' || categoryResponse.status !== 'fulfilled') throw new Error('Gagal mengambil data resep atau kategori.')
    recipes.value = extractArray(recipeResponse.value)
    categories.value = extractArray(categoryResponse.value)
    favorites.value = favoriteResponse.status === 'fulfilled' ? extractArray(favoriteResponse.value) : []
  } catch (err) { error.value = err?.response?.data?.message || err.message || 'Gagal memuat resep.' }
  finally { loading.value = false }
}
async function toggleFavorite(recipe) {
  const id = Number(recipe.id)
  if (busyIds.value.has(id)) return
  busyIds.value = new Set([...busyIds.value, id])
  try {
    if (favoriteIds.value.has(id)) await removeFavorite(id)
    else await addFavorite(id)
    favorites.value = extractArray(await getFavorites())
  } catch (err) { window.alert(err?.response?.data?.message || 'Gagal memperbarui favorit. Silakan coba lagi.') }
  finally { const next = new Set(busyIds.value); next.delete(id); busyIds.value = next }
}
onMounted(loadData)
</script>

<style scoped>
.explore-page{background:#f8f7f4;min-height:calc(100vh - 80px);padding:44px 0 64px;color:#26392d}.container{max-width:1160px;margin:0 auto;padding:0 24px}.intro{margin-bottom:30px}.eyebrow{font-size:11px;font-weight:800;color:#315f44;letter-spacing:.03em;margin:0 0 12px}.intro h1{font-family:Georgia,'Times New Roman',serif;font-size:43px;font-weight:400;margin:0 0 14px}.intro>p:last-child{color:#7b8780;font-size:14px;margin:0}.filters{background:white;border:1px solid #e3e7e1;border-radius:14px;padding:20px;display:grid;grid-template-columns:minmax(0,1fr) 185px 185px;gap:14px;margin-bottom:26px}.field{display:flex;flex-direction:column;gap:12px;font-size:12px;font-weight:700;color:#303d33}.field select,.search-input{height:43px;border:1px solid #dfe5dd;border-radius:9px;background:white;color:#344438;font:inherit;font-weight:400;width:100%}.field select{padding:0 12px}.search-input{display:flex;align-items:center;gap:9px;padding:0 13px}.search-input span{font-size:24px;color:#87948b}.search-input input{width:100%;border:0;outline:0;background:transparent;font:inherit;font-size:13px}.results-heading{display:flex;justify-content:space-between;align-items:center;margin:0 0 25px;font-size:13px}.results-heading>span{color:#849188}.recipe-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px}.recipe-card{overflow:hidden;background:white;border:1px solid #e1e5df;border-radius:15px}.photo{height:160px;position:relative;background:#edf1ea;cursor:pointer}.photo img{width:100%;height:100%;object-fit:cover}.photo-placeholder{height:100%;display:grid;place-items:center;font-size:40px}.photo.image-failed:after{content:'🍲';position:absolute;left:45%;top:35%;font-size:35px}.favorite-button{position:absolute;top:12px;right:12px;width:32px;height:32px;border:0;border-radius:50%;background:white;color:#7d8e82;font-size:23px;cursor:pointer;display:grid;place-items:center}.favorite-button.active{background:#326448;color:white}.favorite-button:disabled{opacity:.6;cursor:wait}.card-body{padding:17px 16px;cursor:pointer}.category-tag{display:inline-block;background:#eaf2ea;color:#346147;border-radius:20px;padding:5px 10px;font-size:10px;font-weight:700}.card-body h2{font-family:Georgia,'Times New Roman',serif;font-weight:400;font-size:20px;margin:11px 0 10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.meta{display:flex;gap:9px;color:#85938a;font-size:12px}.pagination{display:flex;justify-content:space-between;align-items:center;margin-top:40px;font-size:12px;color:#819087}.page-buttons{display:flex;align-items:center;gap:6px}.page-buttons button{border:0;background:transparent;padding:10px 12px;border-radius:8px;color:#426c51;cursor:pointer}.page-buttons button.selected{background:#315f44;color:white}.page-buttons button:disabled{opacity:.35;cursor:not-allowed}.notice,.empty-state{padding:28px;text-align:center}.error{color:#b13b3b}.notice button,.empty-state button{margin-left:10px;border:1px solid #315f44;border-radius:8px;padding:9px 13px;background:white;color:#315f44;cursor:pointer}.empty-state{background:white;border:1px solid #e1e5df;border-radius:14px}.empty-state h2{font-family:Georgia,serif;font-weight:400}@media(max-width:850px){.filters{grid-template-columns:1fr 1fr}.search-field{grid-column:1/-1}.recipe-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:540px){.intro h1{font-size:35px}.filters{grid-template-columns:1fr}.search-field{grid-column:auto}.recipe-grid{grid-template-columns:1fr}.photo{height:210px}.pagination{flex-direction:column;gap:14px;align-items:flex-start}.results-heading{gap:12px}}
</style>
