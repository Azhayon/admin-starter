import { QueryClient } from "@tanstack/react-query"

// Exported (instead of created inside providers.jsx) so non-React code, like the axios interceptor, can use it.
export const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: false } },
})