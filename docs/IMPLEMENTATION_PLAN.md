# Laravel Livewire CRM - Implementation Plan & Task List

## Table of Contents
1. [Project Overview](#project-overview)
2. [Development Phases](#development-phases)
3. [Task Breakdown](#task-breakdown)
4. [Implementation Guidelines](#implementation-guidelines)
5. [Testing Strategy](#testing-strategy)
6. [Deployment Checklist](#deployment-checklist)

---

## Project Overview

### Objective
Build a production-ready, multi-tenant SaaS CRM with integrated telephony using Laravel 11, Livewire v3, Alpine.js, and Tailwind CSS.

### Current Status
✅ **Phase 1-4 Complete**: Core infrastructure, database, models, authentication, and basic leads management implemented.

### Remaining Work
- Phase 5: Additional Livewire components (LeadDrawer, CallsLog, AgentConsole, etc.)
- Phase 6: Telephony integration (SIP.js, BroadcastChannel, call wrap-up)
- Phase 7: Advanced UI/UX features
- Phase 8: Background jobs and queue processing
- Phase 9: Comprehensive testing
- Phase 10: Documentation and deployment preparation

---

## Development Phases

### Phase 1: Project Foundation ✅ COMPLETE
**Status**: ✅ Complete  
**Duration**: Completed  
**Description**: Laravel installation, dependency configuration, basic project structure.

**Deliverables**:
- [x] Laravel 11 fresh installation
- [x] Livewire v3 installed and configured
- [x] Alpine.js integrated
- [x] Tailwind CSS with custom configuration
- [x] Base directory structure
- [x] Version control initialized

---

### Phase 2: Database Schema ✅ COMPLETE
**Status**: ✅ Complete  
**Duration**: Completed  
**Description**: All database migrations with proper relationships and indexes.

**Deliverables**:
- [x] Tenants table migration
- [x] Users table with tenant FK and roles
- [x] Permissions table
- [x] Lead statuses table (dynamic)
- [x] Leads table with full fields
- [x] Lead activities table
- [x] Agent SIP credentials table
- [x] Calls table with polymorphic relationships
- [x] Call dispositions table
- [x] Audit logs table

---

### Phase 3: Core Models & Tenant Isolation ✅ COMPLETE
**Status**: ✅ Complete  
**Duration**: Completed  
**Description**: Eloquent models with tenant scoping and relationships.

**Deliverables**:
- [x] BelongsToTenant trait with global scope
- [x] TenantScope implementation
- [x] All 11 models with proper relationships
- [x] Encrypted SIP credentials
- [x] Model factories updated
- [x] Model casting and attribute accessors

---

### Phase 4: Authentication & Registration ✅ COMPLETE
**Status**: ✅ Complete  
**Duration**: Completed  
**Description**: Company registration and session-based authentication.

**Deliverables**:
- [x] Company registration Livewire component
- [x] Login Livewire component
- [x] TenantService for atomic provisioning
- [x] Default lead status seeding
- [x] Default call disposition seeding
- [x] Permission seeding
- [x] Tenant suspension checks
- [x] Guest and authenticated layouts

---

### Phase 5: Livewire Components
**Status**: 🔄 Partial (Kanban complete, others pending)  
**Duration**: 2-3 weeks  
**Priority**: HIGH  
**Description**: Build all remaining Livewire components for full CRM functionality.

**Task List**:

#### ✅ 5.1 Dashboard Component (COMPLETE)
- [x] Dashboard view with metrics
- [x] Overview statistics (leads, calls, users)
- [x] Welcome message

#### ✅ 5.2 Leads Kanban (COMPLETE)
- [x] Kanban board layout
- [x] Drag and drop with Alpine.js
- [x] Status change validation
- [x] Activity logging on move
- [x] Create lead modal
- [x] Lead cards with scoring indicators

#### 🔲 5.3 Lead Drawer/Detail View
**Priority**: HIGH  
**Estimated Time**: 3-4 days

**Tasks**:
- [ ] Create LeadDrawer Livewire component
- [ ] Slide-over panel UI with Tailwind
- [ ] Lead details form (editable)
- [ ] Activity timeline display
- [ ] Related calls list
- [ ] Tasks/follow-ups section
- [ ] Status change action buttons
- [ ] Click-to-call button
- [ ] Delete/archive functionality
- [ ] Email lead button (future)

**Files to Create**:
```
app/Livewire/Leads/LeadDrawer.php
resources/views/livewire/leads/lead-drawer.blade.php
```

**Key Features**:
- Should open from Kanban card click
- Full CRUD operations on lead
- Real-time updates using Livewire events
- Proper authorization checks

#### 🔲 5.4 Calls Log Component
**Priority**: HIGH  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Create CallsLog Livewire component
- [ ] Table/list view of calls
- [ ] Filters (date range, user, disposition)
- [ ] Search functionality
- [ ] Pagination
- [ ] Play recording button (if URL exists)
- [ ] Related entity links (lead, etc.)
- [ ] Export to CSV functionality
- [ ] Call statistics summary

**Files to Create**:
```
app/Livewire/Calls/CallsLog.php
resources/views/livewire/calls/calls-log.blade.php
resources/views/calls/index.blade.php
```

**Key Features**:
- Sortable columns
- Tenant-scoped queries
- Role-based access (supervisors see all, agents see own)

#### 🔲 5.5 Call Wrap-Up Modal
**Priority**: CRITICAL  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Create CallWrapUpModal Livewire component
- [ ] Modal UI with disposition selection
- [ ] Conditional note field (if required by disposition)
- [ ] Schedule follow-up option
- [ ] Link to related lead/entity
- [ ] Validation enforcement (cannot skip wrap-up)
- [ ] Save call record update
- [ ] Create lead activity if related to lead
- [ ] Close modal only after save

**Files to Create**:
```
app/Livewire/Calls/CallWrapUpModal.php
resources/views/livewire/calls/call-wrap-up-modal.blade.php
```

**Key Requirements**:
- Must be shown immediately after call ends
- Cannot be dismissed without completing
- Integrates with Agent Console

#### 🔲 5.6 Agent Console
**Priority**: CRITICAL  
**Estimated Time**: 5-7 days

**Tasks**:
- [ ] Create AgentConsole Livewire component
- [ ] Dedicated route `/agent/console`
- [ ] Agent status selector (Available, Away, etc.)
- [ ] SIP.js initialization and registration
- [ ] Audio device selection (mic/speaker)
- [ ] Incoming call notification
- [ ] Outgoing call interface
- [ ] Call timer display
- [ ] Hold/mute/transfer buttons
- [ ] BroadcastChannel listener for CALL_REQUEST
- [ ] Emit call events to backend (started, ended)
- [ ] Auto-show wrap-up modal on call end
- [ ] Call history sidebar
- [ ] WebRTC connection status indicator

**Files to Create**:
```
app/Livewire/Calls/AgentConsole.php
resources/views/livewire/calls/agent-console.blade.php
resources/views/agent/console.blade.php
resources/js/sip-client.js
```

**Key Requirements**:
- SIP.js loads ONLY on this page
- Registration only when status = Available
- Unregister on Away/Logout/Page unload
- Must integrate with Call and CallWrapUpModal models

#### 🔲 5.7 SIP Settings Form
**Priority**: MEDIUM  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] Create SIPSettingsForm Livewire component
- [ ] Form for SIP credentials input
- [ ] Encrypted password storage
- [ ] Test connection button (UI only, no validation)
- [ ] Auto-register checkbox
- [ ] Display name field
- [ ] Save credentials
- [ ] Show success/error messages

**Files to Create**:
```
app/Livewire/Settings/SIPSettingsForm.php
resources/views/livewire/settings/sip-settings-form.blade.php
```

**Key Features**:
- Only editable by agent and tenant_admin
- Password field shows masked value when editing
- No actual PBX validation (per spec)

#### 🔲 5.8 Lead Status Settings
**Priority**: MEDIUM  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Create LeadStatusSettings Livewire component
- [ ] List all lead statuses
- [ ] Inline editing of statuses
- [ ] Add new status
- [ ] Delete status (with lead count check)
- [ ] Reorder statuses (drag and drop)
- [ ] Color picker for status
- [ ] Toggle flags (is_default, is_closed, is_won, is_lost)
- [ ] Toggle business rules (requires_note, requires_followup_date)
- [ ] Ensure at least one default status exists

**Files to Create**:
```
app/Livewire/Settings/LeadStatusSettings.php
resources/views/livewire/settings/lead-status-settings.blade.php
```

**Key Requirements**:
- Tenant-scoped settings
- Only accessible by tenant_admin
- Cannot delete status with associated leads

#### 🔲 5.9 Call Disposition Settings
**Priority**: MEDIUM  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] Create DispositionSettings Livewire component
- [ ] List all call dispositions
- [ ] Inline editing
- [ ] Add new disposition
- [ ] Delete disposition (with call count check)
- [ ] Reorder dispositions
- [ ] Toggle is_default flag
- [ ] Toggle requires_note flag

**Files to Create**:
```
app/Livewire/Settings/DispositionSettings.php
resources/views/livewire/settings/disposition-settings.blade.php
```

**Key Requirements**:
- Similar to LeadStatusSettings
- Tenant-scoped

#### 🔲 5.10 Users Management
**Priority**: MEDIUM  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Create UsersManagement Livewire component
- [ ] List all users in tenant
- [ ] Add new user
- [ ] Edit user details
- [ ] Change user role
- [ ] Toggle is_active status
- [ ] Delete user (soft delete or deactivate)
- [ ] Reset password (send email)
- [ ] Show user statistics (leads, calls)

**Files to Create**:
```
app/Livewire/Settings/UsersManagement.php
resources/views/livewire/settings/users-management.blade.php
```

**Key Requirements**:
- Only accessible by tenant_admin and supervisor
- Cannot delete/deactivate self
- Email validation for unique within tenant

#### 🔲 5.11 Shared Components
**Priority**: LOW  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] NotificationBell component (future notifications)
- [ ] AgentStatusIndicator component
- [ ] SearchBar component (global search)
- [ ] Modal base component (reusable)

---

### Phase 6: Telephony Integration
**Status**: 🔲 Not Started  
**Duration**: 2-3 weeks  
**Priority**: CRITICAL  
**Description**: Integrate SIP.js and implement click-to-call functionality.

**Task List**:

#### 🔲 6.1 BroadcastChannel Message Contract
**Priority**: CRITICAL  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Define message types and structure
- [ ] Create JavaScript utility for BroadcastChannel
- [ ] Implement message validation
- [ ] Add timeout handling for failed requests
- [ ] Create fallback modal ("Open Agent Console")

**Files to Create**:
```
resources/js/broadcast-channel.js
```

**Message Types**:
- `CALL_REQUEST`
- `CALL_STARTED`
- `CALL_ANSWERED`
- `CALL_ENDED`
- `CALL_FAILED`

#### 🔲 6.2 SIP.js Integration
**Priority**: CRITICAL  
**Estimated Time**: 3-4 days

**Tasks**:
- [ ] Install SIP.js via npm
- [ ] Create SIP client wrapper class
- [ ] Implement registration logic
- [ ] Handle incoming calls
- [ ] Handle outgoing calls
- [ ] Implement call control (hold, mute, transfer)
- [ ] Audio device management
- [ ] Connection state management
- [ ] Error handling and reconnection
- [ ] Integration with Agent Console component

**Files to Create**:
```
resources/js/sip-client.js
```

**Key Requirements**:
- Load SIP.js ONLY on Agent Console page
- Register/unregister based on agent status
- Clean up on page unload

#### 🔲 6.3 Click-to-Call Implementation
**Priority**: HIGH  
**Estimated Time**: 2 days

**Tasks**:
- [ ] Add click-to-call buttons to lead cards
- [ ] Add click-to-call button to LeadDrawer
- [ ] Implement BroadcastChannel message sending
- [ ] Handle "Agent Console not open" scenario
- [ ] Show call status feedback to user
- [ ] Create Call record on call start

**Files to Modify**:
```
resources/views/livewire/leads/kanban.blade.php
resources/views/livewire/leads/lead-drawer.blade.php
resources/js/click-to-call.js (new)
```

#### 🔲 6.4 Call Lifecycle Management
**Priority**: CRITICAL  
**Estimated Time**: 3-4 days

**Tasks**:
- [ ] Create CallService for call operations
- [ ] Implement startCall() method
- [ ] Implement endCall() method
- [ ] Calculate call duration
- [ ] Store PBX call ID
- [ ] Link calls to related entities (leads)
- [ ] Emit Livewire events for call state changes
- [ ] Integration with wrap-up modal

**Files to Create**:
```
app/Services/CallService.php
```

**Database Operations**:
- Insert call record on start
- Update call record on end
- Create lead activity on call to lead

#### 🔲 6.5 Mandatory Call Wrap-Up
**Priority**: CRITICAL  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] Trigger wrap-up modal on call end
- [ ] Prevent modal dismissal without completion
- [ ] Validate disposition selection
- [ ] Enforce note entry if required
- [ ] Update call record with disposition and notes
- [ ] Create follow-up task if scheduled
- [ ] Close modal only after successful save

**Integration Points**:
- Agent Console → Call end event → Wrap-up modal
- Wrap-up modal → Save → Update call record

#### 🔲 6.6 Recording URL Storage
**Priority**: LOW  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Add recording_url field to call record (already in schema)
- [ ] Display recording link in calls log
- [ ] Implement permission check (only supervisors)
- [ ] Add "play" button with external link
- [ ] Optional: retention policy job

**Key Requirement**:
- CRM NEVER deletes PBX recordings
- Only stores URL reference

---

### Phase 7: UI/UX Enhancements
**Status**: 🔄 Partial (layouts complete)  
**Duration**: 1-2 weeks  
**Priority**: MEDIUM  
**Description**: Polish user interface and add advanced interactions.

**Task List**:

#### ✅ 7.1 Main Layout (COMPLETE)
- [x] Sidebar navigation
- [x] Top bar with user info
- [x] Logout functionality

#### 🔲 7.2 Slide-Over Drawers
**Priority**: HIGH  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] Create reusable slide-over component
- [ ] Slide-in/out animations
- [ ] Click outside to close
- [ ] ESC key to close
- [ ] Prevent body scroll when open
- [ ] Use in LeadDrawer

**Files to Create**:
```
resources/views/components/slide-over.blade.php
```

#### 🔲 7.3 Modal Components
**Priority**: MEDIUM  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Create base modal component
- [ ] Backdrop overlay
- [ ] Center/slide-up animation
- [ ] Close button
- [ ] Confirm dialog variant
- [ ] Use Alpine.js for open/close state

**Files to Create**:
```
resources/views/components/modal.blade.php
resources/views/components/confirm-dialog.blade.php
```

#### 🔲 7.4 Advanced Kanban Features
**Priority**: MEDIUM  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Lead filtering (assigned user, score, date range)
- [ ] Lead search within Kanban
- [ ] Quick edit from card (inline)
- [ ] Bulk actions (assign multiple leads)
- [ ] Compact/expanded card view toggle
- [ ] Custom field display preferences

#### 🔲 7.5 Notifications System
**Priority**: LOW  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Create notifications table migration
- [ ] Notification model
- [ ] NotificationBell Livewire component
- [ ] Mark as read functionality
- [ ] Notification types (lead assigned, call missed, etc.)
- [ ] Real-time notification display
- [ ] Notification preferences

**Files to Create**:
```
database/migrations/xxxx_create_notifications_table.php
app/Models/Notification.php
app/Livewire/Shared/NotificationBell.php
```

#### 🔲 7.6 Responsive Design
**Priority**: MEDIUM  
**Estimated Time**: 2 days

**Tasks**:
- [ ] Mobile sidebar (hamburger menu)
- [ ] Responsive Kanban (scrollable on mobile)
- [ ] Touch-friendly drag and drop (mobile)
- [ ] Responsive tables
- [ ] Mobile-optimized forms

#### 🔲 7.7 Dark Mode (Optional)
**Priority**: LOW  
**Estimated Time**: 2-3 days

**Tasks**:
- [ ] Dark mode color palette
- [ ] Toggle component
- [ ] Store preference in session
- [ ] Update all components for dark mode
- [ ] Test all UI states

---

### Phase 8: Background Jobs & Queues
**Status**: 🔲 Configured (implementation pending)  
**Duration**: 1 week  
**Priority**: MEDIUM  
**Description**: Implement asynchronous job processing.

**Task List**:

#### 🔲 8.1 Activity Logging Job
**Priority**: MEDIUM  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Create LogActivity job
- [ ] Queue activity logging (async)
- [ ] Batch processing optimization
- [ ] Error handling and retry logic

**Files to Create**:
```
app/Jobs/LogActivity.php
```

**Usage**:
```php
LogActivity::dispatch($tenant, $user, $action, $entity);
```

#### 🔲 8.2 Follow-Up Reminder Job
**Priority**: MEDIUM  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] Create SendFollowupReminder job
- [ ] Schedule daily check for due follow-ups
- [ ] Send email notifications
- [ ] Send in-app notifications
- [ ] Mark reminder as sent

