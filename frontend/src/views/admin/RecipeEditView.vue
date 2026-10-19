
<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCategories, getRecipeById, updateRecipe } from '../../services/recipeService'
import { toastSuccess, toastError } from '../../services/toast'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const recipeDate = ref('')
const categories = ref([])
const form = ref({
  title: '', category_id: '', difficulty: '', description: '',
  cooking_time: '', servings: '',
})
const ingredients = ref([''])
const instructions = ref([''])
const difficulties = ['Mudah', 'Sedang', 'Sulit']

// Gambar yang sudah tersimpan tetap digunakan bila tidak ada upload baru.
const existingImageUrl = ref('')
const selectedImage = ref(null)
const localPreviewUrl = ref('')
const imageFailed = ref(false)
const imageInput = ref(null)
const previewUrl = computed(() => localPreviewUrl.value || existingImageUrl.value)
const canRemoveIngredient = computed(() => ingredients.value.length > 1)
const canRemoveInstruction = computed(() => instructions.value.length > 1)

function extractArray(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function extractRecipe(response) {
  const value = response?.data ?? response
  if (value?.data && !Array.isArray(value.data)) return value.data
  return value
}

function formatDate(value) {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric',
  }).format(date)
}

function parseIngredients(value) {
  if (Array.isArray(value)) return value.map(String)
  if (typeof value === 'string') {
    return value.split(/\r?\n/).map(item => item.trim()).filter(Boolean)
  }
  return []
}

function parseInstructions(value) {
  if (Array.isArray(value)) return value.map(String)
  if (typeof value === 'string') {
    return value.split(/\r?\n/)
      .map(item => item.replace(/^\s*\d+[.)]\s*/, '').trim())
      .filter(Boolean)
  }
  return []
}

function clearLocalPreview() {
  if (localPreviewUrl.value) URL.revokeObjectURL(localPreviewUrl.value)
  localPreviewUrl.value = ''
}

function handleImageChange(event) {
  errorMessage.value = ''
  const file = event.target.files?.[0]
  if (!file) return
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    errorMessage.value = 'Format gambar harus JPG, PNG, atau WEBP.'
    toastError(errorMessage.value)
    event.target.value = ''
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    errorMessage.value = 'Ukuran gambar maksimal 2 MB.'
    toastError(errorMessage.value)
    event.target.value = ''
    return
  }
  clearLocalPreview()
  selectedImage.value = file
  localPreviewUrl.value = URL.createObjectURL(file)
  imageFailed.value = false
}

function cancelNewImage() {
  selectedImage.value = null
  clearLocalPreview()
  imageFailed.value = false
  if (imageInput.value) imageInput.value.value = ''
}

function handleImageError() {
  imageFailed.value = true
}

async function loadRecipe() {
  loading.value = true
  errorMessage.value = ''
  try {
    const [recipeResponse, categoryResponse] = await Promise.all([
      getRecipeById(route.params.id), getCategories(),
    ])
    const recipe = extractRecipe(recipeResponse)
    if (!recipe?.id) throw new Error('Data resep tidak ditemukan.')
    categories.value = extractArray(categoryResponse)
    form.value = {
      title: recipe.title ?? '',
      category_id: String(recipe.category_id ?? ''),
      difficulty: recipe.difficulty ?? '',
      description: recipe.description ?? '',
      cooking_time: recipe.cooking_time ?? '',
      servings: recipe.servings ?? '',
    }
    existingImageUrl.value = recipe.image_url ?? ''
    imageFailed.value = false
    cancelNewImage()
    const savedIngredients = parseIngredients(recipe.ingredients)
    const savedInstructions = parseInstructions(recipe.instructions)
    ingredients.value = savedIngredients.length ? savedIngredients : ['']
    instructions.value = savedInstructions.length ? savedInstructions : ['']
    recipeDate.value = formatDate(recipe.created_at)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message || 'Gagal memuat data resep.'
    toastError(errorMessage.value)
  } finally {
    loading.value = false
  }
}

