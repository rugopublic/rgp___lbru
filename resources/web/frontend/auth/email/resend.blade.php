{{--
|--------------------------------------------------------------------------
| Resend Email Confirmation
|--------------------------------------------------------------------------
|
| Route: POST /auth/resend
|
| Purpose:
|   Allows a guest user to request a new confirmation email if their
|   previous email verification link has expired or was not received.
|
| Data expected in the request:
|   - email (required) — the email address to resend the confirmation to
|
| Flow context:
|   → User submits email via POST /auth/resend
|   → Middleware: guest (already applied globally)
|   → Input validation: email format, required
|   → Throttle check per IP and per email to prevent abuse
|   → Lookup registration_request or user in DB
|       ├─ If email is already confirmed → do nothing
|       └─ If pending → generate a new token or reuse valid one
|   → Send confirmation email (with cooldown)
|       └─ Include magic link with token
|   → Log attempt (IP, masked email, timestamp)
|   → Return neutral message:
|       "If a confirmation request exists, you will receive an email shortly"
|
| Security/UX:
|   - Do not reveal whether the email exists
|   - Strict rate-limiting to prevent spam
|   - Mask email in UI and logs
|   - CSRF protection required
|
| View relation:
|   - After submission, user typically remains on the same page or sees a neutral
|     message indicating an email will be sent if the request exists
|   - Success/failure is never revealed explicitly to prevent enumeration attacks
--}}
