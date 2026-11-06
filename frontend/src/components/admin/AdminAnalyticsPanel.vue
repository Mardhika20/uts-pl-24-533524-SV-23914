
<template>
  <section class="analytics" aria-labelledby="analytics-title">

    <!-- Judul Analytics -->
    <div class="analytics-heading">
      <div>
        <p class="eyebrow">
          STATISTIK PENGGUNA & INTERAKSI
        </p>

        <h2 id="analytics-title">
          Analytics RecipeBox
        </h2>

        <p>
          Ringkasan aktivitas dari database RecipeBox.
        </p>
      </div>

      <button
        type="button"
        class="refresh"
        :disabled="loading"
        @click="loadAnalytics"
      >
        {{ loading ? 'Memuat...' : '↻ Perbarui' }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="state">
      Memuat statistik...
    </div>

    <!-- Error -->
    <div v-else-if="error" class="state error">
      {{ error }}

      <button type="button" @click="loadAnalytics">
        Coba Lagi
      </button>
    </div>

    <template v-else-if="analytics">

      <!-- Kartu Statistik -->
      <div class="metric-grid">

        <article class="metric-card">
          <div class="metric-top">
            <span>Total Pengguna</span>
            <span>♙</span>
          </div>

          <strong>
            {{ formatNumber(analytics.summary.total_users) }}
          </strong>

          <p>Seluruh akun termasuk admin</p>
        </article>

        <article class="metric-card">
          <div class="metric-top">
            <span>Total Favorit</span>
            <span>♥</span>
          </div>

          <strong>
            {{ formatNumber(analytics.summary.total_favorites) }}
          </strong>

          <p>Resep disimpan pengguna</p>
        </article>

        <article class="metric-card">
          <div class="metric-top">
            <span>Total Review</span>
            <span>✎</span>
          </div>

          <strong>
            {{ formatNumber(analytics.summary.total_reviews) }}
          </strong>

          <p>Rating dan ulasan terkirim</p>
        </article>

        <article class="metric-card">
          <div class="metric-top">
            <span>Rata-rata Rating</span>
            <span>★</span>
          </div>

          <strong>
            {{ formatRating(analytics.summary.average_rating) }}
            <small>/ 5</small>
          </strong>

          <p>Dari seluruh rating pengguna</p>
        </article>

      </div>

      <!-- Grafik dan Resep Populer -->
      <div class="analytics-grid">

        <!-- Resep Populer -->
        <div class="insight-card">
          <div class="insight-heading">
            <h3>5 Resep Paling Populer</h3>
            <span>Berdasarkan jumlah favorit</span>
          </div>

          <p
            v-if="!analytics.top_recipes.length"
            class="empty"
          >
            Belum ada resep.
          </p>

          <ol v-else class="top-list">
            <li
              v-for="(recipe, index) in analytics.top_recipes"
              :key="recipe.id"
            >
              <span class="rank">
                {{ index + 1 }}
              </span>

              <div class="top-info">
                <RouterLink
                  :to="`/admin/recipes/${recipe.id}`"
                >
                  {{ recipe.title }}
                </RouterLink>

                <p>
                  ♥ {{ formatNumber(recipe.favorites_count) }}
                  favorit ·
                  {{ formatNumber(recipe.reviews_count) }}
                  review
                </p>
              </div>

              <span class="rating">
                ★ {{ formatRating(recipe.average_rating) }}
              </span>
            </li>
          </ol>
        </div>

        <!-- Distribusi Kategori -->
        <div class="insight-card">
          <div class="insight-heading">
            <h3>Resep per Kategori</h3>
            <span>Komposisi resep</span>
          </div>

          <p
            v-if="!analytics.categories.length"
            class="empty"
          >
            Belum ada kategori.
          </p>

          <div
            v-for="category in analytics.categories"
            :key="category.id"
            class="bar-item"
          >
            <div class="bar-label">
              <span>{{ category.name }}</span>

              <strong>
                {{ category.recipe_count }} resep
              </strong>
            </div>

            <div class="bar-track">
              <div
                class="bar-fill"
                :style="{
                  width: `${categoryPercent(category.recipe_count)}%`
                }"
              ></div>
            </div>
          </div>
        </div>

      </div>

      <!-- Distribusi Rating -->
      <div class="insight-card rating-card">
        <div class="insight-heading">
          <h3>Distribusi Rating</h3>
          <span>Jumlah ulasan berdasarkan bintang</span>
        </div>

        <p
          v-if="!analytics.summary.total_reviews"
          class="empty"
        >
          Belum ada review untuk dianalisis.
        </p>

        <div
          v-for="item in analytics.rating_distribution"
          :key="item.rating"
          class="rating-row"
        >
          <span>{{ item.rating }} ★</span>

          <div class="bar-track">
            <div
              class="bar-fill"
              :style="{
                width: `${ratingPercent(item.total)}%`
              }"
            ></div>
          </div>

          <strong>{{ item.total }}</strong>
        </div>
      </div>

    </template>

  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { getAdminAnalytics } from '../../services/adminAnalyticsService'

