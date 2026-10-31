import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts"
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart"
import { useMetric } from "@/hooks/useMetric"
import WidgetCard from "./WidgetCard"

// shape: { items: [{ label, value }] }
export default function DistributionWidget({ metric, title, description, params, className }) {
  const query = useMetric(metric, params)
  const config = { value: { label: title, color: "var(--chart-2)" } }

  return (
    <WidgetCard title={title} description={description} query={query} className={className}>
      {({ items }) => (
        <ChartContainer config={config} className="h-56 w-full">
          <BarChart data={items}>
            <CartesianGrid vertical={false} />
            <XAxis dataKey="label" tickLine={false} axisLine={false} tickMargin={8} />
            <YAxis allowDecimals={false} width={28} tickLine={false} axisLine={false} />
            <ChartTooltip content={<ChartTooltipContent />} />
            <Bar dataKey="value" fill="var(--color-value)" radius={4} />
          </BarChart>
        </ChartContainer>
      )}
    </WidgetCard>
  )
}