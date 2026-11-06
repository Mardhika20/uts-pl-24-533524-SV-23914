
import api from './api'

// Mengambil data statistik Dashboard Admin
// Endpoint hanya dapat diakses oleh Admin

export async function getAdminAnalytics() {
  const response = await api.get('/admin/analytics')

  return response.data.data
}
