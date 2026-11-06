
import api from './api'

// Mencari resep berdasarkan bahan yang tersedia
export async function findRecipesByIngredients(ingredients) {
  const response = await api.get(
    '/recipes/find-by-ingredients',
    {
      params: {
        ingredients: ingredients
      }
    }
  )

  return response.data
}
