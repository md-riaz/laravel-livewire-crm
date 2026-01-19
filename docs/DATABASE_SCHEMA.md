# Laravel Livewire CRM - Database Schema Documentation

## Table of Contents
1. [Schema Overview](#schema-overview)
2. [Table Descriptions](#table-descriptions)
3. [Relationships](#relationships)
4. [Indexes](#indexes)
5. [Sample Queries](#sample-queries)
6. [Migration Order](#migration-order)

---

## Schema Overview

### Entity Relationship Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                        MULTI-TENANT CRM SCHEMA                    │
└──────────────────────────────────────────────────────────────────┘

┌─────────────┐
│  tenants    │
│─────────────│
│ id (PK)     │◄───────────────────────┐
│ name        │                        │
│ status      │                        │ (tenant_id FK)
│ timezone    │                        │
│ timestamps  │                        │
└─────────────┘                        │
                                       │
┌─────────────────────┐                │
│  users              │                │
│─────────────────────│                │
│ id (PK)             │◄───────────────┤
│ tenant_id (FK)      ├────────────────┘
│ name                │
│ email (UNIQUE)      │
│ password            │
│ role (ENUM)         │
│ is_active           │
│ timestamps          │
└──────┬──────────────┘
       │
       │ (user_id FK)
       │
       ├──────────────────────────────────┐
       │                                  │
       ▼                                  ▼
┌──────────────────┐             ┌─────────────────┐
│  permissions     │             │ agent_sip_creds │
│──────────────────│             │─────────────────│
│ id (PK)          │             │ id (PK)         │
│ tenant_id (FK)   │             │ tenant_id (FK)  │
│ role (ENUM)      │             │ user_id (FK)    │
│ permission       │             │ sip_ws_url      │
│ timestamps       │             │ sip_username    │
└──────────────────┘             │ sip_password    │
                                 │ sip_domain      │
                                 │ display_name    │
                                 │ auto_register   │
                                 │ timestamps      │
                                 └─────────────────┘

┌──────────────────┐
│  lead_statuses   │
│──────────────────│
│ id (PK)          │◄───────────────────┐
│ tenant_id (FK)   │                    │
│ name             │                    │ (lead_status_id FK)
│ color            │                    │
│ sort_order       │                    │
│ is_default       │                    │
│ is_closed        │                    │
│ is_won           │                    │
│ is_lost          │                    │
│ requires_note    │                    │
│ requires_fup     │                    │
│ timestamps       │                    │
└──────────────────┘                    │
                                        │
┌──────────────────────────────┐        │
│  leads                       │        │
│──────────────────────────────│        │
│ id (PK)                      │        │
│ tenant_id (FK)               │        │
│ lead_status_id (FK)          ├────────┘
│ assigned_to_user_id (FK)     │
│ created_by_user_id (FK)      │
│ name                         │
│ company_name                 │
│ email                        │
│ phone                        │
│ source                       │
│ score (ENUM)                 │
│ estimated_value              │
│ last_contacted_at            │
│ next_followup_at             │
│ timestamps                   │
└──────────┬───────────────────┘
           │
           │ (lead_id FK)
           │
           ▼
┌──────────────────┐
│ lead_activities  │
│──────────────────│
│ id (PK)          │
│ tenant_id (FK)   │
│ lead_id (FK)     │
│ user_id (FK)     │
│ type (ENUM)      │
│ payload_json     │
│ created_at       │
└──────────────────┘

┌────────────────────┐
│ call_dispositions  │
│────────────────────│
│ id (PK)            │◄───────────────┐
│ tenant_id (FK)     │                │
│ name               │                │ (disposition_id FK)
│ sort_order         │                │
│ is_default         │                │
│ requires_note      │                │
│ timestamps         │                │
└────────────────────┘                │
                                      │
┌──────────────────────────────┐      │
│  calls                       │      │
│──────────────────────────────│      │
│ id (PK)                      │      │
│ tenant_id (FK)               │      │
│ user_id (FK)                 │      │
│ related_type                 │      │ (polymorphic)
│ related_id                   │      │
│ direction (ENUM)             │      │
│ from_number                  │      │
│ to_number                    │      │
│ started_at                   │      │
│ ended_at                     │      │
│ duration_seconds             │      │
│ pbx_call_id                  │      │
│ recording_url                │      │
│ disposition_id (FK)          ├──────┘
│ wrapup_notes                 │
│ timestamps                   │
└──────────────────────────────┘

┌──────────────────┐
│  audit_logs      │
│──────────────────│
│ id (PK)          │
│ tenant_id (FK)   │
│ user_id (FK)     │
│ action           │
│ entity_type      │
│ entity_id        │
│ ip_address       │
│ user_agent       │
│ created_at       │
└──────────────────┘
```

---

## Table Descriptions

### tenants

**Purpose**: Company/tenant information for multi-tenant isolation.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NO | - | Company name |
| status | ENUM | NO | 'active' | Tenant lifecycle state |
| timezone | VARCHAR(255) | NO | 'UTC' | Default timezone |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Status Values**: `active`, `suspended`, `closed`

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_status (status)

---

### users

**Purpose**: User accounts with tenant relationship and role-based access.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| name | VARCHAR(255) | NO | - | User's full name |
| email | VARCHAR(255) | NO | - | Unique email address |
| email_verified_at | TIMESTAMP | YES | NULL | Email verification timestamp |
| password | VARCHAR(255) | NO | - | Hashed password |
| role | ENUM | NO | - | User role within tenant |
| is_active | BOOLEAN | NO | TRUE | Account active status |
| remember_token | VARCHAR(100) | YES | NULL | Remember me token |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Role Values**: `tenant_admin`, `sales_agent`, `support_agent`, `supervisor`, `read_only`

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE KEY users_email_unique (email)
- INDEX idx_tenant_active (tenant_id, is_active)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE

---

### permissions

**Purpose**: Granular role-based permissions per tenant.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| role | ENUM | NO | - | Role this permission applies to |
| permission | VARCHAR(255) | NO | - | Permission string (e.g., 'leads.view') |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Permission Examples**:
- `leads.view`
- `leads.manage`
- `calls.make`
- `calls.view`
- `calls.recordings.view`
- `users.manage`
- `settings.manage`

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE KEY unique_permission (tenant_id, role, permission)
- INDEX idx_tenant (tenant_id)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE

---

### lead_statuses

**Purpose**: Dynamic, tenant-configurable lead pipeline statuses.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| name | VARCHAR(255) | NO | - | Status name (e.g., 'New', 'Qualified') |
| color | VARCHAR(7) | NO | '#3B82F6' | Hex color for UI |
| sort_order | INT | NO | 0 | Display order in Kanban |
| is_default | BOOLEAN | NO | FALSE | Default status for new leads |
| is_closed | BOOLEAN | NO | FALSE | Status represents closed lead |
| is_won | BOOLEAN | NO | FALSE | Status represents won deal |
| is_lost | BOOLEAN | NO | FALSE | Status represents lost deal |
| requires_note | BOOLEAN | NO | FALSE | Moving to this status requires note |
| requires_followup_date | BOOLEAN | NO | FALSE | Moving to this status requires follow-up |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Business Rules**:
- Exactly one `is_default = TRUE` per tenant
- `is_won` and `is_lost` are mutually exclusive
- Cannot delete status with associated leads

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_tenant_sort (tenant_id, sort_order)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE

---

### leads

**Purpose**: Lead records with full CRM data.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| lead_status_id | BIGINT | NO | - | Current status |
| assigned_to_user_id | BIGINT | YES | NULL | Assigned user |
| created_by_user_id | BIGINT | YES | NULL | User who created lead |
| name | VARCHAR(255) | NO | - | Lead name/contact name |
| company_name | VARCHAR(255) | YES | NULL | Company name |
| email | VARCHAR(255) | YES | NULL | Email address |
| phone | VARCHAR(255) | YES | NULL | Phone number |
| source | VARCHAR(255) | YES | NULL | Lead source (e.g., 'Website', 'Referral') |
| score | ENUM | NO | 'warm' | Lead quality indicator |
| estimated_value | DECIMAL(10,2) | YES | NULL | Deal size estimate |
| last_contacted_at | TIMESTAMP | YES | NULL | Last contact timestamp |
| next_followup_at | TIMESTAMP | YES | NULL | Scheduled follow-up |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Score Values**: `hot`, `warm`, `cold`

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_tenant_status (tenant_id, lead_status_id)
- INDEX idx_tenant_assigned (tenant_id, assigned_to_user_id)
- INDEX idx_tenant_followup (tenant_id, next_followup_at)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
- FOREIGN KEY (lead_status_id) REFERENCES lead_statuses(id) ON DELETE RESTRICT
- FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL
- FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL

---

### lead_activities

**Purpose**: Immutable activity timeline for each lead.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| lead_id | BIGINT | NO | - | Foreign key to leads |
| user_id | BIGINT | NO | - | User who performed activity |
| type | ENUM | NO | - | Activity type |
| payload_json | JSON | YES | NULL | Additional data (flexible) |
| created_at | TIMESTAMP | YES | NULL | Activity timestamp |

**Type Values**: `call`, `note`, `status_change`, `followup`

**Payload Examples**:
```json
// status_change
{
  "from_status": "New",
  "to_status": "Qualified"
}

// call
{
  "call_id": 123,
  "duration": 180,
  "outcome": "Answered"
}

// note
{
  "note": "Customer interested in enterprise plan"
}
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_tenant_lead_created (tenant_id, lead_id, created_at)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
- FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

**Note**: No `updated_at` - activities are immutable

---

### agent_sip_credentials

**Purpose**: Per-agent SIP credentials for WebRTC calling (encrypted).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| user_id | BIGINT | NO | - | Foreign key to users |
| sip_ws_url | VARCHAR(255) | NO | - | WebSocket URL (wss://...) |
| sip_username | VARCHAR(255) | NO | - | SIP username/extension |
| sip_password | TEXT | NO | - | Encrypted SIP password |
| sip_domain | VARCHAR(255) | NO | - | SIP domain |
| display_name | VARCHAR(255) | YES | NULL | Display name for caller ID |
| auto_register | BOOLEAN | NO | FALSE | Auto-register on console load |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Security**:
- `sip_password` is encrypted using Laravel's `encrypt()` function
- Decrypted automatically by model accessor

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE KEY unique_user (tenant_id, user_id)
- INDEX idx_tenant (tenant_id)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### calls

**Purpose**: Call records with polymorphic relationships to related entities.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| user_id | BIGINT | NO | - | User who made/received call |
| related_type | VARCHAR(255) | YES | NULL | Polymorphic type (e.g., 'lead') |
| related_id | BIGINT | YES | NULL | Polymorphic ID |
| direction | ENUM | NO | - | Call direction |
| from_number | VARCHAR(255) | NO | - | Caller number |
| to_number | VARCHAR(255) | NO | - | Called number |
| started_at | TIMESTAMP | NO | - | Call start time |
| ended_at | TIMESTAMP | YES | NULL | Call end time |
| duration_seconds | INT | YES | NULL | Call duration (calculated) |
| pbx_call_id | VARCHAR(255) | YES | NULL | PBX system call ID |
| recording_url | VARCHAR(255) | YES | NULL | URL to recording (PBX) |
| disposition_id | BIGINT | YES | NULL | Call outcome |
| wrapup_notes | TEXT | YES | NULL | Agent's wrap-up notes |
| created_at | TIMESTAMP | YES | NULL | Record creation |
| updated_at | TIMESTAMP | YES | NULL | Last update |

**Direction Values**: `inbound`, `outbound`

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_tenant_user_started (tenant_id, user_id, started_at)
- INDEX idx_tenant_related (tenant_id, related_type, related_id)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (disposition_id) REFERENCES call_dispositions(id) ON DELETE SET NULL

**Polymorphic Relationship**:
```php
// Can relate to any entity
$call->related_type = 'lead';
$call->related_id = 123;

// Or
$call->related_type = 'customer';
$call->related_id = 456;
```

---

### call_dispositions

**Purpose**: Tenant-configurable call outcome options.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| name | VARCHAR(255) | NO | - | Disposition name |
| sort_order | INT | NO | 0 | Display order |
| is_default | BOOLEAN | NO | FALSE | Default selection |
| requires_note | BOOLEAN | NO | FALSE | Forces note entry |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Last update timestamp |

**Default Dispositions** (seeded):
- Answered
- No Answer
- Voicemail
- Busy
- Wrong Number
- Follow-up Required (requires_note = TRUE)

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_tenant_sort (tenant_id, sort_order)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE

---

### audit_logs

**Purpose**: System-wide activity audit trail.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT | NO | AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT | NO | - | Foreign key to tenants |
| user_id | BIGINT | YES | NULL | User who performed action |
| action | VARCHAR(255) | NO | - | Action performed |
| entity_type | VARCHAR(255) | YES | NULL | Related entity type |
| entity_id | BIGINT | YES | NULL | Related entity ID |
| ip_address | VARCHAR(45) | YES | NULL | IP address |
| user_agent | TEXT | YES | NULL | Browser user agent |
| created_at | TIMESTAMP | YES | NULL | Log timestamp |

**Action Examples**:
- `user.login`
- `user.logout`
- `lead.created`
- `lead.updated`
- `lead.status_changed`
- `call.started`
- `call.ended`
- `settings.updated`

**Indexes**:
- PRIMARY KEY (id)
- INDEX idx_tenant_created (tenant_id, created_at)
- INDEX idx_tenant_entity (tenant_id, entity_type, entity_id)
- FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL

**Note**: No `updated_at` - logs are immutable

---

## Relationships

### Tenant Relationships

```
Tenant
├── hasMany Users
├── hasMany LeadStatuses
├── hasMany Leads
├── hasMany CallDispositions
├── hasMany Calls
├── hasMany Permissions
├── hasMany AgentSipCredentials
├── hasMany LeadActivities
└── hasMany AuditLogs
```

### User Relationships

```
User
├── belongsTo Tenant
├── hasMany AssignedLeads (leads.assigned_to_user_id)
├── hasMany CreatedLeads (leads.created_by_user_id)
├── hasMany Calls
├── hasMany LeadActivities
├── hasOne AgentSipCredential
└── hasMany AuditLogs
```

### Lead Relationships

```
Lead
├── belongsTo Tenant
├── belongsTo LeadStatus
├── belongsTo AssignedToUser (users)
├── belongsTo CreatedByUser (users)
├── hasMany LeadActivities
└── morphMany Calls (related)
```

### Call Relationships

```
Call
├── belongsTo Tenant
├── belongsTo User
├── belongsTo CallDisposition
└── morphTo Related (lead, etc.)
```

---

## Indexes

### Performance Indexes

**Multi-Column Indexes** (optimized for tenant-scoped queries):

```sql
-- users
INDEX idx_tenant_active (tenant_id, is_active)

-- lead_statuses
INDEX idx_tenant_sort (tenant_id, sort_order)

-- leads
INDEX idx_tenant_status (tenant_id, lead_status_id)
INDEX idx_tenant_assigned (tenant_id, assigned_to_user_id)
INDEX idx_tenant_followup (tenant_id, next_followup_at)

-- lead_activities
INDEX idx_tenant_lead_created (tenant_id, lead_id, created_at)

-- calls
INDEX idx_tenant_user_started (tenant_id, user_id, started_at)
INDEX idx_tenant_related (tenant_id, related_type, related_id)

-- call_dispositions
INDEX idx_tenant_sort (tenant_id, sort_order)

-- audit_logs
INDEX idx_tenant_created (tenant_id, created_at)
INDEX idx_tenant_entity (tenant_id, entity_type, entity_id)
```

**Why tenant_id is first**:
- Most queries filter by tenant_id first
- Enables index-only scans for tenant-scoped queries
- Improves multi-tenant performance

---

## Sample Queries

### Get Leads for Kanban Board

```sql
SELECT 
    l.*,
    ls.name as status_name,
    ls.color as status_color,
    u.name as assigned_to_name
FROM leads l
INNER JOIN lead_statuses ls ON l.lead_status_id = ls.id
LEFT JOIN users u ON l.assigned_to_user_id = u.id
WHERE l.tenant_id = ?
ORDER BY ls.sort_order, l.created_at DESC;
```

**Eloquent**:
```php
Lead::with(['status', 'assignedTo'])
    ->orderBy('created_at', 'desc')
    ->get()
    ->groupBy('lead_status_id');
```

### Get Lead Activity Timeline

```sql
SELECT 
    la.*,
    u.name as user_name
FROM lead_activities la
INNER JOIN users u ON la.user_id = u.id
WHERE la.tenant_id = ? AND la.lead_id = ?
ORDER BY la.created_at DESC;
```

**Eloquent**:
```php
LeadActivity::with('user')
    ->where('lead_id', $leadId)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get Calls for Today

```sql
SELECT 
    c.*,
    u.name as agent_name,
    cd.name as disposition_name
FROM calls c
INNER JOIN users u ON c.user_id = u.id
LEFT JOIN call_dispositions cd ON c.disposition_id = cd.id
WHERE c.tenant_id = ? 
  AND DATE(c.started_at) = CURDATE()
ORDER BY c.started_at DESC;
```

**Eloquent**:
```php
Call::with(['user', 'disposition'])
    ->whereDate('started_at', today())
    ->orderBy('started_at', 'desc')
    ->get();
```

### Get User's Active Leads with Upcoming Follow-Ups

```sql
SELECT 
    l.*,
    ls.name as status_name
FROM leads l
INNER JOIN lead_statuses ls ON l.lead_status_id = ls.id
WHERE l.tenant_id = ?
  AND l.assigned_to_user_id = ?
  AND l.next_followup_at IS NOT NULL
  AND l.next_followup_at >= NOW()
  AND ls.is_closed = FALSE
ORDER BY l.next_followup_at ASC;
```

**Eloquent**:
```php
Lead::with('status')
    ->where('assigned_to_user_id', $userId)
    ->whereNotNull('next_followup_at')
    ->where('next_followup_at', '>=', now())
    ->whereHas('status', fn($q) => $q->where('is_closed', false))
    ->orderBy('next_followup_at')
    ->get();
```

### Tenant Statistics

```sql
SELECT 
    t.name as tenant_name,
    COUNT(DISTINCT u.id) as total_users,
    COUNT(DISTINCT l.id) as total_leads,
    COUNT(DISTINCT CASE WHEN ls.is_closed = FALSE THEN l.id END) as open_leads,
    COUNT(DISTINCT c.id) as total_calls
FROM tenants t
LEFT JOIN users u ON t.id = u.tenant_id AND u.is_active = TRUE
LEFT JOIN leads l ON t.id = l.tenant_id
LEFT JOIN lead_statuses ls ON l.lead_status_id = ls.id
LEFT JOIN calls c ON t.id = c.tenant_id AND DATE(c.started_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE t.id = ?
GROUP BY t.id, t.name;
```

---

## Migration Order

Migrations must be run in this order to respect foreign key constraints:

1. **tenants** (no dependencies)
2. **users** (depends on tenants)
3. **permissions** (depends on tenants)
4. **lead_statuses** (depends on tenants)
5. **leads** (depends on tenants, users, lead_statuses)
6. **lead_activities** (depends on tenants, users, leads)
7. **agent_sip_credentials** (depends on tenants, users)
8. **call_dispositions** (depends on tenants)
9. **calls** (depends on tenants, users, call_dispositions)
10. **audit_logs** (depends on tenants, users)

**Laravel Migration Files** (chronological order):
```
0001_01_01_000000_create_users_table.php (Laravel default)
0001_01_01_000001_create_cache_table.php (Laravel default)
0001_01_01_000002_create_jobs_table.php (Laravel default)
2026_01_19_160854_create_tenants_table.php
2026_01_19_160903_modify_users_table_add_tenant.php
2026_01_19_160904_create_permissions_table.php
2026_01_19_160904_create_lead_statuses_table.php
2026_01_19_160904_create_leads_table.php
2026_01_19_160904_create_lead_activities_table.php
2026_01_19_160904_create_agent_sip_credentials_table.php
2026_01_19_160905_create_call_dispositions_table.php
2026_01_19_160904_create_calls_table.php
2026_01_19_160905_create_audit_logs_table.php
```

**Run Migrations**:
```bash
php artisan migrate
```

**Rollback**:
```bash
php artisan migrate:rollback
```

**Fresh Migration** (drops all tables):
```bash
php artisan migrate:fresh
```

---

## Data Integrity Rules

### Business Rules Enforced by Database

1. **Tenant Isolation**
   - All tenant-scoped tables have `tenant_id` foreign key
   - Cascade delete on tenant deletion

2. **User Relationships**
   - Users belong to exactly one tenant
   - Cannot delete user with assigned leads (SET NULL)

3. **Lead Status**
   - Cannot delete status with associated leads (RESTRICT)
   - Must have at least one default status per tenant

4. **Calls**
   - Must have disposition before closing (enforced in application)
   - Related entity is optional (polymorphic)

5. **Immutable Records**
   - `lead_activities` and `audit_logs` have no `updated_at`
   - Cannot be modified after creation

---

## Query Performance Tips

### Optimization Strategies

1. **Always Include tenant_id in WHERE**
```sql
-- Good
WHERE tenant_id = ? AND status = 'active'

-- Bad (won't use index)
WHERE status = 'active'
```

2. **Use Eager Loading**
```php
// Good
Lead::with(['status', 'assignedTo', 'activities'])->get();

// Bad (N+1 problem)
$leads = Lead::all();
foreach ($leads as $lead) {
    $status = $lead->status; // Triggers separate query
}
```

3. **Limit Result Set**
```php
// Use pagination
Lead::paginate(50);

// Or cursor pagination for large datasets
Lead::cursorPaginate(50);
```

4. **Select Only Needed Columns**
```php
Lead::select('id', 'name', 'email')->get();
```

5. **Use Database Transactions**
```php
DB::transaction(function () {
    // Multiple operations
});
```

---

**Version**: 1.0  
**Last Updated**: 2026-01-19  
**Database Engine**: MySQL 8 / PostgreSQL 15  
**Character Set**: utf8mb4 (MySQL) / UTF8 (PostgreSQL)
