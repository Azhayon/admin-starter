import axios from "axios"
import { queryClient } from "./queryClient"
import { ME_KEY } from "./queryKeys"

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: "application/json" },
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const url = error.config?.url ?? ""
    // 401 (not logged in) or 419 (CSRF/session expired) on any call except the
    // login attempt and the initial "who am I?" check = the session is gone.
    if ((status === 401 || status === 419) && !url.includes("/login") && !url.endsWith("/api/v1/me")) {
      queryClient.setQueryData(ME_KEY, null)
    }
    return Promise.reject(error)
  }
)

export default api