**Files to Create**:
```
app/Jobs/SendFollowupReminder.php
app/Console/Commands/CheckFollowups.php
```

**Cron Setup**:
```php
$schedule->command('followups:check')->dailyAt('09:00');
```

#### 🔲 8.3 Recording Retention Cleanup Job
**Priority**: LOW  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Create CleanupOldRecordings job
- [ ] Check call age against retention policy
- [ ] Delete call records (NOT PBX recordings)
- [ ] Schedule monthly execution
- [ ] Tenant-specific retention settings

**Files to Create**:
```
app/Jobs/CleanupOldRecordings.php
```

**Retention Rules**:
- Configurable per tenant (e.g., 90 days)
- Only deletes CRM call records
- NEVER touches PBX recordings

#### 🔲 8.4 Queue Monitoring
**Priority**: MEDIUM  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Install Laravel Horizon (optional) or use built-in queue:monitor
- [ ] Set up failed job notifications
- [ ] Create queue dashboard
- [ ] Configure queue priorities

**Monitoring**:
```bash
php artisan queue:monitor database --max=100
```

---

### Phase 9: Testing
**Status**: 🔄 Partial (8/40+ tests)  
**Duration**: 2-3 weeks  
**Priority**: HIGH  
**Description**: Comprehensive test coverage for all features.

