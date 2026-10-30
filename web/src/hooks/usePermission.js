import { useAuth } from "@/hooks/useAuth"

export function usePermission() {
  const { user } = useAuth()
  const permissions = user?.permissions ?? []

  return {
    can: (permission) => permissions.includes(permission),
    canAny: (...list) => list.some((p) => permissions.includes(p)),
    hasRole: (role) => user?.roles?.includes(role) ?? false,
  }
}