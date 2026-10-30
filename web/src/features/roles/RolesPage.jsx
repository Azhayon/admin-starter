import CrudPage from "@/components/crud/CrudPage"
import { useResourceList } from "@/hooks/useResource"
import { ME_KEY } from "@/lib/queryKeys"
import { roleColumns } from "./columns"
import { getRoleFields } from "./schema"

export default function RolesPage() {
  const { data } = useResourceList("permissions", { per_page: 100, sort: "name" })

  // "users.update" -> { label: "update", group: "users" }: the groups form the permission matrix
  const permissionOptions = (data?.data ?? []).map((p) => {
    const [group, action] = p.name.split(".")
    return { value: p.name, label: action, group }
  })

  return (
    <CrudPage
      resource="roles"
      singular="role"
      title="Roles"
      permission="roles"
      columns={roleColumns}
      fields={({ isEdit }) => getRoleFields({ isEdit, permissionOptions })}
      canEdit={(role) => !role.is_protected}
      canDelete={(role) => !role.is_protected}
      mutationOptions={{ alsoInvalidate: [ME_KEY, ["users"]] }}
    />
  )
}