**Task List**:

#### ✅ 9.1 Company Registration Tests (COMPLETE)
- [x] Registration page accessible
- [x] Company can be registered
- [x] Default lead statuses seeded
- [x] Default call dispositions seeded
- [x] Validation errors shown

#### ✅ 9.2 Tenant Isolation Tests (COMPLETE)
- [x] Tenant scope filters leads
- [x] Tenant ID auto-set on create
- [x] Cross-tenant access prevented

#### 🔲 9.3 Lead Management Tests
**Priority**: HIGH  
**Estimated Time**: 2-3 days

**Tests to Create**:
- [ ] Lead creation with all fields
- [ ] Lead update
- [ ] Lead deletion
- [ ] Lead assignment
- [ ] Lead search/filtering
- [ ] Lead status change
- [ ] Activity logging on changes
- [ ] Business rule validation (note required, etc.)

**Files to Create**:
```
tests/Feature/LeadManagementTest.php
```

#### 🔲 9.4 Kanban Move Validation Tests
**Priority**: HIGH  
**Estimated Time**: 1-2 days

**Tests to Create**:
- [ ] Successful status change
- [ ] Status change triggers activity log
- [ ] Note required validation
- [ ] Follow-up required validation
- [ ] Cannot move to deleted status

**Files to Create**:
```
tests/Feature/KanbanTest.php
```

