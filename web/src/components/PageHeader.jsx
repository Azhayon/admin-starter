export default function PageHeader({ title, children }) {
  return (
    <div className="mb-6 flex items-center justify-between">
      <h1 className="text-2xl font-bold">{title}</h1>
      <div className="flex items-center gap-2">{children}</div>
    </div>
  )
}