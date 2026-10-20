<template>
  <main class="detail-page"><div class="container">
    <button class="back" @click="router.push('/recipes')">← &nbsp; Kembali ke Jelajahi Resep</button>
    <p v-if="loading" class="status">Memuat detail resep...</p>
    <div v-else-if="error" class="status error">{{ error }} <button @click="load">Coba lagi</button></div>
    <template v-else-if="recipe">
      <div class="hero">
        <div class="main-photo"><img v-if="recipe.image_url && !imageFailed" :src="recipe.image_url" :alt="recipe.title" @error="imageFailed=true"/><div v-else class="placeholder">🍲</div></div>
        <div class="summary">
          <span class="tag">{{ categoryName(recipe) }}</span><h1>{{ recipe.title }}</h1>
          <p class="description">{{ recipe.description || 'Nikmati resep pilihan dari dapur RecipeBox.' }}</p>
          <div class="stats"><div><span class="stat-icon">◷</span><strong>{{ recipe.cooking_time ?? '—' }} menit</strong><small>Waktu memasak</small></div><div><span class="stat-icon">♙</span><strong>{{ recipe.servings ?? '—' }} porsi</strong><small>Jumlah porsi</small></div><div><span class="stat-icon">▥</span><strong>{{ recipe.difficulty || '—' }}</strong><small>Kesulitan</small></div></div>
          <button class="favorite" :disabled="favoriteBusy" @click="toggleFavorite">{{ isFavorite ? '♡  Hapus dari Favorit' : '♡  Tambah ke Favorit' }}</button>
          <p v-if="favoriteError" class="error small">{{ favoriteError }}</p>
          <p v-else-if="isFavorite" class="subtle">Tersimpan di Favorit Saya</p>
        </div>
      </div>
      <div class="content-grid">
        <section class="ingredients"><div class="section-heading"><h2>Bahan-bahan</h2><span v-if="recipe.servings" class="tag">{{ recipe.servings }} porsi</span></div><p class="hint">Siapkan semua bahan sebelum mulai memasak.</p><ul><li v-for="(item,index) in ingredientItems" :key="index"><label><input type="checkbox" v-model="checkedIngredients" :value="index"/><span>{{ item }}</span></label></li></ul><p v-if="!ingredientItems.length" class="hint">Belum ada bahan yang dicantumkan.</p></section>
        <section class="instructions"><h2>Langkah Memasak</h2><p class="hint">Ikuti langkah berikut untuk menyiapkan hidangan.</p><ol><li v-for="(step,index) in instructionItems" :key="index"><span class="step-number">{{ index+1 }}</span><div><h3>Langkah {{ index+1 }}</h3><p>{{ step }}</p></div></li></ol><p v-if="!instructionItems.length" class="hint">Langkah memasak belum tersedia.</p></section>
      </div>
      <section v-if="relatedRecipes.length" class="related"><h2>Masakan lain untuk dicoba</h2><p class="hint">Lengkapi meja makan dengan resep dari koleksi kami.</p><div class="recipe-grid"><article v-for="item in relatedRecipes" :key="item.id" class="recipe-card"><div class="card-photo" @click="openRecipe(item.id)"><img v-if="item.image_url && !brokenRelated.has(item.id)" :src="item.image_url" :alt="item.title" @error="markBroken(item.id)"/><span v-else class="placeholder">🍲</span><button class="heart" :class="{ active: favoriteIds.has(Number(item.id)) }" :disabled="favoriteBusy" @click.stop="toggleRelated(item)">{{ favoriteIds.has(Number(item.id)) ? '♥' : '♡' }}</button></div><div class="card-body" @click="openRecipe(item.id)"><span class="tag">{{ categoryName(item) }}</span><h3>{{ item.title }}</h3><p>◷ {{ item.cooking_time ?? '—' }} menit &nbsp; · &nbsp; {{ item.difficulty || '—' }}</p></div></article></div></section>
    </template>
  </div></main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getRecipeById, getRecipes, getCategories } from '../../services/recipeService'
