{{--
|--------------------------------------------------------------------------
| Email Confirmation Info Page View
|--------------------------------------------------------------------------
|
| This view informs the user that a confirmation email has been sent.
| Accessible only to guests immediately after a successful POST /auth.
|
| Key route:
| - GET /auth/confirm → auth.confirm
|
| Middleware:
| - 'web'
| - 'guest'
| - Optional: custom middleware to check session flag from POST /auth
|
| Typical content:
| - Neutral message: "If you submitted a request, you will receive an email."
| - Partially masked email (optional)
| - Instructions: check inbox and click the confirmation link
| - Optional link to resend confirmation (rate-limited)
|
| Security notes:
| - Do not reveal if the email exists in system
| - Page should only be accessible after successful email submission
|
| UX notes:
| - Clearly explain next steps
| - Include cooldown info or resend link as needed
|
--}}
