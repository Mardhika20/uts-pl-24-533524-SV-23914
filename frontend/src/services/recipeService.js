import api from './api'

// Mengambil seluruh kategori
export async function getCategories() {
  const response = await api.get('/categories')
  return response.data
}

// Mengambil seluruh resep
export async function getRecipes() {
  const response = await api.get('/recipes')
  return response.data
}

// Mengambil detail resep
export async function getRecipeById(id) {
  const response = await api.get(`/recipes/${id}`)
  return response.data
}

// Menambahkan resep dengan upload gambar
export async function createRecipe(data) {
  const response = await api.post('/recipes', data)
  return response.data
}

// Mengubah resep, termasuk mengganti gambar
export async function updateRecipe(id, data) {
  if (data instanceof FormData) {
    data.set('_method', 'PUT')

    const response = await api.post(`/recipes/${id}`, data)
    return response.data
  }

  const response = await api.put(`/recipes/${id}`, data)
  return response.data
}

// Menghapus resep
export async function deleteRecipe(id) {
  const response = await api.delete(`/recipes/${id}`)
  return response.data
}