// Data analytics
const analytics = ref(null)
const loading = ref(true)
const error = ref('')

// Format angka Indonesia
const numberFormatter = new Intl.NumberFormat('id-ID')

function formatNumber(value) {
  return numberFormatter.format(Number(value) || 0)
}

// Format rating satu angka desimal
function formatRating(value) {
  return (Number(value) || 0).toFixed(1)
}

// Persentase resep berdasarkan kategori
function categoryPercent(count) {
  const total = Number(
    analytics.value?.summary?.total_recipes
  ) || 0

  return total
    ? Math.round(Number(count) / total * 100)
    : 0
}

// Persentase rating bintang
function ratingPercent(count) {
  const total = Number(
    analytics.value?.summary?.total_reviews
  ) || 0

  return total
    ? Math.round(Number(count) / total * 100)
    : 0
}

// Mengambil analytics dari Laravel
async function loadAnalytics() {
  loading.value = true
  error.value = ''

  try {
    analytics.value = await getAdminAnalytics()
  } catch (err) {
    analytics.value = null

    error.value =
      err?.response?.data?.message ||
      'Gagal memuat analytics. Periksa backend.'
  } finally {
    loading.value = false
  }
}

// Jalankan ketika komponen ditampilkan
onMounted(loadAnalytics)
</script>

<style scoped>
.analytics {
  margin: 30px 0 38px;
  color: #27332b;
}

.analytics-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 11px;
  color: #315f44;
  font-weight: 800;
}

.analytics-heading h2 {
  font: normal 27px Georgia, serif;
  margin: 0 0 7px;
}

.analytics-heading p:last-child {
  font-size: 13px;
  color: #839087;
  margin: 0;
}

.refresh,
.state button {
  border: 1px solid #cfdcd2;
  background: white;
  color: #315f44;
  border-radius: 9px;
  padding: 10px 14px;
  font-weight: 700;
  cursor: pointer;
}

.refresh:disabled {
  opacity: 0.5;
}

.state {
  background: white;
  border: 1px solid #e3e7e1;
  border-radius: 12px;
  padding: 22px;
  font-size: 13px;
}

.error {
  color: #b13b3b;
}

.metric-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.metric-card,
.insight-card {
  background: white;
  border: 1px solid #e3e7e1;
  border-radius: 14px;
  padding: 20px;
}

.metric-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  color: #78887c;
  font-size: 12px;
}

.metric-top span:last-child {
  color: #315f44;
  font-size: 20px;
}

.metric-card > strong {
  display: block;
  font-size: 31px;
  color: #273e30;
  margin-top: 12px;
}

.metric-card > strong small {
  font-size: 13px;
  color: #859188;
}

.metric-card > p {
  font-size: 11px;
  color: #849187;
  margin: 8px 0 0;
}

.analytics-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin-top: 18px;
}

.insight-heading {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 12px;
  margin-bottom: 18px;
}

.insight-heading h3 {
  font: normal 21px Georgia, serif;
  margin: 0;
}

.insight-heading > span {
  font-size: 11px;
  color: #8a958c;
  text-align: right;
}

.empty {
  font-size: 13px;
  color: #89968c;
}

.top-list {
  padding: 0;
  margin: 0;
  list-style: none;
}

.top-list li {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-top: 1px solid #edf0ec;
}

.rank {
  flex: none;
  background: #e8f0e8;
  color: #315f44;
  border-radius: 8px;
  width: 28px;
  height: 28px;
  display: grid;
  place-items: center;
  font-weight: 800;
}

.top-info {
  flex: 1;
  min-width: 0;
}

.top-info a {
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  color: #304436;
}

.top-info a:hover {
  text-decoration: underline;
}

.top-info p {
  color: #86948a;
  font-size: 11px;
  margin: 5px 0 0;
}

.rating {
  color: #b18a32;
  font-size: 12px;
  font-weight: 800;
}

.bar-item {
  margin: 14px 0;
}

.bar-label {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  font-size: 12px;
  margin-bottom: 7px;
}

.bar-label strong {
  color: #315f44;
}

.bar-track {
  background: #e8eee8;
  border-radius: 10px;
  height: 9px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: #38694c;
  border-radius: 10px;
  transition: width 0.2s ease;
}

.rating-card {
  margin-top: 18px;
}

.rating-row {
  display: grid;
  grid-template-columns: 34px 1fr 25px;
  align-items: center;
  gap: 12px;
  margin: 11px 0;
  font-size: 12px;
  color: #728174;
}

.rating-row strong {
  text-align: right;
  color: #315f44;
}

@media (max-width: 1100px) {
  .metric-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .analytics-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 520px) {
  .metric-grid {
    grid-template-columns: 1fr;
  }

  .analytics-heading {
    flex-direction: column;
    align-items: flex-start;
  }

  .insight-heading {
    flex-direction: column;
  }
}
</style>
