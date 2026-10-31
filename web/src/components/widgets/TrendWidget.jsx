import { Area, AreaChart, CartesianGrid, XAxis } from "recharts"
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart"
import { useMetric } from "@/hooks/useMetric"
import WidgetCard from "./WidgetCard"

// "2026-10-31" -> "Oct 31". Adding T00:00:00 makes the browser read it as LOCAL time, so the day never shifts.
const formatDay = (date) =>
  new Date(`${date}T00:00:00`).toLocaleDateString(undefined, { month: "short", day: "numeric" })

// shape: { points: [{ date, value }] }
export default function TrendWidget({ metric, title, description, params, className }) {
  const query = useMetric(metric, params)

  // The key ("value") becomes the CSS variable --color-value that the chart parts read.
  const config = { value: { label: title, color: "var(--chart-1)" } }

  return (
    <WidgetCard title={title} description={description} query={query} className={className}>
      {({ points }) => (
        <ChartContainer config={config} className="h-56 w-full">
          <AreaChart data={points} margin={{ left: 12, right: 12 }}>
            <CartesianGrid vertical={false} />
            <XAxis dataKey="date" tickLine={false} axisLine={false} tickMargin={8} tickFormatter={formatDay} />
            <ChartTooltip content={<ChartTooltipContent labelFormatter={formatDay} />} />
            <Area dataKey="value" type="monotone" stroke="var(--color-value)" fill="var(--color-value)" fillOpacity={0.2} />
          </AreaChart>
        </ChartContainer>
      )}
    </WidgetCard>
  )
}