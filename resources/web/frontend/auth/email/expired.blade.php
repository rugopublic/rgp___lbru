{{--
|--------------------------------------------------------------------------
| Email Confirmation — Token Expired
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.email-expired
|
| Purpose:
|   Displayed when the email confirmation token has expired.
|   The user must request a new confirmation email.
|
| Data passed to the view:
|   - $email (optional, partially masked) — to show which email the token was for
|
| Typical content:
|   - Message: "The confirmation link has expired."
|   - Button/link: "Request a new confirmation email" → POST /auth/resend
|   - Optional note about waiting period between requests (rate-limit)
|
| Flow context:
|   → User clicked the confirmation link from email
|   → Token was invalid or expired
|   → User is not yet confirmed
|
| Security/UX:
|   - Mask the email partially
|   - Do not disclose whether the email exists in the system
|   - Clearly instruct the user to request a new confirmation email
|   - Enforce rate-limiting on resending emails
|
| Route relation:
|   GET /auth/confirm/{token} → token expired → render expired.blade.php
--}}
