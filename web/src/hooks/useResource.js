import { keepPreviousData, useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import api from "@/lib/api"

const base = (resource) => `/api/v1/${resource}`

// List: GET /api/v1/{resource}?page=&per_page=&sort=&search=&filter[x]=
export function useResourceList(resource, params = {}, options = {}) {
  return useQuery({
    // The key includes `params`, so a new page/sort/search = a new cache entry and an automatic refetch.
    queryKey: [resource, "list", params],
    queryFn: async () => (await api.get(base(resource), { params })).data, // { data, links, meta }
    placeholderData: keepPreviousData, // keep showing the old page while the next one loads
    ...options,
  })
}

// Create / update / delete. Each one refreshes every cached list of this resource afterwards.
export function useResourceMutations(resource, { alsoInvalidate = [] } = {}) {
  const queryClient = useQueryClient()

  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: [resource] })
    queryClient.invalidateQueries({ queryKey: ["metrics"] }) // dashboard numbers depend on this data
    alsoInvalidate.forEach((queryKey) => queryClient.invalidateQueries({ queryKey }))
  }

  const create = useMutation({
    mutationFn: async (values) => (await api.post(base(resource), values)).data.data,
    onSuccess: invalidate,
  })

  const update = useMutation({
    mutationFn: async ({ id, values }) => (await api.patch(`${base(resource)}/${id}`, values)).data.data,
    onSuccess: invalidate,
  })

  const remove = useMutation({
    mutationFn: async (id) => api.delete(`${base(resource)}/${id}`),
    onSuccess: invalidate,
  })

  return { create, update, remove }
}