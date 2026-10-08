import api from "@/lib/api"

export const ME_KEY = ["auth", "me"] // the cache key for "who is logged in"

// Sanctum SPA flow: get the CSRF cookie first, then POST credentials.
export async function login(credentials) {
  await api.get("/sanctum/csrf-cookie")
  await api.post("/login", credentials)
}

export async function logout() {
  await api.post("/logout")
}

// Returns the user, or null when not logged in (401 is an expected answer, not an error).
export async function fetchMe() {
  try {
    const { data } = await api.get("/api/v1/me")
    return data.data
  } catch (error) {
    if (error.response?.status === 401) return null
    throw error
  }
}