function addIngredient() { ingredients.value.push('') }
function removeIngredient(index) {
  if (ingredients.value.length > 1) ingredients.value.splice(index, 1)
}
function addInstruction() { instructions.value.push('') }
function removeInstruction(index) {
  if (instructions.value.length > 1) instructions.value.splice(index, 1)
}

function validateForm() {
  const data = form.value
  if (!data.title.trim() || !data.category_id || !data.difficulty || !data.description.trim()) {
    return 'Lengkapi seluruh informasi resep yang wajib diisi.'
  }
  if (!Number.isInteger(Number(data.cooking_time)) || Number(data.cooking_time) < 1) {
    return 'Waktu memasak minimal 1 menit.'
  }
  if (!Number.isInteger(Number(data.servings)) || Number(data.servings) < 1) {
    return 'Jumlah porsi minimal 1.'
  }
  if (ingredients.value.some(item => !item.trim()) || instructions.value.some(item => !item.trim())) {
    return 'Semua bahan dan langkah memasak harus diisi.'
  }
  return ''
}

async function handleSubmit() {
  if (saving.value) return
  errorMessage.value = ''
  successMessage.value = ''
  const validationError = validateForm()
  if (validationError) {
    errorMessage.value = validationError
    toastError(validationError)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }
  saving.value = true
  try {
    const payload = new FormData()
    payload.append('title', form.value.title.trim())
    payload.append('category_id', String(form.value.category_id))
    payload.append('difficulty', form.value.difficulty)
    payload.append('description', form.value.description.trim())
    payload.append('cooking_time', String(form.value.cooking_time))
    payload.append('servings', String(form.value.servings))
    payload.append('ingredients', ingredients.value.map(item => item.trim()).join('\n'))
    payload.append('instructions', instructions.value
      .map((item, index) => `${index + 1}. ${item.trim()}`).join('\n'))
    if (selectedImage.value) payload.append('image', selectedImage.value)

    // recipeService menambahkan _method=PUT ketika menerima FormData.
    await updateRecipe(route.params.id, payload)
    successMessage.value = 'Perubahan resep berhasil disimpan.'
    toastSuccess(successMessage.value)
    await router.push('/admin/recipes')
  } catch (error) {
    const errors = error.response?.data?.errors
    errorMessage.value = errors
      ? Object.values(errors).flat().join(' ')
      : error.response?.data?.message || 'Gagal menyimpan perubahan resep.'
    toastError(errorMessage.value)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    saving.value = false
  }
}

function cancelEdit() { router.push('/admin/recipes') }
onMounted(loadRecipe)
onBeforeUnmount(clearLocalPreview)
</script>

