import { useAuth } from "@/hooks/useAuth"
import Can from "@/components/Can"

export default function DashboardPage() {
  const { user } = useAuth()
  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-bold">Welcome, {user.name}</h1>
      <p className="text-sm text-muted-foreground">Roles: {user.roles.join(", ")}</p>
      <Can permission="users.delete">
        <p className="text-sm">You can delete users.</p>
      </Can>
    </div>
  )
}