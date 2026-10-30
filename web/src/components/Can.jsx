import { usePermission } from "@/hooks/usePermission"

// <Can permission="users.delete"><DeleteButton /></Can>
export default function Can({ permission, children, fallback = null }) {
  const { can } = usePermission()
  return can(permission) ? children : fallback
}