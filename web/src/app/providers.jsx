import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { AuthProvider } from "@/features/auth/AuthContext"

const queryClient = new QueryClient()

// One place for every global provider. AuthProvider gets added here later.
export default function Providers({ children }) {
   return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>{children}</AuthProvider>
    </QueryClientProvider>
  )
}