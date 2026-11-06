<template>
  <section class="finder" aria-labelledby="finder-title">
    <div class="finder-heading">
      <div>
        <p class="eyebrow">MASAK DARI BAHAN YANG ADA</p>
        <h2 id="finder-title">Cari Resep Berdasarkan Bahan</h2>
        <p>Masukkan bahan yang kamu punya, pisahkan dengan koma.</p>
      </div>
    </div>

    <form class="finder-form" @submit.prevent="searchRecipes">
      <label for="finder-ingredients">Bahan yang tersedia</label>
      <div class="finder-row">
        <input
          id="finder-ingredients"
          v-model="input"
          type="text"
          placeholder="Contoh: nasi, telur, kecap"
          :disabled="loading"
        />
        <button type="submit" :disabled="loading">
          {{ loading ? 'Mencari...' : 'Cari Resep' }}
        </button>
      </div>
      <p v-if="error" role="alert" class="error">{{ error }}</p>
    </form>

    <div v-if="results !== null" class="finder-results">
      <div class="results-heading">
        <h3>Hasil Smart Recipe Finder</h3>
        <button type="button" class="clear" @click="clearResults">Tutup hasil</button>
      </div>

      <p v-if="results.length === 0" class="empty">
        Belum ditemukan resep yang cocok. Coba masukkan bahan lainnya.
      </p>

      <div v-else class="finder-grid">
        <article v-for="item in results" :key="item.recipe.id" class="finder-card">
          <div class="photo">
            <img
              v-if="item.recipe.image_url && !failedImages.has(item.recipe.id)"
              :src="item.recipe.image_url"
              :alt="item.recipe.title"
              @error="markImageFailed(item.recipe.id)"
            />
            <span v-else class="placeholder">🍲</span>
          </div>
          <div class="card-body">
            <div class="card-head">
              <h4>{{ item.recipe.title }}</h4>
              <strong>{{ item.match_percentage }}% cocok</strong>
            </div>
            <div
              class="progress-track"
              role="progressbar"
              :aria-valuenow="item.match_percentage"
              aria-valuemin="0"
              aria-valuemax="100"
              :aria-label="`Kecocokan ${item.recipe.title}`"
            >
              <div class="progress-fill" :style="{ width: `${item.match_percentage}%` }"></div>
            </div>
            <p class="matched">{{ item.matched_count }} dari {{ item.total_ingredients }} bahan cocok</p>
            <p class="missing">
              <strong>Belum tersedia:</strong>
              {{ item.missing_ingredients.length ? item.missing_ingredients.join(', ') : 'Semua bahan tersedia' }}
            </p>
            <button class="detail-link" type="button" @click="openRecipe(item.recipe.id)">
              Lihat Resep →
            </button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { findRecipesByIngredients } from '../../services/finderService'

const router = useRouter()
const input = ref('')
const loading = ref(false)
const error = ref('')
const results = ref(null)
const failedImages = ref(new Set())

function markImageFailed(id) {
  failedImages.value = new Set([...failedImages.value, id])
}

function clearResults() {
  results.value = null
  error.value = ''
}

function openRecipe(id) {
  router.push(`/recipes/${id}`)
}

async function searchRecipes() {
  error.value = ''

  const ingredients = [...new Set(
    input.value.split(/[,\n]+/)
      .map(value => value.trim())
      .filter(Boolean)
  )]

  if (ingredients.length === 0) {
    error.value = 'Masukkan minimal satu bahan terlebih dahulu.'
    return
  }
  if (ingredients.length > 15) {
    error.value = 'Maksimal 15 jenis bahan dalam satu pencarian.'
    return
  }
  if (ingredients.some(value => value.length < 2 || value.length > 60)) {
    error.value = 'Setiap nama bahan harus berisi 2–60 karakter.'
    return
  }

  loading.value = true
  results.value = null

  try {
    const response = await findRecipesByIngredients(ingredients)
    results.value = Array.isArray(response.data) ? response.data : []
  } catch (err) {
    error.value = err?.response?.data?.message || 'Pencarian gagal. Coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.finder{background:#fff;border:1px solid #e3e7e1;border-radius:14px;padding:22px;margin:-6px 0 28px;color:#26392d}
.eyebrow{font-size:11px;font-weight:800;color:#315f44;letter-spacing:.03em;margin:0 0 10px}
.finder-heading h2{font:normal 25px Georgia,'Times New Roman',serif;margin:0 0 9px}
.finder-heading p:last-child{color:#7b8780;font-size:13px;margin:0 0 20px}
.finder-form label{display:block;color:#303d33;font-size:12px;font-weight:700;margin-bottom:9px}
.finder-row{display:flex;gap:10px}
.finder-row input{flex:1;min-width:0;height:43px;box-sizing:border-box;border:1px solid #dfe5dd;border-radius:9px;padding:0 14px;font:inherit;font-size:13px}
.finder-row button,.detail-link{border:0;border-radius:9px;background:#315f44;color:white;padding:0 18px;font-size:12px;font-weight:700;cursor:pointer}
.finder-row button:disabled{opacity:.65;cursor:wait}
.error{font-size:12px;color:#b13b3b;margin:10px 0 0}
.finder-results{margin-top:26px;border-top:1px solid #e3e7e1;padding-top:20px}
.results-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}
.results-heading h3{font:normal 20px Georgia,'Times New Roman',serif;margin:0}
.clear{border:0;background:transparent;color:#315f44;font-size:12px;cursor:pointer}
.empty{font-size:13px;color:#849188}
.finder-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.finder-card{border:1px solid #e3e7e1;border-radius:12px;overflow:hidden;display:flex;background:#fff}
.photo{width:130px;flex:none;height:160px;background:#eaf0e9}
.photo img{width:100%;height:100%;object-fit:cover}
.placeholder{height:100%;display:grid;place-items:center;font-size:36px}
.card-body{min-width:0;flex:1;padding:14px}
.card-head{display:flex;justify-content:space-between;align-items:start;gap:8px}
.card-head h4{font:normal 18px Georgia,'Times New Roman',serif;margin:0 0 12px}
.card-head strong{white-space:nowrap;color:#315f44;font-size:12px}
.progress-track{height:7px;background:#e9efea;border-radius:10px;overflow:hidden}
.progress-fill{height:100%;background:#315f44;border-radius:10px}
.matched,.missing{font-size:11px;color:#7d8c82;line-height:1.5;margin:9px 0}
.missing{overflow-wrap:anywhere}
.detail-link{padding:9px 12px;margin-top:4px}
@media(max-width:850px){.finder-grid{grid-template-columns:1fr}}
@media(max-width:540px){.finder{padding:16px}.finder-row{flex-direction:column}.finder-row button{height:42px}.finder-card{display:block}.photo{width:100%;height:155px;min-height:0}}
</style>
