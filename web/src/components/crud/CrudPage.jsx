import { useEffect, useState } from "react"
import ConfirmDialog from "@/components/ConfirmDialog"
import PageHeader from "@/components/PageHeader"
import DataTable from "@/components/data-table/DataTable"
import ResourceForm from "@/components/form/ResourceForm"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { useDebouncedValue } from "@/hooks/useDebouncedValue"
import { usePermission } from "@/hooks/usePermission"
import { useResourceList, useResourceMutations } from "@/hooks/useResource"
import { getErrorMessage } from "@/lib/errors"

/**
 * resource:   API path segment ("users")        singular: "user"      title: "Users"
 * permission: RBAC prefix ("users" -> users.create / users.update / users.delete)
 * columns:    DataTable columns (an Actions column is added for you)
 * fields:     ({ isEdit, record }) => field configs for ResourceForm. Omit it for read-only pages
 *             (no create/edit dialog is rendered).
 * canEdit / canDelete: (record) => boolean, for per-row rules (protected roles, "not yourself", ...)
 */
export default function CrudPage({
  resource, singular, title, permission, columns, fields,
  canEdit = () => true, canDelete = () => true, mutationOptions, rowActions,
}) {
  const { can } = usePermission()

  // List state: lives here, and is sent to the API as query-string params.
  const [page, setPage] = useState(1)
  const [sort, setSort] = useState(undefined)
  const [searchInput, setSearchInput] = useState("")
  const search = useDebouncedValue(searchInput)

  // Dialog state
  const [formOpen, setFormOpen] = useState(false)
  const [editingRecord, setEditingRecord] = useState(null) // null = "creating"
  const [deleting, setDeleting] = useState(null)
  const [deleteError, setDeleteError] = useState(null)

  const { data, isLoading, isFetching, isError, error } = useResourceList(resource, {
    page,
    per_page: 10,
    sort,
    search: search || undefined, // undefined params are left out of the URL
  })
  const { create, update, remove } = useResourceMutations(resource, mutationOptions)

  // If the last row of the last page was deleted, step back to a page that still exists.
  const lastPage = data?.meta?.last_page
  useEffect(() => {
    if (lastPage && page > lastPage) setPage(lastPage)
  }, [lastPage, page])

  const openCreate = () => {
    setEditingRecord(null)
    setFormOpen(true)
  }
  const openEdit = (record) => {
    setEditingRecord(record)
    setFormOpen(true)
  }

  const handleSubmit = async (values) => {
    // If this throws, ResourceForm catches it and shows the errors, and the dialog stays open.
    if (editingRecord) await update.mutateAsync({ id: editingRecord.id, values })
    else await create.mutateAsync(values)
    setFormOpen(false)
  }

  const handleDelete = async () => {
    try {
      await remove.mutateAsync(deleting.id)
      setDeleting(null)
    } catch (err) {
      setDeleteError(getErrorMessage(err))
    }
  }

  const allColumns = [
    ...columns,
    {
      key: "actions",
      header: "",
      className: "text-right",
      cell: (row) => (
        <div className="flex justify-end gap-2">
          {rowActions?.(row)}
          {can(`${permission}.update`) && canEdit(row) && (
            <Button variant="outline" size="sm" onClick={() => openEdit(row)}>Edit</Button>
          )}
          {can(`${permission}.delete`) && canDelete(row) && (
            <Button
              variant="destructive"
              size="sm"
              onClick={() => { setDeleteError(null); setDeleting(row) }}
            >
              Delete
            </Button>
          )}
        </div>
      ),
    },
  ]

  return (
    <div>
      <PageHeader title={title}>
        {can(`${permission}.create`) && <Button onClick={openCreate}>New {singular}</Button>}
      </PageHeader>

      <div className="mb-4">
        <Input
          className="max-w-sm"
          placeholder="Search…"
          value={searchInput}
          onChange={(e) => { setSearchInput(e.target.value); setPage(1) }}
        />
      </div>

      {isError ? (
        <p className="text-sm text-destructive">{getErrorMessage(error)}</p>
      ) : (
        <DataTable
          columns={allColumns}
          rows={data?.data}
          meta={data?.meta}
          isLoading={isLoading}
          isFetching={isFetching}
          sort={sort}
          onSortChange={(next) => { setSort(next); setPage(1) }}
          onPageChange={setPage}
        />
      )}

      {fields && (
        <Dialog open={formOpen} onOpenChange={setFormOpen}>
          <DialogContent className="max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>{editingRecord ? `Edit ${singular}` : `New ${singular}`}</DialogTitle>
              <DialogDescription>Fill in the details below.</DialogDescription>
            </DialogHeader>
            <ResourceForm
              key={editingRecord?.id ?? "new"}
              fields={fields({ isEdit: !!editingRecord, record: editingRecord })}
              record={editingRecord}
              onSubmit={handleSubmit}
              onCancel={() => setFormOpen(false)}
            />
          </DialogContent>
        </Dialog>
      )}

      <ConfirmDialog
        open={!!deleting}
        onOpenChange={(open) => !open && setDeleting(null)}
        title={`Delete ${singular}?`}
        description={`"${deleting?.name}" will be permanently deleted.`}
        confirmLabel="Delete"
        loading={remove.isPending}
        error={deleteError}
        onConfirm={handleDelete}
      />
    </div>
  )
}