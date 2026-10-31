import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

const show = (value) => {
  if (value === undefined || value === null) return "—"
  if (Array.isArray(value)) return value.join(", ") || "—"
  return typeof value === "object" ? JSON.stringify(value) : String(value)
}

export default function AuditDetailsDialog({ log, onClose }) {
  // Every field that appears in either the old or the new values, once each.
  const fields = [...new Set([...Object.keys(log?.old_values ?? {}), ...Object.keys(log?.new_values ?? {})])]

  return (
    <Dialog open={!!log} onOpenChange={(open) => !open && onClose()}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>
            {log?.event.replaceAll("_", " ")} · {log?.auditable_type}
          </DialogTitle>
          <DialogDescription>
            {log &&
              `${log.auditable_label ?? `#${log.auditable_id}`} — by ${log.user?.name ?? "system"}` +
                (log.ip_address ? ` from ${log.ip_address}` : "")}
          </DialogDescription>
        </DialogHeader>

        {fields.length === 0 ? (
          <p className="text-sm text-muted-foreground">No field changes recorded.</p>
        ) : (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Field</TableHead>
                <TableHead>Before</TableHead>
                <TableHead>After</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {fields.map((field) => (
                <TableRow key={field}>
                  <TableCell className="font-medium">{field}</TableCell>
                  <TableCell>{show(log.old_values?.[field])}</TableCell>
                  <TableCell>{show(log.new_values?.[field])}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        )}
      </DialogContent>
    </Dialog>
  )
}