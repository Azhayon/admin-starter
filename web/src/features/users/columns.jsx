import { Badge } from "@/components/ui/badge"

export const userColumns = [
  { key: "name", header: "Name", sortable: true },
  { key: "email", header: "Email", sortable: true },
  {
    key: "roles",
    header: "Roles",
    cell: (user) => (
      <div className="flex flex-wrap gap-1">
        {user.roles.map((role) => <Badge key={role} variant="secondary">{role}</Badge>)}
      </div>
    ),
  },
  { key: "created_at", header: "Created", sortable: true, cell: (u) => new Date(u.created_at).toLocaleDateString() },
]