#### 🔲 9.5 Call Management Tests
**Priority**: HIGH  
**Estimated Time**: 2-3 days

**Tests to Create**:
- [ ] Call creation
- [ ] Call end with duration calculation
- [ ] Call linked to lead
- [ ] Wrap-up required enforcement
- [ ] Disposition validation
- [ ] Recording URL storage
- [ ] Call log filtering

**Files to Create**:
```
tests/Feature/CallManagementTest.php
```

#### 🔲 9.6 Click-to-Call Tests
**Priority**: MEDIUM  
**Estimated Time**: 1-2 days

**Tests to Create**:
- [ ] Click-to-call button present
- [ ] BroadcastChannel message sent (mock)
- [ ] Agent Console not open handling
- [ ] Call record created on success

**Files to Create**:
```
tests/Feature/ClickToCallTest.php
```

#### 🔲 9.7 Permission Tests
**Priority**: HIGH  
**Estimated Time**: 1-2 days

**Tests to Create**:
- [ ] Tenant admin has all permissions
- [ ] Sales agent permissions
- [ ] Supervisor permissions
- [ ] Read-only restrictions
- [ ] Policy enforcement on models

**Files to Create**:
```
tests/Unit/PermissionTest.php
tests/Feature/AuthorizationTest.php
```

#### 🔲 9.8 Browser/JavaScript Tests
**Priority**: MEDIUM  
**Estimated Time**: 2-3 days

