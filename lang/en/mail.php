<?php

return [

    'user' => [
        'email_change' => [
            'new_sent_successfully' => 'Verification email sent! Please check your email.',
            'new_verified_successfully' => 'New email successfully verified!',
            'check_old_email' => 'Check your current email to complete the process.',
            'confirmed_successfully' => 'Email successfully changed!',
            'email_changed_successfully' => 'Email successfully changed!',
            'resent_successfully' => 'Email successfully resent!',
            'cancelled_successfully' => 'Email change process cancelled.',
        ],
        'errors' => [
            'password_cooldown_active' => "You cannot change your email because you recently changed your password. Please try again later.",
            'email_cooldown_active' => "You cannot change your email because you recently updated it. Please try again later.",
            'request_cooldown_active' => "You cannot change your email because you recently cancelled an email change request. Please try again later.",
            'request_currently_active' => "You already have an active email change request. Please cancel or complete it first.",
            'token_expired' => "The link has expired. Please request a new one.",
            'token_not_found' => "The link is invalid. Please check it or request a new one."
        ],
        'deletion_grace' => [
            'subject' => 'Hi :name, your account will be permanently deleted in :days days',
            'greeting' => 'Hello :name,',
            'body' => 'We wanted to let you know that your account is scheduled for permanent deletion soon. You have :days day(s) left to take any action.',
            'button_text' => 'Manage Account',
            'footer' => 'If you think this is a mistake, please contact support.',
        ],
        'deletion_grace_started' => [
            'subject' => 'Hi :name, your account will be permanently deleted in :days days',
            'greeting' => 'Hello :name,',
            'intro' => 'Your account has been **soft deleted** and is now in the **grace period**.',
            'period' => 'You have **:days days** to restore your account before it is permanently deleted.',
            'button' => 'Restore Account',
            'warning' => 'If you do not restore your account within this period, all your data will be permanently removed.',
        ],
        'deletion_grace_expired' => [
            'subject' => 'Your account has been permanently deleted',
            'greeting' => 'Hello :name,',
            'intro' => 'Your account has reached the end of the grace period and has been permanently deleted.',
            'thanks' => 'Thank you for using our service. We hope to see you again!',
        ],
        'email_change_verify_new' => [
            'subject' => 'A request from :name to attach this email',
            'intro' => 'You requested to attach a new email address to your account.',
            'request' => 'Please **verify ownership of the new email** by clicking the button below:',
            'button' => 'Confirm attachment',
            'warning' => 'The link will expire in :duration hours.',
        ],
        'email_change_confirm_old' => [
            'subject' => 'Confirm new email attachment for :name',
            'intro' => 'We have received confirmation that you want to attach a new email to your account.',
            'info' => 'New email: :new_email',
            'request' => 'Please **verify your intention** by clicking the button below:',
            'button' => 'Confirm attachment',
            'warning' => 'If you do not click the button, the attachment will happen automatically in :days days.',
        ],
    ],

];
