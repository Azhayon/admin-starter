import CrudPage from "@/components/crud/CrudPage"
import { useAuth } from "@/hooks/useAuth"
import { usePermission } from "@/hooks/usePermission"
import { useResourceList } from "@/hooks/useResource"
import { ME_KEY } from "@/lib/queryKeys"
import { userColumns } from "./columns"
import { getUserFields } from "./schema"

export default function UsersPage() {
  const { user: me } = useAuth()
  const { can } = usePermission()
  const canAssign = can("users.assign-roles")

  // Hooks can't be conditional, but a query can be switched off with `enabled`.
  const { data: rolesData } = useResourceList("roles", { per_page: 100 }, { enabled: canAssign })
  const roleOptions = (rolesData?.data ?? []).map((role) => ({ value: role.name, label: role.label }))

  return (
    <CrudPage
      resource="users"
      singular="user"
      title="Users"
      permission="users"
      columns={userColumns}
      fields={({ isEdit, record }) =>
        getUserFields({
          isEdit,
          roleOptions,
          canAssignRoles: canAssign && record?.id !== me.id, // nobody edits their own roles
        })
      }
      canDelete={(user) => user.id !== me.id}
      mutationOptions={{ alsoInvalidate: [ME_KEY, ["roles"]] }} // refresh my permissions + role user-counts
    />
  )
}