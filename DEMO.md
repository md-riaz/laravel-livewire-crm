# Demo Guide

This document provides a quick guide to set up and explore the Laravel Livewire CRM application.

## Quick Setup with SQLite

The project is already configured to use SQLite for easy setup and testing.

### 1. Installation Steps

```bash
# Clone the repository
git clone https://github.com/md-riaz/laravel-livewire-crm.git
cd laravel-livewire-crm

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start the server
php artisan serve
```

### 2. Access the Application

Visit `http://localhost:8000` in your browser.

### 3. Register Your Company

1. Navigate to the **Register** page
2. Fill in the registration form:
   - **Company Name**: e.g., "Acme Corporation"
   - **Your Name**: e.g., "John Smith"
   - **Email**: e.g., "john.smith@acmecorp.com"
   - **Password**: Choose a secure password
   - **Timezone**: Select your timezone
3. Click **Register Company**
4. You'll be automatically logged in and redirected to the dashboard

### 4. Demo Credentials Used in Screenshots

For the demo screenshots in this repository, the following test account was created:

- **Company**: Acme Corporation
- **Name**: John Smith
- **Email**: john.smith@acmecorp.com
- **Password**: SecurePass123!
- **Role**: tenant_admin (Owner)

**Note**: These are demo credentials. You should create your own account when setting up the application.

### 5. Explore the Features

#### Dashboard
- View key metrics: Total Leads, Active Calls, Team Members
- Quick overview of your CRM system

#### Leads Management
- Access the **Leads** page from the sidebar
- View the Kanban board with default statuses:
  - New
  - Contacted
  - Qualified
  - Proposal Sent
  - Won
  - Lost
- Create new leads using the **+ New Lead** button
- Drag and drop leads between status columns

#### Call Logs
- Access the **Calls** page to view call history
- Filter by date, agent, and disposition
- Search by phone number or notes

#### Agent Console
- Access the **Agent Console** for SIP phone integration
- Configure SIP credentials for making calls

#### Settings (Admin Only)
- **My SIP Credentials**: Configure your personal SIP credentials
- **Lead Statuses**: Customize lead statuses for your organization
- **Call Dispositions**: Manage call outcome options
- **Users Management**: Invite and manage team members

### 6. Multi-Tenant Features

The application is built with multi-tenancy in mind:

- Each company (tenant) has completely isolated data
- Users can only see data from their own tenant
- Perfect for SaaS deployment where multiple companies use the same application
- Tenant ID is automatically applied to all queries

### 7. Testing

Run the test suite to verify everything is working:

```bash
php artisan test
```

You should see all 8 tests passing, covering:
- Company registration
- Tenant isolation
- Default data seeding
- Authentication flows

## Screenshots

See the [README.md](README.md) for visual demos of all major features.

## Need Help?

- Check the [README.md](README.md) for detailed documentation
- Review the [IMPLEMENTATION_COMPLETE.md](IMPLEMENTATION_COMPLETE.md) for technical details
- Open an issue on GitHub for bugs or feature requests

---

**Happy CRM-ing! 🎉**
