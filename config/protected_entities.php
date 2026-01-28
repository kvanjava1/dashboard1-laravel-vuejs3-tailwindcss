<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Protected Entities Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines accounts and roles that are protected from
    | deletion and modification. These entities are critical to the system
    | and should not be altered through normal user management interfaces.
    |
    */

    'protected_accounts' => [
        /*
        |--------------------------------------------------------------------------
        | Protected User Accounts
        |--------------------------------------------------------------------------
        |
        | Define user accounts that cannot be deleted or have their roles modified.
        | Protection is based on email address and/or role assignment.
        |
        | Format:
        | 'email@domain.com' => [
        |     'protect_deletion' => true,      // Cannot be deleted
        |     'protect_role_change' => true,   // Cannot have roles changed
        |     'protect_profile_update' => true, // Can only update password/image
        |     'reason' => 'System critical account'
        | ]
        |
        */

        'super@admin.com' => [
            'protect_deletion' => true,
            'protect_role_change' => true,
            'protect_profile_update' => true,
            'reason' => 'Super administrator account - critical system access'
        ],

        // Add more protected accounts as needed
        // 'admin@company.com' => [
        //     'protect_deletion' => true,
        //     'protect_role_change' => false,
        //     'protect_profile_update' => false,
        //     'reason' => 'Company administrator'
        // ],
    ],

    'protected_roles' => [
        /*
        |--------------------------------------------------------------------------
        | Protected Roles
        |--------------------------------------------------------------------------
        |
        | Define roles that cannot be deleted or modified. These roles are
        | essential to the system's permission structure.
        |
        | Format:
        | 'role_name' => [
        |     'protect_deletion' => true,      // Cannot be deleted
        |     'protect_modification' => true,  // Cannot be modified
        |     'reason' => 'Critical system role'
        | ]
        |
        */

        'super_admin' => [
            'protect_deletion' => true,
            'protect_modification' => true,
            'reason' => 'Super administrator role - highest system privileges'
        ],

        // Add more protected roles as needed
        // 'administrator' => [
        //     'protect_deletion' => false,
        //     'protect_modification' => true,
        //     'reason' => 'Administrator role - protected from modification'
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Protection Behavior
    |--------------------------------------------------------------------------
    |
    | Configure how the protection system behaves when violations occur.
    |
    */

    'behavior' => [
        'throw_exceptions' => true,           // Throw exceptions on violations
        'log_violations' => true,             // Log protection violations
        'exception_code' => 403,              // HTTP status code for exceptions
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Override
    |--------------------------------------------------------------------------
    |
    | Emergency override settings for critical maintenance.
    | WARNING: Only enable during emergency maintenance windows.
    |
    */

    'emergency_override' => [
        'enabled' => false,                   // Master override switch
        'allowed_ips' => [],                  // IP addresses allowed to override
        'require_confirmation' => true,       // Require additional confirmation
    ],
];