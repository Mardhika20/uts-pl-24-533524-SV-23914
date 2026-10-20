import api from './api'

// Mengambil daftar resep favorit
export async function getFavorites() {
  const response = await api.get('/favorites')
  return response.data
}

// Menambahkan resep ke favorit
export async function addFavorite(recipeId) {
  const response = await api.post('/favorites', {
    recipe_id: recipeId,
  })

  return response.data
}

// Menghapus resep dari favorit
export async function removeFavorite(recipeId) {
  const response = await api.delete(`/favorites/${recipeId}`)
  return response.data
}