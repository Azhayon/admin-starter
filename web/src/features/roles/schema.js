export function getRoleFields({ isEdit, permissionOptions }) {
  return [
    // A role's machine name is immutable, so it's only asked for on create.
    ...(isEdit ? [] : [{ name: "name", label: "Name (lowercase, e.g. support-agent)", type: "text", required: true }]),
    { name: "label", label: "Display name", type: "text", required: true },
    { name: "permissions", label: "Permissions", type: "checkbox-group", options: permissionOptions },
  ]
}