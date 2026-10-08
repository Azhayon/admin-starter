import { createBrowserRouter } from "react-router-dom"
import AdminLayout from "./layouts/AdminLayout"
import AuthLayout from "./layouts/AuthLayout"
import RequireAuth from "@/features/auth/RequireAuth"
import LoginPage from "@/features/auth/LoginPage"
import DashboardPage from "@/features/dashboard/DashboardPage"

const router = createBrowserRouter([
  // Public
  { element: <AuthLayout />, children: [{ path: "/login", element: <LoginPage /> }] },

  // Protected: RequireAuth wraps everything inside it
  {
    element: <RequireAuth />,
    children: [
      { element: <AdminLayout />, children: [{ path: "/", element: <DashboardPage /> }] },
    ],
  },
])

export default router