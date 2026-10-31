import { useState } from "react"
import CrudPage from "@/components/crud/CrudPage"
import { Button } from "@/components/ui/button"
import AuditDetailsDialog from "./AuditDetailsDialog"
import { auditColumns } from "./columns"

export default function AuditLogsPage() {
  const [selected, setSelected] = useState(null)

  // <> ... </> is a Fragment: it lets a component return two siblings without an extra wrapper <div>.
  return (
    <>
      <CrudPage
        resource="audit-logs"
        singular="log entry"
        title="Audit log"
        permission="audit-logs"
        columns={auditColumns}
        rowActions={(log) => (
          <Button variant="outline" size="sm" onClick={() => setSelected(log)}>
            Details
          </Button>
        )}
      />
      <AuditDetailsDialog log={selected} onClose={() => setSelected(null)} />
    </>
  )
}