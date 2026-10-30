import { Badge } from "@/components/ui/badge"

export const roleColumns = [
  {
    key: "label",
    header: "Role",
    sortable: true,
    cell: (role) => (
      <div className="flex items-center gap-2">
        {role.label}
        {role.is_protected && <Badge variant="outline">protected</Badge>}
      </div>
    ),
  },
  { key: "name", header: "Name", sortable: true },
  { key: "permissions", header: "Permissions", cell: (role) => `${role.permissions.length} granted` },
  { key: "users_count", header: "Users" },
]