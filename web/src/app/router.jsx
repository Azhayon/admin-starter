import { createBrowserRouter } from "react-router-dom"
import AdminLayout from "./layouts/AdminLayout"
import { Button } from "@/components/ui/button"

// createBrowserRouter = "data router": the whole route table is one plain object,
// which is what makes a single router.jsx possible. A route with no `path`
// (the layout below) just wraps its children.
const router = createBrowserRouter([
  {
    element: <AdminLayout />,
    children: [
      {
        path: "/",
        element: (
          <div className="space-y-4">
            <h1 className="text-2xl font-bold">admin-starter</h1>
            <Button>shadcn works</Button>
          </div>
        ),
      },
    ],
  },
])

export default router