**Tests to Create**:
- [ ] BroadcastChannel message dispatch
- [ ] Agent Console receives call request
- [ ] SIP.js mock invocation
- [ ] Drag and drop functionality
- [ ] Modal open/close

**Setup Required**:
```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

**Files to Create**:
```
tests/Browser/KanbanDragDropTest.php
tests/Browser/ClickToCallTest.php
tests/Browser/AgentConsoleTest.php
```

#### 🔲 9.9 Integration Tests
**Priority**: MEDIUM  
**Estimated Time**: 2-3 days

**Tests to Create**:
- [ ] Full registration to dashboard flow
- [ ] Create lead → assign → call → wrap-up flow
- [ ] Multi-tenant data isolation across all models
- [ ] Queue job processing

**Files to Create**:
```
tests/Feature/EndToEndFlowTest.php
```

---

### Phase 10: Documentation & Deployment
**Status**: 🔄 Partial (README complete)  
**Duration**: 1 week  
**Priority**: MEDIUM  
**Description**: Finalize documentation and prepare for deployment.

**Task List**:

#### ✅ 10.1 README.md (COMPLETE)
- [x] Installation instructions
- [x] Feature overview
- [x] Usage guide
- [x] Testing commands

#### 🔲 10.2 .env.example
**Priority**: HIGH  
**Estimated Time**: 1 hour

**Tasks**:
- [ ] Complete all required ENV variables
- [ ] Add comments for each variable
- [ ] Document optional variables
- [ ] Production-specific settings

**Variables to Document**:
```
APP_URL
DB_*
QUEUE_CONNECTION
SESSION_*
MAIL_*
SIP_* (if needed)
```

#### 🔲 10.3 API Documentation
**Priority**: LOW  
**Estimated Time**: 1-2 days

**Tasks**:
- [ ] Document Livewire component actions
- [ ] Document BroadcastChannel message contracts
- [ ] Document permission strings
- [ ] Document database schema details

**Files to Create**:
```
docs/API.md
```

#### 🔲 10.4 Deployment Guide
**Priority**: HIGH  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Server requirements documentation
- [ ] Step-by-step deployment instructions
- [ ] Nginx/Apache configuration examples
- [ ] SSL/TLS setup guide
- [ ] Queue worker setup (Supervisor)
- [ ] Cron job configuration
- [ ] Database backup strategy
- [ ] Monitoring setup

**Files to Create**:
```
docs/DEPLOYMENT.md
```

#### 🔲 10.5 Security Documentation
**Priority**: MEDIUM  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Security best practices
- [ ] Tenant isolation guarantees
- [ ] Authentication flow documentation
- [ ] Permission system explanation
- [ ] Encryption details (SIP credentials)
- [ ] OWASP compliance checklist

**Files to Create**:
```
docs/SECURITY.md
```

#### 🔲 10.6 Troubleshooting Guide
**Priority**: MEDIUM  
**Estimated Time**: 1 day

**Tasks**:
- [ ] Common issues and solutions
- [ ] Queue troubleshooting
- [ ] SIP.js connection issues
- [ ] Database migration issues
- [ ] Performance optimization tips

**Files to Create**:
```
docs/TROUBLESHOOTING.md
```

#### 🔲 10.7 Contributing Guide
**Priority**: LOW  
**Estimated Time**: 1 hour

**Tasks**:
- [ ] Code style guidelines
- [ ] Git workflow
- [ ] Testing requirements
- [ ] Pull request template
- [ ] Issue templates

**Files to Create**:
```
CONTRIBUTING.md
.github/PULL_REQUEST_TEMPLATE.md
.github/ISSUE_TEMPLATE/bug_report.md
.github/ISSUE_TEMPLATE/feature_request.md
```

---

## Implementation Guidelines

### Code Quality Standards

#### PHP Code Style
- Follow PSR-12 coding standards
- Use type hints for all parameters and return types
- Write docblocks for public methods
- Maximum method length: 50 lines (guideline)
- Use early returns to reduce nesting

#### JavaScript Code Style
- Use ES6+ syntax
- Use async/await for asynchronous code
- Prefer const over let
- Use meaningful variable names
- Comment complex logic

#### Blade Templates
- Keep logic minimal in views
- Use Blade components for reusable UI
- Proper indentation (4 spaces)
- Use `@` directives over `<?php` tags

#### Tailwind CSS
- Use utility classes consistently
- Extract common patterns into components
- Follow mobile-first responsive design
- Use custom color palette defined in config

### Git Workflow

#### Branch Naming
```
feature/lead-drawer-component
bugfix/kanban-drag-drop-issue
hotfix/security-vulnerability
refactor/tenant-service-optimization
```

#### Commit Messages
```
feat: add lead drawer slide-over component
fix: prevent cross-tenant data access in calls log
refactor: extract call service methods
test: add kanban move validation tests
docs: update deployment guide with WSS requirements
```

#### Pull Request Process
1. Create feature branch from `main`
2. Implement feature with tests
3. Run full test suite
4. Create PR with description
5. Code review
6. Merge to `main`

### Testing Guidelines

#### Test Coverage Requirements
- Aim for 80%+ code coverage
- All critical paths must be tested
- All Livewire component actions tested
- All policies tested

#### Test Organization
```
tests/
├── Feature/          # Integration tests
├── Unit/             # Unit tests
└── Browser/          # Dusk browser tests
```

#### Test Naming Convention
```php
// Feature test
public function test_lead_can_be_created_with_all_fields(): void

