export function getUserFields({ isEdit, roleOptions, canAssignRoles }) {
  return [
    { name: "name", label: "Name", type: "text", required: true },
    { name: "email", label: "Email", type: "email", required: true },
    {
      name: "password",
      label: isEdit ? "New password (leave blank to keep the current one)" : "Password",
      type: "password",
      required: !isEdit,
    },
    // Only offered to people allowed to assign roles; the API enforces this too.
    ...(canAssignRoles ? [{ name: "roles", label: "Roles", type: "checkbox-group", options: roleOptions }] : []),
  ]
}