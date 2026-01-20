<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to Join {{ $tenantName }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin: 15px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .info-box {
            background-color: #f3f4f6;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        .expiry-notice {
            color: #dc2626;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 You've Been Invited!</h1>
        </div>

        <div class="content">
            <p>Hello,</p>
            
            <p><strong>{{ $inviterName }}</strong> has invited you to join <strong>{{ $tenantName }}</strong> on our CRM platform.</p>
            
            <div class="info-box">
                <p><strong>Your Role:</strong> {{ ucfirst(str_replace('_', ' ', $invitation->role)) }}</p>
                <p><strong>Email:</strong> {{ $invitation->email }}</p>
            </div>

            <p>Click the button below to accept the invitation and set up your account:</p>
        </div>

        <div class="button-container">
            <a href="{{ url('/accept-invitation/' . $plainToken) }}" class="button">
                Accept Invitation
            </a>
        </div>

        <div class="content">
            <p class="expiry-notice">⚠️ This invitation will expire on {{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}</p>
            
            <p>If you have any questions, please contact {{ $inviterName }} or your system administrator.</p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>If you did not expect this invitation, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