import { getFavorites, addFavorite, removeFavorite } from '../../services/favoriteService'
const route = useRoute()
const router = useRouter()
const recipe = ref(null)
const recipes = ref([])
const categories = ref([])
const favorites = ref([])
const loading = ref(true)
const error = ref('')
const favoriteError = ref('')
const favoriteBusy = ref(false)
const imageFailed = ref(false)
const brokenRelated = ref(new Set())
const checkedIngredients = ref([])
function unwrap(response) { return response?.data ?? response }
function arrayOf(response) { const value=unwrap(response); return Array.isArray(value) ? value : Array.isArray(value?.data) ? value.data : [] }
function parseItems(value) {
  if (Array.isArray(value)) return value.map(item => typeof item === 'string' ? item : item?.text ?? item?.name ?? item?.description ?? '').filter(Boolean)
  if (!value) return []
  if (typeof value === 'string') {
    try { const parsed=JSON.parse(value); if(Array.isArray(parsed)) return parseItems(parsed) } catch { /* plain text */ }
    return value.split(/\r?\n/).map(item => item.trim().replace(/^\s*(?:\d+[.)]|[-•])\s*/, '')).filter(Boolean)
  }
  return []
}
const ingredientItems = computed(() => parseItems(recipe.value?.ingredients))
const instructionItems = computed(() => parseItems(recipe.value?.instructions))
const favoriteIds = computed(() => new Set(favorites.value.map(item => Number(item.recipe_id ?? item.recipe?.id ?? item.id))))
const isFavorite = computed(() => recipe.value && favoriteIds.value.has(Number(recipe.value.id)))
const relatedRecipes = computed(() => {
  if (!recipe.value) return []
  return recipes.value.filter(item => Number(item.id) !== Number(recipe.value.id)).sort((a,b) => Number(b.category_id === recipe.value.category_id) - Number(a.category_id === recipe.value.category_id)).slice(0,4)
})
function categoryName(item) { return item.category?.name || categories.value.find(category => Number(category.id) === Number(item.category_id))?.name || 'Tanpa kategori' }
function markBroken(id) { brokenRelated.value = new Set([...brokenRelated.value,id]) }
function openRecipe(id) { router.push(`/recipes/${id}`) }
async function load() {
  loading.value=true; error.value=''; recipe.value=null; imageFailed.value=false; checkedIngredients.value=[]
  try {
    const [detail, allRecipes, allCategories, allFavorites] = await Promise.allSettled([getRecipeById(route.params.id), getRecipes(), getCategories(), getFavorites()])
    if(detail.status !== 'fulfilled') throw detail.reason
    recipe.value = unwrap(detail.value)
    if (!recipe.value?.id) throw new Error('Resep tidak ditemukan.')
    recipes.value = allRecipes.status === 'fulfilled' ? arrayOf(allRecipes.value) : []
    categories.value = allCategories.status === 'fulfilled' ? arrayOf(allCategories.value) : []
    favorites.value = allFavorites.status === 'fulfilled' ? arrayOf(allFavorites.value) : []
  } catch(e) { error.value=e?.response?.data?.message || e?.message || 'Gagal memuat resep.' }
  finally { loading.value=false }
}
async function changeFavorite(id) {
  if (favoriteBusy.value) return
  favoriteBusy.value=true; favoriteError.value=''
  try {
    if(favoriteIds.value.has(Number(id))) await removeFavorite(id)
    else await addFavorite(id)
    favorites.value=arrayOf(await getFavorites())
  } catch(e) { favoriteError.value=e?.response?.data?.message || 'Gagal mengubah favorit. Coba lagi.' }
  finally { favoriteBusy.value=false }
}
function toggleFavorite() { if(recipe.value) changeFavorite(recipe.value.id) }
function toggleRelated(item) { changeFavorite(item.id) }
watch(() => route.params.id, load)
onMounted(load)
</script>

