
<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toastSuccess, toastError } from '../../services/toast'
import {
  createRecipe,
  getCategories,
} from '../../services/recipeService'

const router = useRouter()

const categories = ref([])
const loadingCategories = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  title: '',
  category_id: '',
  difficulty: '',
  description: '',
  cooking_time: '',
  servings: '',
})

const ingredients = ref(['', ''])
const instructions = ref(['', ''])

const difficulties = ['Mudah', 'Sedang', 'Sulit']

const selectedImage = ref(null)
const imagePreview = ref('')
const imageInput = ref(null)

function clearImage() {
  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value)
  imagePreview.value = ''
  selectedImage.value = null
  if (imageInput.value) imageInput.value.value = ''
}

function handleImageChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    clearImage()
    errorMessage.value = 'Format gambar harus JPG, PNG, atau WEBP.'
    toastError(errorMessage.value)
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    clearImage()
    errorMessage.value = 'Ukuran gambar maksimal 2 MB.'
    toastError(errorMessage.value)
    return
  }

  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value)
  selectedImage.value = file
  imagePreview.value = URL.createObjectURL(file)
  errorMessage.value = ''
}

onBeforeUnmount(() => {
  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value)
})

const canRemoveIngredient = computed(
  () => ingredients.value.length > 1,
)

const canRemoveInstruction = computed(
  () => instructions.value.length > 1,
)

