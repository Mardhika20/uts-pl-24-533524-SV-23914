
<template>
  <section class="reviews">
    <div class="review-heading">
      <div>
        <h2>Rating & Ulasan</h2>
        <p class="hint">
          Bagikan pengalamanmu mencoba resep ini.
        </p>
      </div>

      <div class="rating-summary">
        <strong>★ {{ Number(summary.average).toFixed(1) }}</strong>
        <small>{{ summary.count }} ulasan</small>
      </div>
    </div>

    <p v-if="loading" class="hint">Memuat ulasan...</p>

    <template v-else>
      <form class="review-form" @submit.prevent="submitReview">
        <h3>{{ myReview ? 'Edit ulasanmu' : 'Berikan penilaianmu' }}</h3>

        <label>Pilih rating</label>

        <div class="rating-stars">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            :class="{ selected: star <= form.rating }"
            :aria-label="`${star} bintang`"
            :aria-pressed="form.rating === star"
            @click="form.rating = star"
          >
            ★
          </button>
        </div>

        <label for="review-comment">Komentar (opsional)</label>

        <textarea
          id="review-comment"
          v-model="form.comment"
          rows="4"
          maxlength="2000"
          placeholder="Bagaimana pengalamanmu memasak resep ini?"
        ></textarea>

        <div class="actions">
          <button
            class="submit-btn"
            type="submit"
            :disabled="saving || form.rating === 0"
          >
            {{ saving ? 'Menyimpan...' : 'Simpan Ulasan' }}
          </button>

          <button
            v-if="myReview"
            class="delete-btn"
            type="button"
            :disabled="saving"
            @click="removeReview"
          >
            Hapus Ulasan
          </button>
        </div>

        <p v-if="error" class="error">{{ error }}</p>
        <p v-if="message" class="success">{{ message }}</p>
      </form>

      <div class="review-list">
        <h3>Ulasan Pengguna</h3>

        <p v-if="reviews.length === 0" class="hint">
          Belum ada ulasan. Jadilah yang pertama!
        </p>

        <article
          v-for="review in reviews"
          :key="review.id"
          class="review-item"
        >
          <div class="review-top">
            <strong>{{ review.user?.name || 'Pengguna' }}</strong>

            <span class="review-stars">
              {{ '★'.repeat(review.rating) }}
              {{ '☆'.repeat(5 - review.rating) }}
            </span>
          </div>

          <small>{{ formatDate(review.created_at) }}</small>

          <p v-if="review.comment">{{ review.comment }}</p>
        </article>
      </div>
    </template>
  </section>
</template>

<script setup>
import { ref, watch } from 'vue'
import {
  getRecipeReviews,
  saveRecipeReview,
  deleteRecipeReview
} from '../../services/reviewService'

const props = defineProps({
  recipeId: {
    type: [Number, String],
    required: true
  }
})

const reviews = ref([])
const summary = ref({ average: 0, count: 0 })
const myReview = ref(null)
const form = ref({ rating: 0, comment: '' })
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const message = ref('')

function formatDate(value) {
  return new Date(value).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

async function loadReviews() {
  loading.value = true
  error.value = ''

  try {
    const result = await getRecipeReviews(props.recipeId)

    reviews.value = result.data ?? []
    summary.value = result.summary ?? { average: 0, count: 0 }
    myReview.value = result.my_review ?? null

    form.value = {
      rating: myReview.value?.rating ?? 0,
      comment: myReview.value?.comment ?? ''
    }
  } catch (e) {
    error.value =
      e.response?.data?.message || 'Gagal memuat ulasan.'
  } finally {
    loading.value = false
  }
}

async function submitReview() {
  if (saving.value || form.value.rating < 1) return

  saving.value = true
  error.value = ''
  message.value = ''

  try {
    await saveRecipeReview(props.recipeId, {
      rating: form.value.rating,
      comment: form.value.comment.trim() || null
    })

    await loadReviews()
    message.value = 'Ulasan berhasil disimpan!'
  } catch (e) {
    error.value =
      e.response?.data?.message || 'Gagal menyimpan ulasan.'
  } finally {
    saving.value = false
  }
}

async function removeReview() {
  if (saving.value || !myReview.value) return
  if (!window.confirm('Yakin ingin menghapus ulasan?')) return

  saving.value = true
  error.value = ''
  message.value = ''

  try {
    await deleteRecipeReview(props.recipeId)
    await loadReviews()
    message.value = 'Ulasan berhasil dihapus.'
  } catch (e) {
    error.value =
      e.response?.data?.message || 'Gagal menghapus ulasan.'
  } finally {
    saving.value = false
  }
}

watch(
  () => props.recipeId,
  () => {
    message.value = ''
    loadReviews()
  },
  { immediate: true }
)
</script>

<style scoped>
.reviews {
  margin-top: 46px;
  padding: 28px;
  border: 1px solid #e3e7e0;
  border-radius: 16px;
  background: #fff;
  color: #26392e;
}

.review-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.review-heading h2 {
  font: normal 27px Georgia, serif;
  margin: 0;
}

.hint {
  color: #8a968d;
  font-size: 13px;
  line-height: 1.7;
}

.rating-summary {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.rating-summary strong {
  color: #d7a52e;
  font-size: 23px;
}

.rating-summary small {
  color: #829087;
}

.review-form {
  margin-top: 25px;
  padding: 22px;
  border-radius: 12px;
  background: #f8f7f4;
}

.review-form h3,
.review-list h3 {
  margin: 0 0 18px;
  font-size: 17px;
}

.review-form label {
  display: block;
  margin: 12px 0;
  font-size: 13px;
  font-weight: 600;
}

.rating-stars {
  display: flex;
  gap: 6px;
  margin-bottom: 20px;
}

.rating-stars button {
  border: none;
  background: none;
  color: #d2d8d0;
  font-size: 34px;
  cursor: pointer;
}

.rating-stars button.selected {
  color: #e3b33c;
}

textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 14px;
  border: 1px solid #dfe5dd;
  border-radius: 9px;
  font-family: inherit;
  resize: vertical;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 16px;
}

.submit-btn {
  background: #315e43;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  cursor: pointer;
}

.delete-btn {
  background: white;
  color: #b04444;
  border: 1px solid #e3c8c8;
  border-radius: 8px;
  padding: 12px 20px;
  cursor: pointer;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.review-list {
  margin-top: 30px;
}

.review-item {
  border-bottom: 1px solid #e8ece6;
  padding: 18px 0;
}

.review-top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.review-stars {
  color: #d7a52e;
}

.review-item small {
  color: #8a968d;
}

.review-item p {
  font-size: 13px;
  line-height: 1.8;
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

.error {
  color: #b04444;
}

.success {
  color: #315e43;
}

@media (max-width: 520px) {
  .reviews {
    padding: 18px;
  }

  .review-heading {
    align-items: flex-start;
  }
}
</style>
