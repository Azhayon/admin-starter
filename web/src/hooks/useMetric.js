import { useQuery } from "@tanstack/react-query"
import api from "@/lib/api"

export function useMetric(name, params = {}) {
  return useQuery({
    queryKey: ["metrics", name, params],
    queryFn: async () => (await api.get(`/api/v1/metrics/${name}`, { params })).data.data,
    staleTime: 60_000, // a dashboard doesn't need a refetch on every visit; mutations invalidate it when data changes
  })
}