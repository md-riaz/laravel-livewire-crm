# User Invitation System Documentation

## Overview
The user invitation system allows tenant administrators to invite new users via email instead of creating accounts directly. Invited users can set their own passwords upon acceptance.

## Features
- Email-based invitation system
- Secure token generation and hashing
- 7-day expiration period
- One-time use tokens
- Tenant-scoped validation
- Resend and revoke capabilities
- Comprehensive audit logging

## Database Schema

### user_invitations Table
```sql
- id: Primary key
- tenant_id: Foreign key to tenants table
- email: Invitee's email address (max 255 chars)
- token: Hashed invitation token (max 255 chars)
- role: User role (agent, supervisor, tenant_admin)
- invited_by_user_id: Foreign key to users table
- expires_at: Expiration timestamp (7 days from creation)
- accepted_at: Acceptance timestamp (null until accepted)
- created_at: Creation timestamp
- updated_at: Last update timestamp
```

### Indexes
- `(expires_at, accepted_at)`: For efficient lookup of pending invitations
- `(tenant_id, email)`: For checking existing invitations per tenant

## Security Features

### Token Security
- Tokens are 64-character random strings generated using `Str::random(64)`
- Tokens are hashed with bcrypt before database storage
- Plain tokens are only sent via email, never stored
- Token verification uses `Hash::check()` for secure comparison

### Validation
- Email uniqueness validated per tenant (both users and pending invitations)
- Expiration checked on every access attempt
- Tenant-scoped queries throughout
- One-time use enforcement via `accepted_at` field

### CSRF Protection
- All forms protected by Livewire's built-in CSRF token handling

## Usage

### Inviting a User
1. Navigate to Settings > Users Management
2. Click "Invite User" button
3. Enter email address and select role
4. Click "Send Invitation"
5. System sends email and logs audit entry

### Accepting an Invitation
1. User receives email with unique invitation link
2. User clicks link and is taken to acceptance page
3. User enters name and password
4. System creates user account and logs them in
5. Invitation is marked as accepted

### Managing Invitations
- **Resend**: Generates new token and extends expiration by 7 days
- **Revoke**: Deletes invitation (cannot be undone)
- Pending invitations are displayed in yellow banner

## Email Template
The invitation email includes:
- Clear subject line with tenant name
- Inviter's name
- Assigned role
- Large "Accept Invitation" button
- Expiration date and time
- Help text for unexpected invitations

## API / Component Methods

### UserInvitation Model
```php
// Generate a new token
$token = UserInvitation::generateToken();

// Hash and store token
$invitation->hashToken($token);

// Verify a token
$isValid = $invitation->verifyToken($plainToken);

// Check if expired
$expired = $invitation->isExpired();

// Check if accepted
$accepted = $invitation->isAccepted();

// Check if valid (not expired and not accepted)
$valid = $invitation->isValid();

// Mark as accepted
$invitation->markAsAccepted();
```

### UsersManagement Component
```php
// Open invite modal
$this->openInviteModal();

// Send invitation
$this->sendInvite();

// Resend invitation
$this->resendInvitation($invitationId);

// Revoke invitation
$this->revokeInvitation($invitationId);
```

### AcceptInvitation Component
```php
// Mount with token
public function mount(string $token)

// Accept invitation
public function accept()
```

## Audit Logging
The system logs the following actions:
- `user.invited`: When an invitation is sent
- `user.invitation_resent`: When an invitation is resent
- `user.invitation_revoked`: When an invitation is revoked

Each log entry includes:
- Tenant ID
- User ID (who performed the action)
- Action type
- Email address
- IP address
- User agent
- Timestamp

## Configuration

### Expiration Period
Default: 7 days
To change, modify the expiration in:
- `UsersManagement::sendInvite()` method
- `UsersManagement::resendInvitation()` method

```php
'expires_at' => now()->addDays(7), // Change 7 to desired days
```

### Token Length
Default: 64 characters
To change, modify in `UserInvitation::generateToken()`:
```php
return Str::random(64); // Change 64 to desired length
```

## Routes
- `GET /accept-invitation/{token}` - Invitation acceptance page (guest only)

## Permissions
- Only tenant_admin role can access Users Management
- Only tenant_admin can send, resend, or revoke invitations
- Invitations are tenant-scoped automatically

## Testing Checklist
- [ ] Send invitation to new email
- [ ] Verify email received with correct details
- [ ] Accept invitation and verify account creation
- [ ] Verify automatic login after acceptance
- [ ] Test expired invitation handling
- [ ] Test already-accepted invitation handling
- [ ] Test invalid token handling
- [ ] Test duplicate email validation
- [ ] Verify resend functionality
- [ ] Verify revoke functionality
- [ ] Test tenant isolation
- [ ] Verify audit log entries

## Troubleshooting

### Email Not Received
1. Check mail configuration in `.env`
2. Check spam folder
3. Check audit logs to confirm invitation was sent
4. Review Laravel logs for mail errors

### Token Invalid Error
1. Verify invitation hasn't expired (check `expires_at`)
2. Verify invitation hasn't been accepted (check `accepted_at`)
3. Check for copy/paste issues with token
4. Verify token in URL matches one in database (hashed comparison)

### Performance Considerations
The token verification process loads all pending invitations from the database because tokens are bcrypt-hashed. For high-traffic applications with many pending invitations:
- Consider adding a caching layer
- Implement a separate unhashed `token_identifier` field for direct lookups
- Monitor database query performance
- Set up proper database indexes

## Future Enhancements
- Customizable expiration periods
- Role-specific invitation templates
- Bulk invitation sending
- Invitation analytics dashboard
- Webhook notifications
- Custom email templates per tenant
