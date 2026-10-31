import DistributionWidget from "@/components/widgets/DistributionWidget"
import StatWidget from "@/components/widgets/StatWidget"
import TrendWidget from "@/components/widgets/TrendWidget"
import { dashboardWidgets } from "@/config/dashboard"
import { useAuth } from "@/hooks/useAuth"
import { usePermission } from "@/hooks/usePermission"

// Map from a config "type" to the component that renders it.
const COMPONENTS = { stat: StatWidget, trend: TrendWidget, distribution: DistributionWidget }

function renderWidget(widget) {
  const Component = COMPONENTS[widget.type] // JSX needs a capitalised name, hence the local variable
  return (
    <Component
      key={widget.id}
      metric={widget.metric}
      title={widget.title}
      description={widget.description}
      params={widget.params}
      className={widget.span === 2 ? "md:col-span-2" : undefined}
    />
  )
}

export default function DashboardPage() {
  const { user } = useAuth()
  const { can } = usePermission()

  const visible = dashboardWidgets.filter((widget) => can(widget.permission))
  const stats = visible.filter((widget) => widget.type === "stat")
  const charts = visible.filter((widget) => widget.type !== "stat")

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Welcome, {user.name}</h1>
        <p className="text-sm text-muted-foreground">Roles: {user.roles.join(", ") || "none"}</p>
      </div>

      {visible.length === 0 && (
        <p className="text-sm text-muted-foreground">There's nothing to show for your account yet.</p>
      )}
      {stats.length > 0 && <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">{stats.map(renderWidget)}</div>}
      {charts.length > 0 && <div className="grid gap-4 md:grid-cols-3">{charts.map(renderWidget)}</div>}
    </div>
  )
}