
import api from './api'

// Mengambil review berdasarkan ID resep
export async function getRecipeReviews(recipeId) {
  const response = await api.get(
    `/recipes/${recipeId}/reviews`
  )

  return response.data
}

// Menambah atau mengubah review sendiri
export async function saveRecipeReview(recipeId, payload) {
  const response = await api.put(
    `/recipes/${recipeId}/my-review`,
    payload
  )

  return response.data
}

// Menghapus review sendiri
export async function deleteRecipeReview(recipeId) {
  const response = await api.delete(
    `/recipes/${recipeId}/my-review`
  )

  return response.data
}
