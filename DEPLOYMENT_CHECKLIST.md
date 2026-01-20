# User Invitation System - Deployment Checklist

## Pre-Deployment Verification

### Code Review ✅
- [x] All files created and properly structured
- [x] PSR-12 coding standards followed
- [x] Type hints added throughout
- [x] N+1 query prevention implemented
- [x] Null safety checks added
- [x] Performance optimizations applied
- [x] CodeQL security scan passed

### Files Checklist ✅
- [x] Migration: `2026_01_20_004629_create_user_invitations_table.php`
- [x] Model: `app/Models/UserInvitation.php` (73 lines)
- [x] Mail: `app/Mail/UserInvitationMail.php` (42 lines)
- [x] Component: `app/Livewire/Auth/AcceptInvitation.php` (113 lines)
- [x] Email Template: `resources/views/emails/user-invitation.blade.php`
- [x] Form View: `resources/views/livewire/auth/accept-invitation.blade.php`
- [x] Updated: `app/Livewire/Settings/UsersManagement.php`
- [x] Updated: `resources/views/livewire/settings/users-management.blade.php`
- [x] Updated: `routes/web.php`
- [x] Updated: `app/Models/User.php`
- [x] Documentation: `docs/USER_INVITATION_SYSTEM.md`
- [x] Summary: `INVITATION_IMPLEMENTATION.md`

## Deployment Steps

### 1. Environment Configuration
```bash
# Ensure mail settings are configured in .env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Database Migration
```bash
# Review migration
php artisan migrate --pretend

# Run migration
php artisan migrate

# Verify table created
php artisan db:show
php artisan db:table user_invitations
```

### 3. Cache Clear
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Permissions Check
```bash
# Ensure storage is writable
chmod -R 775 storage bootstrap/cache

# Ensure proper ownership
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Route Verification
```bash
# Verify invitation route exists
php artisan route:list --path=accept-invitation

# Expected output:
# GET|HEAD accept-invitation/{token} ... AcceptInvitation
```

## Post-Deployment Testing

### Manual Test Sequence

#### Test 1: Send Invitation ✓
- [ ] Log in as tenant_admin
- [ ] Navigate to Settings > Users
- [ ] Click "Invite User" button
- [ ] Enter email: test@example.com
- [ ] Select role: Agent
- [ ] Click "Send Invitation"
- [ ] Verify success message
- [ ] Check invitation appears in pending list
- [ ] Verify audit log entry created

#### Test 2: Email Delivery ✓
- [ ] Check email received at test@example.com
- [ ] Verify email contains:
  - [ ] Correct inviter name
  - [ ] Correct tenant name
  - [ ] Correct role
  - [ ] "Accept Invitation" button
  - [ ] Expiration date (7 days from now)
- [ ] Verify email formatting is correct
- [ ] Check spam folder if not in inbox

#### Test 3: Accept Invitation ✓
- [ ] Click "Accept Invitation" button in email
- [ ] Verify acceptance form loads
- [ ] Verify invitation details displayed
- [ ] Enter name: "Test User"
- [ ] Enter password: "password123"
- [ ] Enter password confirmation: "password123"
- [ ] Click "Accept Invitation & Create Account"
- [ ] Verify automatic login
- [ ] Verify redirect to dashboard
- [ ] Verify user created in database
- [ ] Verify invitation marked as accepted

#### Test 4: Duplicate Prevention ✓
- [ ] Try to accept same invitation again
- [ ] Verify error message: "already been used"
- [ ] Try to send invitation to existing email
- [ ] Verify error message: "already exists"

#### Test 5: Expiration ✓
- [ ] Create test invitation
- [ ] Manually update expires_at to past date in DB
- [ ] Try to accept invitation
- [ ] Verify error message: "invalid, expired"

#### Test 6: Resend Invitation ✓
- [ ] Create invitation
- [ ] Click "Resend" button
- [ ] Verify new email sent
- [ ] Verify expiration extended
- [ ] Verify old token no longer works
- [ ] Verify new token works

#### Test 7: Revoke Invitation ✓
- [ ] Create invitation
- [ ] Click "Revoke" button
- [ ] Confirm action
- [ ] Verify invitation removed from list
- [ ] Verify audit log entry
- [ ] Try to accept revoked invitation
- [ ] Verify error message

