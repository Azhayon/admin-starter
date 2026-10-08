import { useAuth } from "@/hooks/useAuth"

export default function DashboardPage() {
  const { user } = useAuth()
  return <h1 className="text-2xl font-bold">Welcome, {user.name}</h1>
}