// type: "stat" | "trend" | "distribution" (the three response shapes)
// permission: the widget is hidden from users who don't have it
export const dashboardWidgets = [
  { id: "total-users", type: "stat", metric: "total-users", title: "Total users", permission: "users.view" },
  {
    id: "new-users", type: "stat", metric: "new-users", title: "New users",
    description: "Last 30 days", params: { days: 30 }, permission: "users.view",
  },
  {
    id: "audit-events", type: "stat", metric: "audit-events", title: "Audit events",
    description: "Last 7 days", params: { days: 7 }, permission: "audit-logs.view",
  },
  { id: "users-by-role", type: "distribution", metric: "users-by-role", title: "Users by role", permission: "users.view" },
  {
    id: "audit-activity", type: "trend", metric: "audit-activity", title: "Activity",
    description: "Audit events per day, last 14 days", params: { days: 14 }, permission: "audit-logs.view", span: 2,
  },
]