<template>
  <main class="edit-page">
    <div class="page-heading">
      <h1>Edit Resep</h1>
      <p v-if="!loading">
        {{ form.title }} · Dibuat {{ recipeDate }}
      </p>
    </div>

    <button type="button" class="back-link" @click="cancelEdit">
      ← Kembali ke Kelola Resep
    </button>

    <p class="helper-note">
      <span>ⓘ</span>
      Tanda * menunjukkan field wajib diisi.
      Bahan dan langkah dapat ditambah atau dihapus.
    </p>

    <div v-if="errorMessage" class="message error-message" role="alert">
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="message success-message" role="status">
      {{ successMessage }}
    </div>

    <div v-if="loading" class="loading-state">
      Memuat data resep...
    </div>

    <form v-else @submit.prevent="handleSubmit" novalidate>
      <div class="top-grid">
        <!-- Informasi Resep -->
        <section class="form-card">
          <div class="section-heading">
            <span class="section-number">01</span>
            <div>
              <h2>Informasi Resep</h2>
              <p>Informasi utama yang tampil di kartu dan halaman detail.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="title">Nama Resep *</label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              placeholder="Masukkan nama resep"
            />
          </div>

          <div class="two-columns">
            <div class="form-group">
              <label for="category">Kategori *</label>
              <select
                id="category"
                v-model="form.category_id"
                required
              >
                <option value="">Pilih kategori</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="String(category.id)"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label for="difficulty">Tingkat Kesulitan *</label>
              <select
                id="difficulty"
                v-model="form.difficulty"
                required
              >
                <option value="">Pilih tingkat</option>
                <option
                  v-for="difficulty in difficulties"
                  :key="difficulty"
                  :value="difficulty"
                >
                  {{ difficulty }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="description">Deskripsi *</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              required
            ></textarea>
          </div>

          <div class="two-columns">
            <div class="form-group">
              <label for="cooking_time">Waktu Memasak *</label>
              <input
                id="cooking_time"
                v-model="form.cooking_time"
                type="number"
                min="1"
                step="1"
                required
              />
              <small>Angka dalam menit, minimal 1 menit.</small>
            </div>

            <div class="form-group">
              <label for="servings">Jumlah Porsi *</label>
              <input
                id="servings"
                v-model="form.servings"
                type="number"
                min="1"
                step="1"
                required
              />
              <small>Jumlah porsi, minimal 1 porsi.</small>
            </div>
          </div>
        </section>

        <!-- Foto Masakan -->
        <aside class="form-card image-card">
          <div class="image-heading">
            <span>▧</span>
            <h2>Foto Masakan</h2>
          </div>

          <div class="form-group">
            <label for="recipe-image">Upload Gambar Baru</label>
            <input
              id="recipe-image"
              ref="imageInput"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              :disabled="saving"
              @change="handleImageChange"
            />
            <small>JPG, PNG, atau WEBP. Maksimal 2 MB. Kosongkan jika tidak ingin mengganti gambar.</small>
          </div>

          <div v-if="previewUrl && !imageFailed" class="image-preview">
            <img
              :src="previewUrl"
              alt="Pratinjau foto masakan"
              @error="handleImageError"
            />
            <small>{{ selectedImage ? 'Pratinjau gambar baru' : 'Gambar resep saat ini' }}</small>
          </div>

          <div v-else class="image-placeholder">
            <span>▧</span>
            <strong>{{ imageFailed ? 'Gambar tidak dapat dimuat' : 'Belum ada foto masakan' }}</strong>
            <p>{{ imageFailed ? 'Silakan pilih gambar baru untuk menggantinya.' : 'Pilih gambar dari komputer untuk menambahkannya.' }}</p>
          </div>

          <button
            v-if="selectedImage"
            type="button"
            class="remove-image-button"
            :disabled="saving"
            @click="cancelNewImage"
          >
            Batalkan Gambar Baru
          </button>
        </aside>
      </div>

      <div class="bottom-grid">
        <!-- Bahan-bahan -->
        <section class="form-card">
          <div class="section-heading">
            <span class="section-number">02</span>
            <div>
              <h2>Bahan-bahan *</h2>
              <p>Satu bahan per baris. Sertakan takaran dan persiapannya.</p>
            </div>
          </div>

          <div class="dynamic-fields">
            <div
              v-for="(ingredient, index) in ingredients"
              :key="index"
              class="ingredient-row"
            >
              <span class="row-number">{{ index + 1 }}</span>

              <input
                v-model="ingredients[index]"
                type="text"
                :aria-label="`Bahan ${index + 1}`"
                placeholder="Masukkan bahan dan takarannya"
                required
              />

              <button
                type="button"
                class="remove-button"
                :disabled="!canRemoveIngredient"
                :aria-label="`Hapus bahan ${index + 1}`"
                @click="removeIngredient(index)"
              >
                ×
              </button>
            </div>
          </div>

          <button type="button" class="add-button" @click="addIngredient">
            <span>+</span> Tambah Bahan
          </button>
        </section>

        <!-- Langkah Memasak -->
        <section class="form-card">
          <div class="section-heading">
            <span class="section-number">03</span>
            <div>
              <h2>Langkah Memasak *</h2>
              <p>
                Tulis langkah berurutan dan mudah diikuti,
                dari persiapan hingga penyajian.
              </p>
            </div>
          </div>

          <div class="dynamic-fields">
            <div
              v-for="(instruction, index) in instructions"
              :key="index"
              class="instruction-item"
            >
              <div class="instruction-label">
                <label :for="`instruction-${index}`">
                  Langkah {{ index + 1 }} *
                </label>

                <button
                  type="button"
                  class="remove-button"
                  :disabled="!canRemoveInstruction"
                  :aria-label="`Hapus langkah ${index + 1}`"
                  @click="removeInstruction(index)"
                >
                  ×
                </button>
              </div>

              <textarea
                :id="`instruction-${index}`"
                v-model="instructions[index]"
                rows="4"
                required
                placeholder="Tuliskan langkah memasak..."
              ></textarea>
            </div>
          </div>

          <button type="button" class="add-button" @click="addInstruction">
            <span>+</span> Tambah Langkah
          </button>
        </section>
      </div>

      <!-- Tombol Simpan -->
      <div class="form-actions">
        <p>ⓘ Periksa kembali bahan dan langkah sebelum menyimpan.</p>

        <div class="action-buttons">
          <button
            type="button"
            class="cancel-button"
            :disabled="saving"
            @click="cancelEdit"
          >
            Batal
          </button>

          <button
            type="submit"
            class="save-button"
            :disabled="saving"
          >
            {{ saving ? 'Menyimpan...' : '✓ Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </form>

    <footer class="page-footer">
      © {{ new Date().getFullYear() }} RecipeBox · Panel Admin
    </footer>
  </main>
</template>

<style scoped>
.edit-page {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  min-height: calc(100vh - 80px);
  padding: 36px 40px;
  background: #f8f7f3;
  color: #27332b;
}

.page-heading h1 {
  font-size: 30px;
  margin: 0 0 8px;
  font-weight: 700;
}

.page-heading p {
  margin: 0;
  color: #7b867e;
  font-size: 13px;
}

.back-link {
  display: inline-flex;
  margin: 26px 0 18px;
  padding: 0;
  border: 0;
  background: transparent;
  color: #315e43;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.helper-note {
  margin: 0 0 24px;
  font-size: 12px;
  color: #7b867e;
}

.helper-note span {
  margin-right: 8px;
  color: #315e43;
}

.top-grid {
  display: grid;
  grid-template-columns: minmax(0, 2.2fr) minmax(240px, 1fr);
  gap: 20px;
  align-items: start;
}

.bottom-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
  margin-top: 24px;
  align-items: start;
}

.form-card {
  min-width: 0;
  padding: 24px;
  border: 1px solid #e1e5dd;
  border-radius: 15px;
  background: #fff;
  box-sizing: border-box;
}

.section-heading {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 26px;
}

.section-number {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  width: 26px;
  height: 26px;
  border-radius: 7px;
  background: #eaf0e8;
  color: #315e43;
  font-size: 12px;
  font-weight: 700;
}

.section-heading h2,
.image-heading h2 {
  margin: 0 0 6px;
  font-size: 17px;
  font-weight: 700;
}

.section-heading p {
  margin: 0;
  font-size: 12px;
  line-height: 1.6;
  color: #8a948c;
}

.form-group {
  margin-bottom: 22px;
  min-width: 0;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label,
.instruction-label label {
  display: block;
  margin-bottom: 9px;
  font-size: 12px;
  font-weight: 700;
}

.form-group input,
.form-group select,
.form-group textarea,
.ingredient-row input,
.instruction-item textarea {
  display: block;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: 13px 14px;
  border: 1px solid #dfe5dc;
  border-radius: 9px;
  background: #fff;
  color: #27332b;
  font: inherit;
  font-size: 13px;
  outline: none;
}

.form-group input,
.form-group select,
.ingredient-row input {
  height: 44px;
}

.form-group textarea,
.instruction-item textarea {
  min-height: 90px;
  resize: vertical;
  line-height: 1.6;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #315e43;
  box-shadow: 0 0 0 2px rgba(49, 94, 67, 0.08);
}

.form-group small {
  display: block;
  margin-top: 8px;
  font-size: 11px;
  line-height: 1.5;
  color: #8a948c;
}

.two-columns {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.image-heading {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.image-heading span {
  color: #315e43;
  font-size: 21px;
}

.image-heading h2 {
  margin: 0;
}

.image-preview img {
  display: block;
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border-radius: 10px;
  background: #eaf0e8;
}

.image-preview small {
  display: block;
  margin-top: 8px;
  color: #8a948c;
  font-size: 11px;
}

.image-placeholder {
  min-height: 150px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 18px;
  border-radius: 10px;
  background: #eaf0e8;
  color: #315e43;
  text-align: center;
}

.image-placeholder span {
  font-size: 27px;
  margin-bottom: 12px;
}

.image-placeholder strong {
  font-size: 13px;
}

.image-placeholder p {
  margin: 10px 0 0;
  font-size: 12px;
  line-height: 1.6;
}

.dynamic-fields {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ingredient-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.row-number {
  flex-shrink: 0;
  width: 15px;
  color: #8a948c;
  font-size: 12px;
}

.ingredient-row input {
  flex: 1;
}

.remove-button {
  flex-shrink: 0;
  width: 26px;
  height: 32px;
  border: 0;
  background: transparent;
  color: #78847a;
  font-size: 23px;
  cursor: pointer;
}

.remove-button:hover {
  color: #b84236;
}

.remove-button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.add-button {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-top: 22px;
  padding: 11px 16px;
  border: 1px solid #dfe5dc;
  border-radius: 9px;
  background: #fff;
  color: #27332b;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.add-button span {
  font-size: 20px;
  color: #315e43;
}

.instruction-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.instruction-label label {
  margin: 0;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding: 20px 24px;
  margin-top: 24px;
  border: 1px solid #e1e5dd;
  border-radius: 15px;
  background: #fff;
}

.form-actions p {
  margin: 0;
  font-size: 12px;
  color: #8a948c;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.cancel-button,
.save-button {
  padding: 12px 18px;
  border-radius: 9px;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
}

.cancel-button {
  border: 1px solid #dfe5dc;
  background: #fff;
  color: #27332b;
}

.save-button {
  border: 1px solid #315e43;
  background: #315e43;
  color: #fff;
}

.save-button:hover {
  background: #203e2d;
}

.save-button:disabled,
.cancel-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-state,
.message {
  padding: 18px;
  margin-bottom: 18px;
  border-radius: 10px;
  background: #fff;
  font-size: 13px;
}

.error-message {
  background: #fff0ed;
  color: #b84236;
}

.success-message {
  background: #eaf0e8;
  color: #315e43;
}

.page-footer {
  margin-top: 28px;
  font-size: 12px;
  color: #89958b;
}

@media (max-width: 1050px) {
  .edit-page {
    padding: 28px;
  }

  .top-grid {
    grid-template-columns: minmax(0, 1.6fr) minmax(220px, 1fr);
  }
}

@media (max-width: 800px) {
  .top-grid,
  .bottom-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .edit-page {
    padding: 20px;
  }

  .two-columns {
    grid-template-columns: 1fr;
    gap: 0;
  }

  .form-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .action-buttons {
    justify-content: flex-end;
  }
}

/* Tombol membatalkan gambar yang baru dipilih */
.remove-image-button {
  margin-top: 12px;
  padding: 10px 14px;
  border: 1px solid #dfe5dc;
  border-radius: 9px;
  background: #fff;
  color: #315e43;
  font: inherit;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}
.remove-image-button:disabled { opacity: .6; cursor: not-allowed; }
</style>