function extractArray(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

async function loadCategories() {
  loadingCategories.value = true

  try {
    const response = await getCategories()
    categories.value = extractArray(response)
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Gagal mengambil kategori dari Laravel.'
    toastError(errorMessage.value)
  } finally {
    loadingCategories.value = false
  }
}

function addIngredient() {
  ingredients.value.push('')
}

function removeIngredient(index) {
  if (ingredients.value.length > 1) {
    ingredients.value.splice(index, 1)
  }
}

function addInstruction() {
  instructions.value.push('')
}

function removeInstruction(index) {
  if (instructions.value.length > 1) {
    instructions.value.splice(index, 1)
  }
}

function validateForm() {
  const data = form.value

  if (
    !data.title.trim() ||
    !data.category_id ||
    !data.difficulty ||
    !data.description.trim() ||
    !selectedImage.value
  ) {
    return 'Lengkapi seluruh informasi resep yang wajib diisi.'
  }

  if (
    !Number.isInteger(Number(data.cooking_time)) ||
    Number(data.cooking_time) < 1
  ) {
    return 'Waktu memasak minimal 1 menit.'
  }

  if (
    !Number.isInteger(Number(data.servings)) ||
    Number(data.servings) < 1
  ) {
    return 'Jumlah porsi minimal 1.'
  }

  if (
    ingredients.value.some((item) => !item.trim()) ||
    instructions.value.some((item) => !item.trim())
  ) {
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
    return
  }

  saving.value = true

  try {
    const payload = new FormData()
    payload.append('title', form.value.title.trim())
    payload.append('category_id', String(form.value.category_id))
    payload.append('difficulty', form.value.difficulty)
    payload.append('description', form.value.description.trim())
    payload.append('cooking_time', String(Number(form.value.cooking_time)))
    payload.append('servings', String(Number(form.value.servings)))
    payload.append('image', selectedImage.value)

    // Backend Laravel menyimpan bahan dan langkah sebagai teks
    payload.append('ingredients', ingredients.value
      .map((item) => item.trim())
      .join('\n'))
    payload.append('instructions', instructions.value
      .map((item, index) => `${index + 1}. ${item.trim()}`)
      .join('\n'))

    await createRecipe(payload)

    successMessage.value = 'Resep berhasil ditambahkan.'
    toastSuccess(successMessage.value)

    // Pindah ke daftar resep setelah penyimpanan berhasil
    await router.push('/admin/recipes')
  } catch (error) {
    const errors = error.response?.data?.errors

    if (errors) {
      errorMessage.value = Object.values(errors)
        .flat()
        .join(' ')
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        'Gagal menyimpan resep. Periksa koneksi Laravel.'
    }
    toastError(errorMessage.value)
  } finally {
    saving.value = false
  }
}

function cancelForm() {
  router.push('/admin/recipes')
}

onMounted(loadCategories)
</script>

<template>
  <main class="create-page">
    <div class="page-heading">
      <h1>Tambah Resep</h1>
      <p>Bagikan inspirasi masakan baru ke koleksi RecipeBox.</p>
    </div>

    <p class="helper-note">
      <span class="info-symbol">ⓘ</span>
      Tanda * menunjukkan field wajib diisi.
      Bahan dan langkah dapat ditambah atau dihapus.
    </p>

    <div v-if="errorMessage" class="message error-message" role="alert">
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="message success-message" role="status">
      {{ successMessage }}
    </div>

    <form novalidate @submit.prevent="handleSubmit">
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
              placeholder="Contoh: Soto Ayam Kuning"
              required
            />
          </div>

          <div class="two-columns">
            <div class="form-group">
              <label for="category">Kategori *</label>
              <select
                id="category"
                v-model="form.category_id"
                :disabled="loadingCategories"
                required
              >
                <option value="">
                  {{ loadingCategories ? 'Memuat kategori...' : 'Pilih kategori' }}
                </option>

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
              <select id="difficulty" v-model="form.difficulty" required>
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
              placeholder="Ceritakan rasa dan keistimewaan masakan ini..."
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
                placeholder="0"
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
                placeholder="0"
                required
              />
              <small>Jumlah porsi, minimal 1 porsi.</small>
            </div>
          </div>
        </section>

        <!-- Foto Masakan -->
        <aside class="form-card image-card">
          <div class="image-heading">
            <svg viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="1.8">
              <rect x="3" y="3" width="18" height="18" rx="3" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <path d="m4 18 5-5 3 3 4-5 4 5" />
            </svg>
            <h2>Foto Masakan</h2>
          </div>

          <div class="form-group">
            <label for="image">Upload Gambar *</label>
            <input
              id="image"
              ref="imageInput"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              required
              @change="handleImageChange"
            />
            <small>Pilih gambar JPG, PNG, atau WEBP maksimal 2 MB.</small>
          </div>

          <div v-if="imagePreview" class="image-preview-box">
            <img :src="imagePreview" alt="Pratinjau foto masakan" />
            <div class="image-preview-actions">
              <span>{{ selectedImage?.name }}</span>
              <button type="button" class="remove-image-button" @click="clearImage">
                Hapus Gambar
              </button>
            </div>
          </div>

          <div class="image-tip">
            <span class="tip-icon">▣</span>
            <strong>Buat masakan terlihat menggugah.</strong>
            <p>
              Pilih foto yang terang dan tajam.
              Rasio 4:3 disarankan agar masakan
              tampil utuh di kartu resep.
            </p>
          </div>

        </aside>
      </div>

      <div class="bottom-grid">
        <!-- Bahan -->
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
                placeholder="Contoh: 500 g ayam, potong 4 bagian"
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

          <button
            type="button"
            class="add-button"
            @click="addIngredient"
          >
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
                placeholder="Jelaskan proses memasak pada langkah ini..."
                required
              ></textarea>
            </div>
          </div>

          <button
            type="button"
            class="add-button"
            @click="addInstruction"
          >
            <span>+</span> Tambah Langkah
          </button>
        </section>
      </div>

      <!-- Tombol Aksi -->
      <div class="form-actions">
        <p>ⓘ Periksa kembali bahan dan langkah sebelum menyimpan.</p>

        <div class="action-buttons">
          <button
            type="button"
            class="cancel-button"
            :disabled="saving"
            @click="cancelForm"
          >
            Batal
          </button>

          <button
            type="submit"
            class="save-button"
            :disabled="saving || loadingCategories"
          >
            {{ saving ? 'Menyimpan...' : '✓ Simpan Resep' }}
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
.create-page {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: 36px 40px;
  background: #f8f7f3;
  color: #27332b;
  min-height: calc(100vh - 80px);
}

.page-heading h1 {
  font-size: 30px;
  margin: 0 0 8px;
  font-weight: 700;
}

.page-heading p {
  font-size: 14px;
  color: #7b867e;
  margin: 0;
}

.helper-note {
  display: flex;
  align-items: center;
  gap: 9px;
  margin: 26px 0 24px;
  color: #7b867e;
  font-size: 12px;
}

.info-symbol {
  font-size: 18px;
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
  align-items: start;
  margin-top: 24px;
}

.form-card {
  min-width: 0;
  background: #fff;
  border: 1px solid #e1e5dd;
  border-radius: 15px;
  padding: 24px;
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
  background: #eaf0e8;
  color: #315e43;
  border-radius: 7px;
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
  color: #8a948c;
  font-size: 12px;
  margin: 0;
  line-height: 1.6;
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
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 9px;
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
  border: 1px solid #dfe5dc;
  border-radius: 9px;
  background: #fff;
  padding: 13px 14px;
  font: inherit;
  font-size: 13px;
  color: #27332b;
  outline: none;
}

.form-group input,
.form-group select,
.ingredient-row input {
  height: 44px;
}

.form-group textarea,
.instruction-item textarea {
  resize: vertical;
  min-height: 90px;
  line-height: 1.6;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #315e43;
  box-shadow: 0 0 0 2px rgba(49, 94, 67, 0.08);
}

input::placeholder,
textarea::placeholder {
  color: #9ba59c;
}

.form-group small {
  display: block;
  margin-top: 8px;
  color: #8a948c;
  font-size: 11px;
  line-height: 1.5;
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

.image-heading svg {
  width: 20px;
  height: 20px;
  color: #315e43;
}

.image-heading h2 {
  margin: 0;
}

.image-tip {
  background: #eaf0e8;
  border-radius: 10px;
  padding: 18px;
  color: #315e43;
}

.tip-icon {
  display: block;
  font-size: 24px;
  margin-bottom: 10px;
}

.image-tip strong {
  display: block;
  font-size: 12px;
  margin-bottom: 10px;
}

.image-tip p {
  font-size: 12px;
  line-height: 1.7;
  margin: 0;
}

.preview-information {
  margin-top: 16px;
  color: #315e43;
  font-size: 12px;
  overflow-wrap: anywhere;
}

/* Pratinjau gambar yang dipilih dari komputer */
.image-preview-box {
  margin: 0 0 18px;
  border: 1px solid #dfe5dc;
  border-radius: 10px;
  overflow: hidden;
  background: #f8f7f3;
}

.image-preview-box img {
  display: block;
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
}

.image-preview-actions {
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
}

.image-preview-actions span {
  font-size: 11px;
  color: #78847a;
  overflow-wrap: anywhere;
  min-width: 0;
}

.remove-image-button {
  border: 1px solid #dfe5dc;
  border-radius: 8px;
  background: white;
  color: #b84236;
  padding: 8px 10px;
  font: inherit;
  font-size: 11px;
  cursor: pointer;
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
  border: none;
  background: transparent;
  color: #78847a;
  font-size: 23px;
  cursor: pointer;
  width: 26px;
  height: 32px;
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
  border: 1px solid #dfe5dc;
  border-radius: 9px;
  background: #fff;
  padding: 11px 16px;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  color: #27332b;
  cursor: pointer;
}

.add-button span {
  font-size: 20px;
  color: #315e43;
}

.add-button:hover {
  background: #f0f3ee;
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

.instruction-item textarea {
  width: 100%;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  background: white;
  border: 1px solid #e1e5dd;
  border-radius: 15px;
  padding: 20px 24px;
  margin-top: 24px;
}

.form-actions p {
  font-size: 12px;
  color: #8a948c;
  margin: 0;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.cancel-button,
.save-button {
  border-radius: 9px;
  padding: 12px 18px;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}

.cancel-button {
  background: white;
  border: 1px solid #dfe5dc;
  color: #27332b;
}

.save-button {
  background: #315e43;
  border: 1px solid #315e43;
  color: white;
}

.save-button:hover {
  background: #203e2d;
}

.save-button:disabled,
.cancel-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  border-radius: 9px;
  padding: 13px 16px;
  font-size: 13px;
  margin-bottom: 18px;
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
  .create-page {
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
  .create-page {
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
</style>