// Unit test  
public function test_tenant_scope_filters_by_authenticated_user(): void
```

### Database Best Practices

#### Migration Guidelines
- Always include `down()` method
- Use foreign key constraints
- Add indexes for commonly queried columns
- Use proper column types and sizes
- Never edit existing migrations (create new ones)

#### Model Guidelines
- Use `$fillable` (whitelist) over `$guarded`
- Define all relationships
- Use proper casting for attributes
- Implement attribute accessors/mutators when needed
- Use model events sparingly

#### Query Optimization
- Always eager load relationships
- Use `select()` to load only needed columns
- Implement pagination for large datasets
- Use database transactions for multi-step operations
- Monitor query count (N+1 problem)

### Security Checklist

#### Before Each Commit
- [ ] No sensitive data in code
- [ ] All inputs validated
- [ ] Authorization checks in place
- [ ] Tenant isolation enforced
- [ ] SQL injection prevented (use Eloquent)
- [ ] XSS prevented (use Blade escaping)
- [ ] CSRF tokens included

#### Before Deployment
- [ ] All ENV variables set correctly
- [ ] Debug mode OFF in production
- [ ] HTTPS enforced
- [ ] Database credentials secure
- [ ] Queue workers running
- [ ] Log monitoring configured

---

## Testing Strategy

### Unit Tests
**Focus**: Individual model methods, services, utilities

**Examples**:
```php
// Test tenant scope
TenantScopeTest::test_filters_by_authenticated_user()

