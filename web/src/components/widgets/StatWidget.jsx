import { useMetric } from "@/hooks/useMetric"
import WidgetCard from "./WidgetCard"

const TONE = { up: "text-emerald-600", down: "text-red-600", flat: "text-muted-foreground" }

function trend(value, previous) {
  if (previous === undefined || previous === null) return null
  if (previous === 0) return value === 0 ? { text: "no change", tone: "flat" } : { text: "new activity", tone: "up" }

  const pct = Math.round(((value - previous) / previous) * 100)
  return {
    text: `${pct > 0 ? "+" : ""}${pct}% vs previous period`,
    tone: pct > 0 ? "up" : pct < 0 ? "down" : "flat",
  }
}

// shape: { value, previous? }
export default function StatWidget({ metric, title, description, params, className }) {
  const query = useMetric(metric, params)

  return (
    <WidgetCard title={title} description={description} query={query} className={className}>
      {({ value, previous }) => {
        const change = trend(value, previous)
        return (
          <div>
            <p className="text-3xl font-bold">{value.toLocaleString()}</p>
            {change && <p className={`text-xs ${TONE[change.tone]}`}>{change.text}</p>}
          </div>
        )
      }}
    </WidgetCard>
  )
}