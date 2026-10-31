import { createBrowserRouter } from "react-router-dom"
import AdminLayout from "./layouts/AdminLayout"
import AuthLayout from "./layouts/AuthLayout"
import RequireAuth from "@/features/auth/RequireAuth"
import LoginPage from "@/features/auth/LoginPage"
import RequirePermission from "@/features/auth/RequirePermission"
import UsersPage from "@/features/users/UsersPage"
import RolesPage from "@/features/roles/RolesPage"
import AuditLogsPage from "@/features/audit/AuditLogsPage"

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
          {
            path: "/",
            lazy: async () => {
              // Loaded as a separate JS file the first time someone visits "/"
              const { default: Component } = await import("@/features/dashboard/DashboardPage")
              return { Component }
            },
          },
          {
            element: <RequirePermission permission="users.view" />,
            children: [{ path: "/users", element: <UsersPage /> }],
          },
          {
            element: <RequirePermission permission="roles.view" />,
            children: [{ path: "/roles", element: <RolesPage /> }],
          },
          {
            element: <RequirePermission permission="audit-logs.view" />,
            children: [{ path: "/audit-log", element: <AuditLogsPage /> }],
          },
        ],
      },
    ],
  },
])

export default router