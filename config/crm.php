<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lead Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for lead management behavior including scoring,
    | assignment rules, and activity logging preferences.
    |
    */
    'leads' => [
        // Default lead score when created
        'default_score' => env('CRM_DEFAULT_LEAD_SCORE', 'warm'),

        // Require lead assignment on creation
        'require_assignment' => env('CRM_REQUIRE_LEAD_ASSIGNMENT', false),

        // Automatically create activity logs for lead actions
        'auto_create_activities' => env('CRM_AUTO_LOG_ACTIVITIES', true),

        // Valid lead scores
        'valid_scores' => ['hot', 'warm', 'cold'],

        // Enable lead score auto-adjustment based on engagement
        'auto_adjust_score' => env('CRM_AUTO_ADJUST_SCORE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Call Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for call management including wrap-up requirements,
    | recording settings, and automatic lead linking.
    |
    */
    'calls' => [
        // Require call wrap-up with disposition
        'mandatory_wrapup' => env('CRM_MANDATORY_WRAPUP', true),

        // Automatically link calls to leads when phone number matches
        'auto_link_to_lead' => env('CRM_AUTO_LINK_CALLS', true),

        // Call recording retention period in days
        'recording_retention_days' => env('CRM_RECORDING_RETENTION', 90),

        // Enable call recording by default
        'enable_recording' => env('CRM_ENABLE_RECORDING', true),

        // Maximum call duration in seconds (0 = unlimited)
        'max_duration_seconds' => env('CRM_MAX_CALL_DURATION', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific CRM features for your organization.
    | Useful for gradual rollout or tenant-specific configurations.
    |
    */
    'features' => [
        // Enable telephony features
        'telephony' => env('CRM_FEATURE_TELEPHONY', true),

        // Enable email integration
        'email_integration' => env('CRM_FEATURE_EMAIL', false),

        // Enable advanced reporting and analytics
        'advanced_reporting' => env('CRM_FEATURE_REPORTING', false),

        // Enable SMS notifications
        'sms_notifications' => env('CRM_FEATURE_SMS', false),

        // Enable workflow automation
        'workflow_automation' => env('CRM_FEATURE_WORKFLOWS', false),

        // Enable API access
        'api_access' => env('CRM_FEATURE_API', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for activity logging and audit trails.
    |
    */
    'activities' => [
        // Automatically purge old activities after X days
        'purge_after_days' => env('CRM_PURGE_ACTIVITIES_AFTER', 365),

        // Activity types to track
        'tracked_types' => [
            'created',
            'updated',
            'assigned',
            'status_changed',
            'call_started',
            'call_ended',
            'email_sent',
            'note_added',
            'followup_scheduled',
        ],

        // Lead fields to track changes for
        'tracked_fields' => [
            'name',
            'email',
            'phone',
            'company_name',
            'score',
            'estimated_value',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pipeline Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for lead processing pipeline stages.
    |
    */
    'pipeline' => [
        // Enable data enrichment from external sources
        'enable_enrichment' => env('CRM_ENABLE_ENRICHMENT', false),

        // Validate lead data strictly
        'strict_validation' => env('CRM_STRICT_VALIDATION', true),

        // Send notifications on lead assignment
        'notify_on_assignment' => env('CRM_NOTIFY_ASSIGNMENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Rules
    |--------------------------------------------------------------------------
    |
    | Configurable business rules and constraints.
    |
    */
    'rules' => [
        // Minimum estimated value for qualified leads
        'min_qualified_value' => env('CRM_MIN_QUALIFIED_VALUE', 0),

        // Days until a lead is considered stale
        'stale_lead_days' => env('CRM_STALE_LEAD_DAYS', 30),

        // Maximum number of leads per user
        'max_leads_per_user' => env('CRM_MAX_LEADS_PER_USER', 0),

        // Require approval for high-value leads
        'high_value_threshold' => env('CRM_HIGH_VALUE_THRESHOLD', 10000),
    ],
];
