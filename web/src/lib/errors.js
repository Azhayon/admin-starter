export function getErrorMessage(error) {
  const errors = error.response?.data?.errors // Laravel validation: { field: ["msg"] }
  if (errors) return Object.values(errors)[0][0]
  return error.response?.data?.message ?? "Something went wrong. Please try again."
}