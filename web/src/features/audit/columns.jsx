import { Badge } from "@/components/ui/badge"

const EVENT_VARIANT = { created: "default", deleted: "destructive" }

export const auditColumns = [
  { key: "created_at", header: "When", sortable: true, cell: (log) => new Date(log.created_at).toLocaleString() },
  {
    key: "user",
    header: "Who",
    cell: (log) => log.user?.name ?? <span className="text-muted-foreground">system</span>,
  },
  {
    key: "event",
    header: "Event",
    sortable: true,
    cell: (log) => (
      <Badge variant={EVENT_VARIANT[log.event] ?? "secondary"}>{log.event.replaceAll("_", " ")}</Badge>
    ),
  },
  {
    key: "auditable_type",
    header: "Record",
    sortable: true,
    cell: (log) => `${log.auditable_type}: ${log.auditable_label ?? `#${log.auditable_id}`}`,
  },
]