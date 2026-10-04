<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Deletion Grace Period
    |--------------------------------------------------------------------------
    |
    | This value defines the number of days that must pass after a user
    | has been softly deleted before the account is permanently removed
    | from the system. The term "grace period" is used analogously to
    | banking terminology, indicating a temporary recovery window.
    |
    */

    'delete_grace_days' => env('USER_DELETE_GRACE_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | User Deletion Grace Period: Notify At Days Remaining
    |--------------------------------------------------------------------------
    |
    | This value defines the number of days that must remain after a user
    | has been softly deleted until the account is permanently removed
    | from the system when the user should be notified on their deletion.
    |
    */

    'delete_grace_notify_days' => env('USER_GRACE_NOTIFY_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | User Email Change: Cooldowns & Token TTL
    |--------------------------------------------------------------------------
    |
    | All values should be specified in seconds.
    |
    | `email_ password_cooldown`: Blocks email change for the specified
    |                             duration in seconds after the user changes
    |                             their password.
    | `email_email_cooldown`: Blocks email change for the specified duration in
    |                         seconds after the user changes their email.
    | `email_request_cooldown`: Blocks email change for the specified duration
    |                           after cancelling the request to change email.
    | `email_resend_cooldown`: Blocks the function to resend the verification
    |                          email for the specified amount of time since
    |                          the last sending by that user.
    | `email_token_ttl`: The time which the token in the email link will
    |                    be valid for.
    |
    */

    'email_password_cooldown' => env('USER_EMAIL_CHANGE_PASSWORD_COOLDOWN', 864000),
    'email_email_cooldown' => env('USER_EMAIL_CHANGE_EMAIL_COOLDOWN', 2592000),
    'email_request_cooldown' => env('USER_EMAIL_CHANGE_REQUEST_COOLDOWN', 44200),
    'email_resend_cooldown' => env('USER_EMAIL_CHANGE_RESEND_COOLDOWN', 60),
    'email_token_ttl' => env('USER_EMAIL_CHANGE_TOKEN_TTL', 259200)
];