<style scoped>
.detail-page{background:#f8f7f4;color:#26392e;min-height:100vh;padding:30px 0 72px}.container{max-width:1160px;padding:0 24px;margin:0 auto}.back{border:0;background:transparent;color:#346347;font-size:13px;cursor:pointer;margin-bottom:26px;padding:0}.hero{display:grid;grid-template-columns:1fr .93fr;gap:38px;align-items:start}.main-photo{height:370px;border-radius:17px;overflow:hidden;background:#e8eee6}.main-photo img,.card-photo img{width:100%;height:100%;object-fit:cover}.placeholder{display:grid;place-items:center;width:100%;height:100%;font-size:56px}.summary{padding-top:10px}.tag{display:inline-block;border-radius:30px;background:#e8f0e8;color:#356347;padding:5px 11px;font-size:11px;font-weight:700}.summary h1{font:normal 44px/1.15 Georgia,'Times New Roman',serif;margin:18px 0 14px}.description{font-size:14px;color:#829087;line-height:1.9;margin:0 0 18px}.stats{display:flex;gap:32px;border-top:1px solid #e3e6df;border-bottom:1px solid #e3e6df;padding:19px 0;margin:20px 0}.stats>div{display:flex;flex-direction:column;gap:6px;min-width:90px}.stat-icon{color:#3b7050;font-size:21px}.stats strong{font-size:14px}.stats small{color:#8d988f;font-size:11px}.favorite{background:white;border:1px solid #dfe5dd;border-radius:9px;padding:12px 18px;color:#315e43;font-weight:700;cursor:pointer}.favorite:disabled,.heart:disabled{opacity:.55}.subtle{color:#849087;font-size:11px;margin-top:13px}.content-grid{display:grid;grid-template-columns:360px 1fr;gap:34px;margin-top:30px}.ingredients{background:white;border:1px solid #e3e7e0;border-radius:15px;padding:22px}.section-heading{display:flex;justify-content:space-between;align-items:center}.content-grid h2,.related h2{font:normal 27px Georgia,'Times New Roman',serif;margin:0 0 18px}.section-heading h2{margin:0}.hint{font-size:12px;color:#8a968d;line-height:1.7;margin:15px 0 20px}.ingredients ul{list-style:none;margin:0;padding:0}.ingredients li{border-bottom:1px solid #edf0eb;padding:12px 0}.ingredients label{display:flex;gap:11px;align-items:flex-start;font-size:12px;line-height:1.6;cursor:pointer}.ingredients input{accent-color:#346347;margin-top:2px}.ingredients input:checked+span{text-decoration:line-through;color:#9ba69e}.instructions{padding:10px 0}.instructions ol{list-style:none;padding:0;margin:24px 0}.instructions li{display:flex;gap:16px;margin-bottom:23px}.step-number{flex:none;width:34px;height:34px;border-radius:50%;background:#e8f0e8;color:#356347;display:grid;place-items:center;font-weight:700;font-size:13px}.instructions h3{font-size:15px;margin:5px 0 8px}.instructions li p{font-size:13px;color:#829087;line-height:1.8;margin:0;white-space:pre-line}.related{margin-top:48px}.related h2{margin-bottom:0}.recipe-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-top:20px}.recipe-card{background:white;border:1px solid #e1e5df;border-radius:14px;overflow:hidden}.card-photo{height:150px;position:relative;cursor:pointer;background:#eaf0e9}.heart{position:absolute;top:10px;right:10px;width:32px;height:32px;border:0;border-radius:50%;background:white;color:#7d8d82;font-size:23px;cursor:pointer}.heart.active{background:#326448;color:white}.card-body{padding:15px;cursor:pointer}.card-body h3{font:normal 20px Georgia,'Times New Roman',serif;margin:10px 0}.card-body p{font-size:12px;color:#88968b;margin:0}.status{padding:60px 0;text-align:center}.error{color:#b04444}.small{font-size:12px}@media(max-width:850px){.hero{grid-template-columns:1fr}.content-grid{grid-template-columns:1fr}.recipe-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.summary h1{font-size:34px}.main-photo{height:250px}.stats{gap:12px;justify-content:space-between}.stats>div{min-width:0}.recipe-grid{grid-template-columns:1fr}.card-photo{height:200px}}
</style>
