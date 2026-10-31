export const navigation = [
  { label: "Dashboard", to: "/" },
  { label: "Users", to: "/users", permission: "users.view" },
  { label: "Roles", to: "/roles", permission: "roles.view" },
  { label: "Audit log", to: "/audit-log", permission: "audit-logs.view" },
]