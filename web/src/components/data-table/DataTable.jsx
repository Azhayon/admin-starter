import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

const ARROWS = { asc: "▲", desc: "▼" }

/**
 * columns: [{ key, header, sortable?, cell?: (row) => node, className? }]
 * sort:    "name" (asc) | "-name" (desc) | undefined  -- the same format the API's ?sort= uses
 * meta:    Laravel pagination meta { current_page, last_page, total }
 */
export default function DataTable({
  columns, rows = [], meta, isLoading, isFetching, sort, onSortChange, onPageChange, emptyMessage = "No results.",
}) {
  const sortState = (key) => (sort === key ? "asc" : sort === `-${key}` ? "desc" : null)

  // Click cycle: none -> ascending -> descending -> none
  const toggleSort = (key) => {
    const state = sortState(key)
    onSortChange(state === null ? key : state === "asc" ? `-${key}` : undefined)
  }

  return (
    <div className="space-y-3">
      <div className={`rounded-md border ${isFetching ? "opacity-60" : ""}`}>
        <Table>
          <TableHeader>
            <TableRow>
              {columns.map((col) => (
                <TableHead key={col.key} className={col.className}>
                  {col.sortable && onSortChange ? (
                    <button
                      type="button"
                      className="inline-flex items-center gap-1 font-medium"
                      onClick={() => toggleSort(col.key)}
                    >
                      {col.header}
                      <span className="text-xs">{ARROWS[sortState(col.key)]}</span>
                    </button>
                  ) : (
                    col.header
                  )}
                </TableHead>
              ))}
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading ? (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center">Loading…</TableCell>
              </TableRow>
            ) : rows.length === 0 ? (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center text-muted-foreground">
                  {emptyMessage}
                </TableCell>
              </TableRow>
            ) : (
              rows.map((row) => (
                <TableRow key={row.id}>
                  {columns.map((col) => (
                    <TableCell key={col.key} className={col.className}>
                      {col.cell ? col.cell(row) : row[col.key]}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>

      {meta && (
        <div className="flex items-center justify-between text-sm text-muted-foreground">
          <span>{meta.total} total</span>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" disabled={meta.current_page <= 1}
                    onClick={() => onPageChange(meta.current_page - 1)}>
              Previous
            </Button>
            <span>Page {meta.current_page} of {meta.last_page}</span>
            <Button variant="outline" size="sm" disabled={meta.current_page >= meta.last_page}
                    onClick={() => onPageChange(meta.current_page + 1)}>
              Next
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}