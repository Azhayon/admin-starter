import { Outlet } from "react-router-dom"
import { usePermission } from "@/hooks/usePermission"

export default function RequirePermission({ permission }) {
  const { can } = usePermission()

  if (!can(permission)) {
    return (
      <div>
        <h1 className="text-xl font-bold">403</h1>
        <p className="text-muted-foreground">You don't have permission to view this page.</p>
      </div>
    )
  }
  return <Outlet />
}