// Test permission check
PermissionTest::test_user_has_permission()

// Test model relationships
LeadTest::test_has_many_activities()
```

### Feature Tests
**Focus**: HTTP requests, Livewire components, full workflows

**Examples**:
```php
// Test registration flow
CompanyRegistrationTest::test_company_can_be_registered()

// Test Kanban move
KanbanTest::test_lead_status_can_be_changed()

// Test call wrap-up
CallManagementTest::test_wrap_up_is_mandatory()
```

### Browser Tests (Dusk)
**Focus**: JavaScript interactions, SPA-like behavior

**Examples**:
```php
// Test drag and drop
KanbanDragDropTest::test_lead_can_be_dragged_to_new_status()

// Test BroadcastChannel
ClickToCallTest::test_call_request_sent_to_agent_console()
```

### Test Execution

**Run All Tests**:
```bash
php artisan test
```

**Run Specific Test**:
```bash
php artisan test --filter=LeadManagementTest
```

**Run with Coverage**:
```bash
php artisan test --coverage --min=80
```

**Run Browser Tests**:
```bash
php artisan dusk
```

---

## Deployment Checklist

### Pre-Deployment

#### Code Readiness
- [ ] All features implemented and tested
- [ ] All tests passing
- [ ] Code review completed
- [ ] No TODO or FIXME comments in critical code
- [ ] Documentation updated

#### Environment Setup
- [ ] Production server provisioned
- [ ] PHP 8.3+ installed
- [ ] MySQL/PostgreSQL configured
- [ ] Web server (Nginx/Apache) configured
- [ ] SSL certificate installed
- [ ] Domain DNS configured

#### Application Configuration
- [ ] `.env` file configured for production
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` set correctly
- [ ] Database credentials set
- [ ] Mail server configured
- [ ] Queue connection set to database

