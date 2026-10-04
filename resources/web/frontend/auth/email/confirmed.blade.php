{{--
|--------------------------------------------------------------------------
| Email Confirmation — Success
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.email-confirmed
|
| Purpose:
|   Displayed to the user after successfully confirming their email
|   via GET /auth/confirm/{token} when the token is valid and the account is activated.
|
| Data passed to the view:
|   - $user (optional) — user model, for greeting or personalization
|
| Typical content:
|   - Message: "Your email has been successfully confirmed!"
|   - Button/link: "Proceed to login" or "Go to dashboard"
|   - Optional greeting by user's name
|
| Flow context:
|   → User clicked the confirmation link from email
|   → Token was valid
|   → User email is marked as verified
|
| Security/UX:
|   - Do not expose token or account details
|   - Show only minimal necessary information
|   - Provide a clear next step (login/dashboard)
|
| Route relation:
|   GET /auth/confirm/{token} → token valid → render confirmed.blade.php
--}}
