import { useMemo } from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { AuthContext } from "./auth-context"
import { ME_KEY, fetchMe, login as loginRequest, logout as logoutRequest } from "./authApi"

export function AuthProvider({ children }) {
  const queryClient = useQueryClient()

  // The cached "me" query IS the auth state. No separate useState to keep in sync.
  const { data: user, isPending } = useQuery({
    queryKey: ME_KEY,
    queryFn: fetchMe,
    staleTime: Infinity, // identity doesn't change on window focus; we update it explicitly
    retry: false,
  })

  const { mutateAsync: login } = useMutation({
    mutationFn: async (credentials) => {
      await loginRequest(credentials)
      return fetchMe()
    },
    onSuccess: (me) => queryClient.setQueryData(ME_KEY, me),
  })

  const { mutateAsync: logout } = useMutation({
    mutationFn: logoutRequest,
    onSuccess: () => {
      queryClient.setQueryData(ME_KEY, null)
      // Drop everything else cached so the next user never sees the previous user's data.
      queryClient.removeQueries({ predicate: (q) => q.queryKey[0] !== "auth" })
    },
  })

  const value = useMemo(
    () => ({ user: user ?? null, isLoading: isPending, isAuthenticated: !!user, login, logout }),
    [user, isPending, login, logout]
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}