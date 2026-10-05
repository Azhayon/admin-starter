import axios from "axios"

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL, // http://localhost:8000
  withCredentials: true,   // send/receive the session cookie cross-origin
  withXSRFToken: true,     // copy the XSRF-TOKEN cookie into the X-XSRF-TOKEN header
  headers: { Accept: "application/json" }, // Laravel returns JSON errors, not redirects
})

export default api