# Laravel Livewire CRM - System Architecture & Design

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [System Overview](#system-overview)
3. [Architecture Decisions](#architecture-decisions)
4. [Technology Stack](#technology-stack)
5. [Database Design](#database-design)
6. [Application Layers](#application-layers)
7. [Security Architecture](#security-architecture)
8. [Multi-Tenancy Design](#multi-tenancy-design)
9. [Component Architecture](#component-architecture)
10. [API Design](#api-design)
11. [Deployment Architecture](#deployment-architecture)

---

## Executive Summary

This document describes the architecture and design of a production-ready, multi-tenant SaaS CRM application built with Laravel 11, Livewire v3, Alpine.js, and Tailwind CSS.

**Key Characteristics:**
- Server-rendered application with SPA-like behavior
- Company-based multi-tenancy with absolute data isolation
- Session-based authentication (no token-based API)
- Perfex-style dynamic lead management
- WebRTC telephony integration (SIP.js)
- Browser BroadcastChannel for inter-tab communication

---

## System Overview

### Purpose
A multi-tenant CRM platform for sales teams with integrated telephony, lead management, and activity tracking.

### Core Features
- Multi-tenant company management
- Dynamic lead pipeline (Kanban)
- Click-to-call telephony
- Call recording and disposition
- Activity audit logging
- Role-based permissions

### Users & Roles
- **Tenant Admin**: Full access to tenant configuration and users
- **Sales Agent**: Lead management, calling, basic reporting
- **Support Agent**: Similar to sales agent with different permissions
- **Supervisor**: Team oversight, reports, recordings access
- **Read Only**: View-only access to data

---

## Architecture Decisions

### ADR-001: Server-Rendered with Livewire
**Decision**: Use Livewire v3 for reactive UI instead of SPA frameworks.

**Rationale**:
- Reduces complexity (single Laravel codebase)
- Better SEO and initial load times
- Session-based auth simplifies security
- Alpine.js provides necessary client-side interactivity

**Consequences**:
- Must use WebSockets or polling for real-time features
- Client-side routing limitations
- Page state lives on server

### ADR-002: Multi-Tenancy via Tenant ID Column
**Decision**: Implement multi-tenancy using tenant_id foreign key with global scopes.

**Rationale**:
- Simple implementation and maintenance
- Excellent query performance
- Native database relationships
- Easy to understand and debug

**Consequences**:
- All tenant-scoped tables need tenant_id column
- Global scopes must be applied consistently
- Risk of cross-tenant bugs if scopes missed

### ADR-003: No Hard-Coded Statuses
**Decision**: Lead statuses and call dispositions are fully tenant-configurable.

**Rationale**:
- Different businesses have different workflows
- Follows Perfex CRM design pattern
- Allows dynamic business rule enforcement

**Consequences**:
- More complex status management UI needed
- Must ensure at least one default status exists
- Business logic depends on status flags, not names

### ADR-004: Database Queue Driver
**Decision**: Use Laravel's database queue driver instead of Redis.

**Rationale**:
- Reduces infrastructure dependencies
- Simpler deployment and maintenance
- Sufficient for expected load

**Consequences**:
- Requires cron-based queue workers
- Less performant than Redis for high volume
- Workers must be monitored

### ADR-005: External PBX (FusionPBX)
**Decision**: CRM does not manage PBX; integrates with external FusionPBX.

**Rationale**:
- PBX management is complex and specialized
- Separation of concerns
- Users may have existing PBX infrastructure

**Consequences**:
- CRM never validates SIP credentials
- No automated extension provisioning
- Recording URLs stored, not files

---

## Technology Stack

### Backend
```
PHP 8.3
Laravel 11 (latest stable)
Livewire v3
MySQL 8 / PostgreSQL 15
```

### Frontend
```
Alpine.js (client-side reactivity)
Tailwind CSS (utility-first styling)
SIP.js (WebRTC telephony)
BroadcastChannel API (inter-tab communication)
```

### Infrastructure
```
Database Queue Driver
Cron-based queue workers
HTTPS/WSS required
Session-based authentication
```

### Testing
```
PHPUnit
Laravel Testing Framework
Browser Testing (for BroadcastChannel)
```

---

## Database Design

### Entity Relationship Overview

```
┌──────────┐       ┌──────────────────┐       ┌──────────────┐
│ Tenants  │◄──────┤ Users            │──────►│ Permissions  │
└────┬─────┘       └──────┬───────────┘       └──────────────┘
     │                    │
     │                    │
     ▼                    ▼
┌──────────────┐    ┌─────────────┐
│ LeadStatuses │◄───┤ Leads       │
└──────────────┘    └──────┬──────┘
                           │
                           ▼
                    ┌──────────────────┐
                    │ LeadActivities   │
                    └──────────────────┘

┌─────────────────────┐    ┌──────────────────┐
│ AgentSipCredentials │    │ Calls            │
└─────────────────────┘    └──────┬───────────┘
                                  │
                                  ▼
                           ┌──────────────────┐
                           │ CallDispositions │
                           └──────────────────┘

┌──────────────┐
│ AuditLogs    │
└──────────────┘
```

### Core Tables

#### tenants
```sql
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    status ENUM('active', 'suspended', 'closed') DEFAULT 'active',
    timezone VARCHAR(255) DEFAULT 'UTC',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status (status)
);
```

**Purpose**: Company/tenant information
**Key Fields**: 
- `status`: Controls tenant access
- `timezone`: Default timezone for tenant

#### users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('tenant_admin', 'sales_agent', 'support_agent', 'supervisor', 'read_only'),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_active (tenant_id, is_active)
);
```

**Purpose**: User accounts with tenant relationship
**Key Fields**:
- `role`: Determines permissions
- `is_active`: Can disable without deletion

#### permissions
```sql
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    role ENUM('tenant_admin', 'sales_agent', 'support_agent', 'supervisor', 'read_only'),
    permission VARCHAR(255) NOT NULL, -- e.g., 'leads.view', 'leads.manage'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (tenant_id, role, permission),
    INDEX idx_tenant (tenant_id)
);
```

**Purpose**: Granular role-based permissions per tenant
**Key Fields**:
- `permission`: Dot-notation permission string
- Unique constraint prevents duplicate permissions

#### lead_statuses
```sql
CREATE TABLE lead_statuses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) DEFAULT '#3B82F6', -- Hex color
    sort_order INT DEFAULT 0,
    is_default BOOLEAN DEFAULT FALSE,
    is_closed BOOLEAN DEFAULT FALSE,
    is_won BOOLEAN DEFAULT FALSE,
    is_lost BOOLEAN DEFAULT FALSE,
    requires_note BOOLEAN DEFAULT FALSE,
    requires_followup_date BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_sort (tenant_id, sort_order)
);
```

**Purpose**: Dynamic, tenant-configurable lead statuses
**Key Fields**:
- `sort_order`: Controls Kanban column order
- `is_default`: Assigned to new leads
- `is_closed/is_won/is_lost`: Status classification flags
- `requires_note/requires_followup_date`: Business rules

#### leads
```sql
CREATE TABLE leads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    lead_status_id BIGINT NOT NULL,
    assigned_to_user_id BIGINT NULL,
    created_by_user_id BIGINT NULL,
    name VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    score ENUM('hot', 'warm', 'cold') DEFAULT 'warm',
    estimated_value DECIMAL(10, 2) NULL,
    last_contacted_at TIMESTAMP NULL,
    next_followup_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_status_id) REFERENCES lead_statuses(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_status (tenant_id, lead_status_id),
    INDEX idx_tenant_assigned (tenant_id, assigned_to_user_id),
    INDEX idx_tenant_followup (tenant_id, next_followup_at)
);
```

**Purpose**: Lead records with full CRM data
**Key Fields**:
- `score`: Quick lead quality indicator
- `estimated_value`: Deal size
- `next_followup_at`: For reminder/scheduling features

#### lead_activities
```sql
CREATE TABLE lead_activities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    lead_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    type ENUM('call', 'note', 'status_change', 'followup'),
    payload_json JSON NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_lead_created (tenant_id, lead_id, created_at)
);
```

**Purpose**: Activity timeline for each lead
**Key Fields**:
- `type`: Activity category
- `payload_json`: Flexible data storage (e.g., old/new status)
- No `updated_at` - activities are immutable

#### agent_sip_credentials
```sql
CREATE TABLE agent_sip_credentials (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    sip_ws_url VARCHAR(255) NOT NULL,
    sip_username VARCHAR(255) NOT NULL,
    sip_password TEXT NOT NULL, -- Encrypted
    sip_domain VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NULL,
    auto_register BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (tenant_id, user_id),
    INDEX idx_tenant (tenant_id)
);
```

**Purpose**: Per-agent SIP credentials for WebRTC calling
**Key Fields**:
- `sip_password`: Laravel encrypted at model level
- `auto_register`: Whether to register on Agent Console load
- One credential set per user

#### calls
```sql
CREATE TABLE calls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    related_type VARCHAR(255) NULL, -- 'lead', etc.
    related_id BIGINT NULL,
    direction ENUM('inbound', 'outbound'),
    from_number VARCHAR(255) NOT NULL,
    to_number VARCHAR(255) NOT NULL,
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    duration_seconds INT NULL,
    pbx_call_id VARCHAR(255) NULL,
    recording_url VARCHAR(255) NULL,
    disposition_id BIGINT NULL,
    wrapup_notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (disposition_id) REFERENCES call_dispositions(id) ON DELETE SET NULL,
    INDEX idx_tenant_user_started (tenant_id, user_id, started_at),
    INDEX idx_tenant_related (tenant_id, related_type, related_id)
);
```

**Purpose**: Call records with related entities
**Key Fields**:
- `related_type/related_id`: Polymorphic relationship to leads, etc.
- `disposition_id`: Outcome of call (required for wrap-up)
- `recording_url`: Link to PBX recording (not file storage)

#### call_dispositions
```sql
CREATE TABLE call_dispositions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_default BOOLEAN DEFAULT FALSE,
    requires_note BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_sort (tenant_id, sort_order)
);
```

**Purpose**: Tenant-configurable call outcomes
**Key Fields**:
- `requires_note`: Forces wrap-up note entry
- `is_default`: Pre-selected in wrap-up modal

#### audit_logs
```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(255) NULL,
    entity_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_created (tenant_id, created_at),
    INDEX idx_tenant_entity (tenant_id, entity_type, entity_id)
);
```

**Purpose**: System-wide activity logging
**Key Fields**:
- `action`: What happened (e.g., 'user.login', 'lead.created')
- `entity_type/entity_id`: Related entity
- No `updated_at` - logs are immutable

### Database Indexes Strategy

**Primary Indexes**:
- All primary keys are auto-increment BIGINT
- All tenant-scoped tables have `(tenant_id, other_columns)` composite indexes

**Performance Considerations**:
- `tenant_id` is first column in multi-column indexes for query optimization
- Timestamp fields used in queries have indexes (e.g., `next_followup_at`)
- Foreign keys automatically indexed

---

## Application Layers

### Layer Architecture

```
┌─────────────────────────────────────────────┐
│           Presentation Layer                │
│  (Livewire Components, Blade Views)         │
└────────────────┬────────────────────────────┘
                 │
┌────────────────▼────────────────────────────┐
│          Application Layer                   │
│  (Controllers, Services, Jobs)              │
└────────────────┬────────────────────────────┘
                 │
┌────────────────▼────────────────────────────┐
│            Domain Layer                      │
│  (Models, Repositories, Policies)           │
└────────────────┬────────────────────────────┘
                 │
┌────────────────▼────────────────────────────┐
│         Infrastructure Layer                 │
│  (Database, Queue, Cache, External APIs)    │
└─────────────────────────────────────────────┘
```

### Presentation Layer Components

**Livewire Components**:
```
app/Livewire/
├── Auth/
│   ├── CompanyRegistration.php
│   └── Login.php
├── Dashboard/
│   └── Dashboard.php
├── Leads/
│   ├── Kanban.php
│   ├── LeadCard.php
│   ├── CreateLeadModal.php
│   ├── LeadDrawer.php
│   └── LeadStatusSettings.php
├── Calls/
│   ├── CallsLog.php
│   ├── CallWrapUpModal.php
│   └── AgentConsole.php
├── Settings/
│   ├── UsersManagement.php
│   ├── DispositionSettings.php
│   └── SIPSettingsForm.php
└── Shared/
    ├── NotificationBell.php
    └── AgentStatusIndicator.php
```

**Blade Layouts**:
```
resources/views/
├── components/
│   └── layouts/
│       ├── app.blade.php (authenticated)
│       └── guest.blade.php (public)
├── livewire/
│   ├── auth/
│   ├── leads/
│   ├── calls/
│   └── settings/
└── emails/
    └── (email templates)
```

### Application Layer

**Services**:
```
app/Services/
├── TenantService.php (tenant provisioning)
├── CallService.php (call lifecycle management)
├── LeadService.php (complex lead operations)
└── AuditService.php (audit logging)
```

**Jobs**:
```
app/Jobs/
├── LogActivity.php (async activity logging)
├── CleanupOldRecordings.php (retention policy)
└── SendFollowupReminder.php (scheduled notifications)
```

### Domain Layer

**Models** (with BelongsToTenant trait):
```
app/Models/
├── Tenant.php
├── User.php
├── Permission.php
├── Lead.php
├── LeadStatus.php
├── LeadActivity.php
├── Call.php
├── CallDisposition.php
├── AgentSipCredential.php
└── AuditLog.php
```

**Traits**:
```
app/Traits/
└── BelongsToTenant.php (global scope + auto-assignment)
```

**Scopes**:
```
app/Scopes/
└── TenantScope.php (Eloquent global scope)
```

**Policies** (authorization):
```
app/Policies/
├── LeadPolicy.php
├── UserPolicy.php
├── CallPolicy.php
└── SettingsPolicy.php
```

### Infrastructure Layer

**Queue Configuration** (`config/queue.php`):
```php
'default' => env('QUEUE_CONNECTION', 'database'),
```

**Cron Setup** (production):
```bash
* * * * * cd /path-to-app && php artisan queue:work --stop-when-empty
```

---

## Security Architecture

### Authentication Flow

```
┌─────────┐     ┌──────────────┐     ┌─────────────┐
│ Browser │────►│ Login Form   │────►│ Authenticate│
└─────────┘     └──────────────┘     └──────┬──────┘
                                             │
                                             ▼
                                      ┌──────────────┐
                                      │ Check Tenant │
                                      │ Status       │
                                      └──────┬───────┘
                                             │
                                             ▼
                                      ┌──────────────┐
                                      │ Set Session  │
                                      └──────┬───────┘
                                             │
                                             ▼
                                      ┌──────────────┐
                                      │ Dashboard    │
                                      └──────────────┘
```

### Security Measures

1. **CSRF Protection**: Enabled for all POST/PUT/DELETE requests
2. **Password Hashing**: Bcrypt with Laravel defaults
3. **SIP Credential Encryption**: Laravel encryption for passwords
4. **SQL Injection**: Eloquent ORM parameterized queries
5. **XSS Protection**: Blade automatic escaping
6. **Session Security**: HTTP-only cookies, secure flag in production
7. **Tenant Isolation**: Server-side global scopes (never client-provided)

### Authorization Pattern

```php
// In controller/component
public function updateLead(Lead $lead)
{
    $this->authorize('update', $lead); // Policy check
    
    // Business logic
}

// In LeadPolicy
public function update(User $user, Lead $lead): bool
{
    return $user->hasPermission('leads.manage') 
        && $lead->tenant_id === $user->tenant_id; // Redundant check
}
```

---

## Multi-Tenancy Design

### Tenant Isolation Pattern

**Global Scope Trait** (`BelongsToTenant`):
```php
namespace App\Traits;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Automatically filter all queries
        static::addGlobalScope(new TenantScope());

        // Automatically set tenant_id on create
        static::creating(function ($model) {
            if (auth()->check() && auth()->user() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

**Usage**:
```php
class Lead extends Model
{
    use BelongsToTenant; // That's it!
    
    // All queries automatically filtered by tenant_id
    // tenant_id automatically set on creation
}
```

### Tenant Provisioning Flow

```
Company Registration
        │
        ▼
┌─────────────────┐
│ Create Tenant   │ (status='active')
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Create Owner    │ (role='tenant_admin')
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Seed Statuses   │ (6 default lead statuses)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Seed Dispositions│ (6 default call dispositions)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Grant Permissions│ (role-based defaults)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Login Owner     │
└────────┬────────┘
         │
         ▼
    Dashboard
```

### Tenant Lifecycle States

```
┌─────────┐
│ active  │◄──────────┐
└────┬────┘           │
     │                │
     │ suspend        │ activate
     ▼                │
┌───────────┐         │
│ suspended │─────────┘
└────┬──────┘
     │
     │ close
     ▼
┌─────────┐
│ closed  │ (permanent)
└─────────┘
```

**Rules**:
- `suspended`: Users cannot log in; data intact
- `closed`: Permanent state; data may be archived

---

## Component Architecture

### Livewire Component Pattern

**Base Component Structure**:
```php
namespace App\Livewire\Leads;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Kanban extends Component
{
    // Public properties (reactive)
    public $showModal = false;
    
    // Validated properties
    #[Validate('required|min:2')]
    public $leadName = '';
    
    // Event listeners
    #[On('leadCreated')]
    public function handleLeadCreated()
    {
        $this->showModal = false;
        // Refresh happens automatically
    }
    
    // Actions
    public function createLead()
    {
        $this->validate();
        // Business logic
        $this->dispatch('leadCreated');
    }
    
    // Render
    public function render()
    {
        return view('livewire.leads.kanban', [
            'statuses' => LeadStatus::ordered()->get(),
            'leads' => Lead::with(['status', 'assignedTo'])->get(),
        ])->layout('components.layouts.app');
    }
}
```

### Alpine.js Integration

**Drag and Drop Example**:
```html
<div x-data="kanbanBoard()">
    <div @dragover.prevent
         @drop="handleDrop($event, statusId)">
        <!-- Draggable cards -->
        <div draggable="true"
             @dragstart="handleDragStart($event, leadId)">
            <!-- Lead card content -->
        </div>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        draggedLeadId: null,
        
        handleDragStart(event, leadId) {
            this.draggedLeadId = leadId;
        },
        
        handleDrop(event, statusId) {
            @this.dispatch('leadMoved', {
                leadId: this.draggedLeadId,
                statusId: statusId
            });
        }
    };
}
</script>
```

### BroadcastChannel Pattern (Click-to-Call)

**Agent Console** (listener):
```javascript
const channel = new BroadcastChannel('crm-agent-phone');

channel.onmessage = (event) => {
    const { type, number, relatedType, relatedId } = event.data;
    
    if (type === 'CALL_REQUEST') {
        sipSession.invite(number);
        
        // Notify success
        channel.postMessage({
            type: 'CALL_STARTED',
            relatedId: relatedId
        });
    }
};
```

**CRM Pages** (publisher):
```javascript
function clickToCall(number, leadId) {
    const channel = new BroadcastChannel('crm-agent-phone');
    
    channel.postMessage({
        type: 'CALL_REQUEST',
        number: number,
        relatedType: 'lead',
        relatedId: leadId,
        requestId: crypto.randomUUID()
    });
    
    // Show "waiting for agent console" if no response
    setTimeout(() => {
        // Prompt user to open agent console
    }, 2000);
}
```

---

## API Design

### Internal Livewire API

While there's no RESTful API, Livewire components expose actions:

**Lead Actions**:
- `createLead(data)`: Create new lead
- `updateLead(leadId, data)`: Update lead
- `moveLead(leadId, statusId)`: Change status
- `assignLead(leadId, userId)`: Assign to user
- `deleteLead(leadId)`: Soft delete

**Call Actions**:
- `startCall(number, relatedType, relatedId)`: Initiate call
- `endCall(callId)`: End call
- `wrapUpCall(callId, dispositionId, notes)`: Complete wrap-up

### BroadcastChannel Message Contracts

**CALL_REQUEST**:
```json
{
    "type": "CALL_REQUEST",
    "number": "+15551234567",
    "relatedType": "lead",
    "relatedId": 123,
    "requestId": "uuid"
}
```

**CALL_STARTED**:
```json
{
    "type": "CALL_STARTED",
    "callId": 456,
    "relatedId": 123
}
```

**CALL_ENDED**:
```json
{
    "type": "CALL_ENDED",
    "callId": 456,
    "duration": 120
}
```

---

## Deployment Architecture

### Production Environment

```
┌─────────────────────────────────────────────┐
│              Load Balancer (HTTPS)          │
└────────────────┬────────────────────────────┘
                 │
        ┌────────┴────────┐
        │                 │
┌───────▼────────┐  ┌────▼──────────┐
│ Web Server 1   │  │ Web Server 2  │
│ (Laravel)      │  │ (Laravel)     │
└───────┬────────┘  └────┬──────────┘
        │                │
        └────────┬────────┘
                 │
        ┌────────▼────────┐
        │   Database      │
        │ MySQL/PostgreSQL│
        └─────────────────┘

┌─────────────────┐
│ Queue Workers   │ (Cron-based)
└─────────────────┘

┌─────────────────┐
│ FusionPBX (WSS) │ (External)
└─────────────────┘
```

### Infrastructure Requirements

**Web Servers**:
- PHP 8.3+ with required extensions
- Nginx or Apache
- HTTPS certificate (Let's Encrypt)
- Session storage (file or database)

**Database**:
- MySQL 8+ or PostgreSQL 15+
- Regular backups
- Replica for read scaling (optional)

**Queue Workers**:
- Cron job running `php artisan queue:work --stop-when-empty`
- Process monitoring (Supervisor recommended)

**External Services**:
- FusionPBX with WSS support
- SMTP for email notifications

### Environment Configuration

**Required ENV Variables**:
```bash
APP_URL=https://crm.example.com
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=crm
DB_USERNAME=crm_user
DB_PASSWORD=secure_password

QUEUE_CONNECTION=database

SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
```

---

## Performance Considerations

### Query Optimization

1. **Eager Loading**: Always use `with()` for relationships
```php
Lead::with(['status', 'assignedTo', 'activities'])->get();
```

2. **Index Usage**: Composite indexes on (tenant_id, other_columns)

3. **Pagination**: Use cursor pagination for large datasets
```php
Lead::orderBy('created_at', 'desc')->cursorPaginate(50);
```

### Caching Strategy

**Configuration Cache**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Model Caching** (optional):
```php
// Cache lead statuses per tenant
$statuses = Cache::remember(
    "tenant.{$tenantId}.lead_statuses",
    3600,
    fn() => LeadStatus::ordered()->get()
);
```

### Frontend Optimization

1. **Asset Compilation**: `npm run build` for production
2. **Lazy Loading**: Load SIP.js only on Agent Console page
3. **Code Splitting**: Vite automatic code splitting
4. **Image Optimization**: Use WebP format where possible

---

## Monitoring & Observability

### Logging Strategy

**Channels** (`config/logging.php`):
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
    ],
    'calls' => [
        'driver' => 'daily',
        'path' => storage_path('logs/calls.log'),
    ],
],
```

### Metrics to Monitor

1. **Application**:
   - Response time per route
   - Queue job processing time
   - Failed job rate

2. **Business**:
   - Active tenants count
   - Leads created per day
   - Calls per hour
   - Conversion rates

3. **Infrastructure**:
   - Database connection pool
   - Disk usage
   - Memory usage

### Health Checks

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => DB::connection()->getDatabaseName(),
        'queue' => Queue::size(),
    ]);
});
```

---

## Appendix: Naming Conventions

### Database
- Tables: `snake_case`, plural (e.g., `lead_statuses`)
- Columns: `snake_case` (e.g., `next_followup_at`)
- Foreign keys: `{model}_id` (e.g., `tenant_id`)

### PHP
- Classes: `PascalCase` (e.g., `LeadStatus`)
- Methods: `camelCase` (e.g., `createLead()`)
- Properties: `camelCase` (e.g., `$isActive`)

### JavaScript
- Functions: `camelCase` (e.g., `handleDragStart()`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `CALL_REQUEST`)

### Livewire
- Components: `Namespace\ComponentName` (e.g., `Leads\Kanban`)
- Events: `kebab-case` (e.g., `lead-created`)

---

**Version**: 1.0  
**Last Updated**: 2026-01-19  
**Author**: Laravel Livewire CRM Team
