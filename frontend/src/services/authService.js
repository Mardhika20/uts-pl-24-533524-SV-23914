
import api from './api'

export async function registerUser(userData) {
  const response = await api.post('/register', {
    name: userData.name,
    email: userData.email,
    password: userData.password,
    password_confirmation: userData.passwordConfirmation,
  })

  return response.data
}

export async function loginUser(email, password) {
  const response = await api.post('/login', {
    email,
    password,
  })

  return response.data
}

export function saveAuth(token, user) {
  localStorage.setItem('auth_token', token)
  localStorage.setItem('auth_user', JSON.stringify(user))
}

export function getToken() {
  return localStorage.getItem('auth_token')
}

export function getAuthUser() {
  const user = localStorage.getItem('auth_user')

  if (!user) return null

  try {
    return JSON.parse(user)
  } catch {
    return null
  }
}

export function clearAuth() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
}


export async function logoutUser() {
  try {
    await api.post('/logout')
  } finally {
    clearAuth()
  }
}

