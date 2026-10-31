# Database schema

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp created_at
    }
    roles {
        bigint id PK
        string name UK
        string label
    }
    permissions {
        bigint id PK
        string name UK
    }
    role_user {
        bigint role_id PK, FK
        bigint user_id PK, FK
    }
    permission_role {
        bigint permission_id PK, FK
        bigint role_id PK, FK
    }
    audit_logs {
        bigint id PK
        bigint user_id FK "nullable: the actor"
        string event
        string auditable_type "polymorphic: user | role"
        bigint auditable_id
        string auditable_label
        json old_values
        json new_values
        string ip_address
        timestamp created_at
    }

    users ||--o{ role_user : "has roles"
    roles ||--o{ role_user : "assigned to"
    roles ||--o{ permission_role : "grants"
    permissions ||--o{ permission_role : "granted by"
    users |o--o{ audit_logs : "performs"
```

Notes: `audit_logs.auditable_*` points at a `users` or `roles` row (polymorphic, so it has no foreign key).
Framework tables (`sessions`, `cache`, `jobs`, `migrations`) are omitted.