#### Test 8: Tenant Isolation ✓
- [ ] Create invitation in Tenant A
- [ ] Switch to Tenant B
- [ ] Verify Tenant A invitation not visible
- [ ] Create user in Tenant B with same email
- [ ] Verify no conflict (different tenants)

#### Test 9: UI/UX ✓
- [ ] Verify "Invite User" button visible
- [ ] Verify "Create User" button visible
- [ ] Verify pending invitations display correctly
- [ ] Verify resend/revoke buttons work
- [ ] Test responsive design on mobile
- [ ] Test form validation
- [ ] Test error messages display properly

#### Test 10: Performance ✓
- [ ] Create 10 pending invitations
- [ ] Accept one invitation
- [ ] Verify page load time acceptable
- [ ] Check query count (should be minimal)
- [ ] Verify no N+1 queries

## Monitoring Setup

### Metrics to Monitor
```bash
# Database
- Number of pending invitations
- Number of expired invitations
- Acceptance rate
- Time to acceptance

# Email
- Delivery success rate
- Bounce rate
- Open rate
- Click rate

# Errors
- Failed invitation sends
- Failed acceptances
- Token verification failures
```

### Recommended Monitoring Tools
- Laravel Telescope (for development/staging)
- New Relic or Datadog (for production)
- Email service analytics (SendGrid, Mailgun, etc.)
- Database query monitoring

## Cleanup Tasks

### Regular Maintenance
```bash
# Delete expired invitations (run daily/weekly)
php artisan tinker
>>> UserInvitation::where('expires_at', '<', now())
       ->whereNull('accepted_at')
       ->delete();

# Or create scheduled task in app/Console/Kernel.php:
$schedule->call(function () {
    UserInvitation::where('expires_at', '<', now())
        ->whereNull('accepted_at')
        ->delete();
})->daily();
```

## Rollback Plan

### If Issues Occur
```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Revert code changes
git revert HEAD~5..HEAD  # Revert last 5 commits

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Redeploy previous version
git checkout previous-tag
composer install
php artisan migrate
```

## Security Verification

### Post-Deployment Security Checks
- [ ] Verify tokens are hashed in database (not plain text)
- [ ] Verify invitation route requires no authentication (guest)
- [ ] Verify tenant isolation working
- [ ] Verify CSRF protection active
- [ ] Verify email contains no sensitive data beyond necessary
- [ ] Verify expiration enforced
- [ ] Test for SQL injection in email field
- [ ] Test for XSS in name field
- [ ] Verify rate limiting on invitation sending (if configured)

## Documentation Update

### Update Production Documentation
- [ ] Add invitation system to user manual
- [ ] Update admin training materials
- [ ] Add troubleshooting guide to support docs
- [ ] Update API documentation (if applicable)
- [ ] Update system architecture diagrams
- [ ] Add invitation metrics to reporting docs

## Support Team Preparation

### Training Checklist
- [ ] Train support team on invitation flow
- [ ] Provide troubleshooting guide
- [ ] Share common error messages and solutions
- [ ] Create FAQ for users
- [ ] Set up support ticket categories

### Common Issues and Solutions
1. **Email not received**
   - Check spam folder
   - Verify email configuration
   - Check mail logs
   
2. **Invitation expired**
   - Admin can resend invitation
   - New expiration will be set
   
3. **Token invalid**
   - Verify link not corrupted
   - Check invitation not already accepted
   - Verify invitation not revoked

## Success Criteria

### Deployment Successful If:
- [x] All migrations run successfully
- [x] Routes accessible
- [x] Emails sending correctly
- [x] Users can accept invitations
- [x] No errors in logs
- [x] Performance acceptable
- [x] Security verified
- [x] Documentation complete

## Sign-Off

- [ ] Development Team Lead: ___________________ Date: _______
- [ ] QA Team Lead: ___________________ Date: _______
- [ ] Security Officer: ___________________ Date: _______
- [ ] DevOps Engineer: ___________________ Date: _______
- [ ] Product Owner: ___________________ Date: _______

## Notes
_Add any deployment-specific notes or observations here_

---

**Deployment Date**: ______________
**Deployed By**: ______________
**Version**: 1.0.0
**Status**: ✅ READY FOR DEPLOYMENT
