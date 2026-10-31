import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { getErrorMessage } from "@/lib/errors"

/**
 * query:    the result of useMetric()
 * children: a FUNCTION that receives the loaded data and returns JSX (a "render prop"),
 *           so this component can handle loading/error without knowing what the data looks like.
 */
export default function WidgetCard({ title, description, query, className, children }) {
  return (
    <Card className={className}>
      <CardHeader>
        <CardTitle className="text-sm font-medium text-muted-foreground">{title}</CardTitle>
        {description && <CardDescription>{description}</CardDescription>}
      </CardHeader>
      <CardContent>
        {query.isPending ? (
          <p className="text-sm text-muted-foreground">Loading…</p>
        ) : query.isError ? (
          <p className="text-sm text-destructive">{getErrorMessage(query.error)}</p>
        ) : (
          children(query.data)
        )}
      </CardContent>
    </Card>
  )
}