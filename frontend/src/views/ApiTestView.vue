<script setup>
import { ref } from 'vue'
import { getCategories, getRecipes, getRecipeById } from '../services/recipeService'
import { getFavorites } from '../services/favoriteService'

const loading = ref(false)
const result = ref(null)
const error = ref('')
const recipeId = ref('1')

async function runTest(testFunction) {
  loading.value = true
  result.value = null
  error.value = ''

  try {
    const data = await testFunction()
    result.value = data
    console.log('API berhasil:', data)
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      err.message ||
      'Terjadi kesalahan saat mengakses API'

    console.error('API gagal:', err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="api-test">
    <h1>Pengujian REST API Laravel</h1>
    <p>
      Pengujian integrasi endpoint kategori, resep, detail resep,
      dan favorit menggunakan Axios.
    </p>

    <div class="test-buttons">
      <button @click="runTest(getCategories)">
        GET Kategori
      </button>

      <button @click="runTest(getRecipes)">
        GET Resep
      </button>

      <button @click="runTest(() => getRecipeById(recipeId))">
        GET Detail Resep
      </button>

      <button @click="runTest(getFavorites)">
        GET Favorit
      </button>
    </div>

    <div class="recipe-input">
      <label for="recipe-id">ID Resep:</label>
      <input
        id="recipe-id"
        v-model="recipeId"
        type="number"
        min="1"
      />
    </div>

    <p v-if="loading">Sedang mengambil data...</p>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <div v-if="result !== null" class="result">
      <h2>Hasil Respons API</h2>
      <pre>{{ JSON.stringify(result, null, 2) }}</pre>
    </div>
  </main>
</template>

<style scoped>
.api-test {
  max-width: 1000px;
  margin: 40px auto;
  padding: 24px;
  font-family: Arial, sans-serif;
}

h1 {
  color: #315e43;
}

.test-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin: 24px 0;
}

button {
  padding: 12px 18px;
  background: #315e43;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

button:hover {
  background: #203e2d;
}

.recipe-input {
  margin-bottom: 20px;
}

input {
  margin-left: 12px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 6px;
  width: 90px;
}

.result {
  margin-top: 24px;
}

pre {
  padding: 20px;
  background: #f3f4f3;
  border-radius: 8px;
  overflow-x: auto;
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

.error {
  padding: 12px;
  background: #fde8e8;
  color: #b84236;
  border-radius: 8px;
}
</style>