import { createBrowserRouter } from "react-router-dom"
import AdminLayout from "./layouts/AdminLayout"
import AuthLayout from "./layouts/AuthLayout"
import RequireAuth from "@/features/auth/RequireAuth"
import LoginPage from "@/features/auth/LoginPage"
import DashboardPage from "@/features/dashboard/DashboardPage"
import RequirePermission from "@/features/auth/RequirePermission"
import UsersPage from "@/features/users/UsersPage"
import RolesPage from "@/features/roles/RolesPage"

const router = createBrowserRouter([
  // Public
  { element: <AuthLayout />, children: [{ path: "/login", element: <LoginPage /> }] },

  // Protected: RequireAuth wraps everything inside it
  {
    element: <RequireAuth />,
    children: [
      {
        element: <AdminLayout />,
        children: [
          { path: "/", element: <DashboardPage /> },
          {
            element: <RequirePermission permission="users.view" />,
            children: [{ path: "/users", element: <UsersPage /> }],
          },
          {
            element: <RequirePermission permission="roles.view" />,
            children: [{ path: "/roles", element: <RolesPage /> }],
          },
        ],
      },
    ],
  },
])

export default router