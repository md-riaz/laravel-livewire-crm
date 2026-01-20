# Laravel Livewire CRM

A production-ready, multi-tenant SaaS CRM application built with Laravel 11, Livewire v3, Alpine.js, and Tailwind CSS.

## Features Implemented

### ✅ Core Infrastructure
- **Multi-Tenant Architecture**: Complete tenant isolation with global scopes
- **Authentication System**: Session-based authentication with company registration
- **Role-Based Permissions**: Flexible permission system (tenant_admin, sales_agent, support_agent, supervisor, read_only)
- **Database Schema**: 10 tables with proper relationships and indexes

### ✅ Leads Management
- **Dynamic Lead Statuses**: Fully configurable per tenant (no hard-coded statuses)
- **Kanban Board**: Drag-and-drop interface with real-time updates
- **Lead Scoring**: Hot/Warm/Cold classification
- **Activity Tracking**: Automatic logging of status changes
- **Assignment Management**: Assign leads to team members

### ✅ User Interface
- **Responsive Design**: Custom Tailwind CSS implementation
- **Alpine.js Integration**: SPA-like behavior without a framework
- **Dashboard**: Overview of key metrics
- **Livewire Components**: Reactive components with no full-page reloads

### ✅ Testing
- **Feature Tests**: Company registration and tenant isolation
- **Unit Tests**: Tenant scoping and data isolation
- **8 Passing Tests**: Comprehensive test coverage for core functionality

## Technology Stack

- **PHP**: 8.3
- **Laravel**: 11 (latest stable)
- **Livewire**: v3
- **Alpine.js**: Client-side reactivity
- **Tailwind CSS**: Custom utility-first CSS
- **Database**: MySQL 8 / PostgreSQL 15 / SQLite
- **Queue Driver**: Laravel database queue
- **Testing**: PHPUnit + Laravel test framework

## Installation

### Prerequisites
- PHP 8.3 or higher
- Composer
- Node.js & npm
- MySQL 8 / PostgreSQL 15 / SQLite

### Setup Steps

1. **Clone the repository**
```bash
git clone https://github.com/md-riaz/laravel-livewire-crm.git
cd laravel-livewire-crm
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database** (edit `.env`)
```env
DB_CONNECTION=sqlite
# Or for MySQL/PostgreSQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=crm
# DB_USERNAME=root
# DB_PASSWORD=
```

6. **Run migrations**
```bash
php artisan migrate
```

7. **Build frontend assets**
```bash
npm run build
# Or for development:
npm run dev
```

8. **Start the development server**
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## Usage

### Company Registration

1. Navigate to `/register-company`
2. Fill in:
   - Company name
   - Owner name
   - Email address
   - Password
   - Timezone
3. System automatically:
   - Creates tenant
   - Creates owner user with `tenant_admin` role
   - Seeds default lead statuses (New, Contacted, Qualified, Proposal Sent, Won, Lost)
   - Seeds default call dispositions
   - Logs in the owner

### Managing Leads

1. Navigate to **Leads** from the sidebar
2. View leads in Kanban board organized by status
3. **Create Lead**: Click "+ New Lead" button
4. **Move Lead**: Drag and drop between status columns
5. Lead properties:
   - Name (required)
   - Company name
   - Email
   - Phone
   - Source
   - Score (Hot/Warm/Cold)
   - Estimated value
   - Assigned team member

### Tenant Isolation

All data is automatically scoped to the authenticated user's tenant:
- Users can only see their own tenant's data
- Queries are automatically filtered by `tenant_id`
- Creating records automatically sets the current tenant
- Cross-tenant data access is impossible

## Database Schema

### Core Tables
- `tenants` - Company/tenant information
- `users` - User accounts with tenant relationship
- `permissions` - Role-based permissions per tenant

### Leads System
- `lead_statuses` - Dynamic, tenant-configured statuses
- `leads` - Lead records with full CRM data
- `lead_activities` - Activity timeline for each lead

### Telephony (Schema Ready)
- `agent_sip_credentials` - Per-agent SIP credentials (encrypted)
- `calls` - Call records with related entities
- `call_dispositions` - Tenant-configured call outcomes

### Audit
- `audit_logs` - System-wide activity logging

## Testing

Run the test suite:

```bash
# All tests
php artisan test

# Specific test
php artisan test --filter=CompanyRegistrationTest

# With coverage
php artisan test --coverage
```

Current test coverage:
- ✅ Company registration workflow
- ✅ Tenant isolation and data scoping
- ✅ Default seeding (lead statuses, call dispositions)
- ✅ Validation and business rules

## Architecture Highlights

### Multi-Tenancy Implementation

**Global Scope Trait** (`BelongsToTenant`):
```php
use App\Traits\BelongsToTenant;

class Lead extends Model {
    use BelongsToTenant; // Automatic tenant filtering
}
```

**Automatic Tenant Assignment**:
```php
// Tenant ID is automatically set on create
Lead::create(['name' => 'John Doe']); 
// tenant_id is populated from auth()->user()->tenant_id
```

### Service Layer Pattern

**TenantService**: Encapsulates complex tenant operations
- Creates tenant with owner
- Seeds default data
- Maintains data consistency in transactions

### Livewire Components

**Reactive Components**:
- `CompanyRegistration` - Multi-step registration form
- `Login` - Session-based authentication
- `Leads\Kanban` - Drag-and-drop lead management
- `Leads\CreateLeadModal` - Modal form with validation

## Configuration

### Queue Configuration

Edit `config/queue.php`:
```php
'default' => env('QUEUE_CONNECTION', 'database'),
```

Run queue worker:
```bash
php artisan queue:work --stop-when-empty
```

### Cron Setup (Production)

Add to crontab:
```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## Deployment

### Requirements
- HTTPS enabled
- WSS support (for future SIP.js integration)
- PHP 8.3+
- Database server
- Composer
- Node.js & npm

### Environment Variables
```env
APP_URL=https://your-domain.com
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
```

### Deployment Steps
1. Clone repository
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm install && npm run build`
4. Configure `.env`
5. Run `php artisan migrate --force`
6. Set up queue workers
7. Configure web server (Nginx/Apache)

## Security Features

- ✅ CSRF protection enabled
- ✅ Password hashing (bcrypt)
- ✅ Tenant data isolation (server-side)
- ✅ SIP credential encryption
- ✅ Input validation
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Blade templating)

## Future Enhancements

The architecture supports these planned features:
- Agent Console with SIP.js WebRTC integration
- Click-to-call using BroadcastChannel API
- Call recordings with retention policies
- Real-time notifications
- Advanced reporting and analytics
- Email integration
- Task management

## Contributing

This is a production-ready foundation. Contributions are welcome:
1. Fork the repository
2. Create a feature branch
3. Write tests for new features
4. Submit a pull request

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues and questions:
- GitHub Issues: https://github.com/md-riaz/laravel-livewire-crm/issues
- Documentation: See code comments and this README

---

**Built with Laravel 11, Livewire v3, Alpine.js, and Tailwind CSS**
