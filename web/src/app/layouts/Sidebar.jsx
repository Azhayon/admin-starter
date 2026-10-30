import { NavLink } from "react-router-dom"
import { navigation } from "@/config/navigation"
import { usePermission } from "@/hooks/usePermission"

export default function Sidebar() {
  const { can } = usePermission()
  const items = navigation.filter((item) => !item.permission || can(item.permission))

  return (
    <nav className="w-56 shrink-0 space-y-1 border-r p-4">
      {items.map((item) => (
        <NavLink
          key={item.to}
          to={item.to}
          end={item.to === "/"}
          className={({ isActive }) =>
            `block rounded px-3 py-2 text-sm ${isActive ? "bg-muted font-medium" : "hover:bg-muted/50"}`
          }
        >
          {item.label}
        </NavLink>
      ))}
    </nav>
  )
}