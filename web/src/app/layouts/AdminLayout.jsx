import { Outlet } from "react-router-dom"

// Layout route: renders the persistent shell. <Outlet /> is the slot where
// whichever child route matched gets rendered. Sidebar + topbar go here later.
export default function AdminLayout() {
  return (
    <div className="min-h-screen">
      <header className="border-b px-6 py-3 font-semibold">Admin</header>
      <main className="p-6">
        <Outlet />
      </main>
    </div>
  )
}