### Deployment Steps

1. **Clone Repository**
```bash
git clone https://github.com/your-org/laravel-livewire-crm.git
cd laravel-livewire-crm
```

2. **Install Dependencies**
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
# Edit .env with production values
```

4. **Database Migration**
```bash
php artisan migrate --force
```

5. **Optimize**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

6. **Set Permissions**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

7. **Queue Workers** (Supervisor)
```ini
[program:laravel-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
user=www-data
autostart=true
autorestart=true
```

8. **Cron Job**
```bash
* * * * * cd /path-to-app && php artisan queue:work --stop-when-empty
```

### Post-Deployment

#### Verification
- [ ] Homepage loads correctly
- [ ] Login works
- [ ] Registration works
- [ ] Dashboard accessible
- [ ] Leads Kanban loads
- [ ] Database queries working
- [ ] Queue processing
- [ ] SSL certificate valid
- [ ] WSS connection works (for SIP.js)

#### Monitoring Setup
- [ ] Log monitoring configured
- [ ] Error tracking (Sentry/Bugsnag)
- [ ] Uptime monitoring
- [ ] Performance monitoring
- [ ] Database backup scheduled

#### Security Verification
- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] Firewall rules applied
- [ ] Database access restricted

---

## Priority Matrix

### Critical Path (Must Have for MVP)
1. Agent Console with SIP.js
2. Call Wrap-Up Modal
3. Click-to-Call Integration
4. Lead Drawer Component
5. Calls Log Component
6. Call Lifecycle Management

### High Priority (Important for Full Features)
1. SIP Settings Form
2. Lead Status Settings
3. Users Management
4. Advanced Kanban features
5. Permission Tests
6. Call Management Tests

### Medium Priority (Nice to Have)
1. Call Disposition Settings
2. Activity Logging Job
3. Follow-Up Reminder Job
4. Notifications System
5. Slide-Over Components
6. Responsive Design

### Low Priority (Future Enhancements)
1. Dark Mode
2. Recording Retention Job
3. Advanced Search
4. Email Integration
5. Custom Fields
6. Advanced Analytics

---

## Estimated Timeline

### Remaining Work
- **Phase 5** (Components): 3-4 weeks
- **Phase 6** (Telephony): 2-3 weeks
- **Phase 7** (UI/UX): 1-2 weeks
- **Phase 8** (Jobs): 1 week
- **Phase 9** (Testing): 2-3 weeks
- **Phase 10** (Docs): 1 week

**Total Estimated Time**: 10-16 weeks (2.5-4 months)

### Realistic Schedule (with buffer)
- **Weeks 1-4**: Phase 5 (Components)
- **Weeks 5-7**: Phase 6 (Telephony)
- **Weeks 8-9**: Phase 7 (UI/UX)
- **Week 10**: Phase 8 (Jobs)
- **Weeks 11-13**: Phase 9 (Testing)
- **Week 14**: Phase 10 (Documentation)
- **Weeks 15-16**: Buffer for issues and polish

**Total**: ~4 months for complete implementation

---

## Success Criteria

### Technical Criteria
✅ All 10 database tables implemented  
✅ Multi-tenant isolation working perfectly  
✅ All 11 models with relationships  
✅ 8+ tests passing (target: 40+ tests)  
⏳ Agent Console with SIP.js functional  
⏳ Click-to-call working end-to-end  
⏳ Call wrap-up enforced  
⏳ All Livewire components implemented  
⏳ 80%+ code coverage  

### Business Criteria
⏳ Company can register and start using immediately  
⏳ Users can manage leads via Kanban  
⏳ Users can make calls from CRM  
⏳ All calls have disposition and notes  
⏳ Activity audit trail complete  
⏳ Role-based permissions working  

### Performance Criteria
⏳ Page load time < 2 seconds  
⏳ Kanban board loads < 1 second  
⏳ Database queries optimized (< 50 queries per page)  
⏳ Queue jobs process < 30 seconds  

---

**Version**: 1.0  
**Last Updated**: 2026-01-19  
**Status**: Active Development  
**Next Review**: Weekly
