{{--
|--------------------------------------------------------------------------
| Email Verification Notice View
|--------------------------------------------------------------------------
|
| This view is displayed when a registered user must verify their email
| address before gaining full access to the application.
|
| By default, this route is protected by the "verified" middleware and
| is accessible via the route name "verification.notice".
|
| Reference: Laravel 12.x Official Documentation
| Section: Authentication > Email Verification
| URL: https://laravel.com/docs/12.x/verification
|
| Key routes involved:
| - GET  /email/verify ....................... → verification.notice
| - POST /email/verification-notification .... → verification.send
| - GET  /email/verify/{id}/{hash} ........... → verification.verify
|
| When the user clicks the verification link sent via the
| VerifyEmail notification, they are redirected to the route
| named "verification.verify", which marks the email as verified
| and typically redirects to RouteServiceProvider::HOME.
|
| This view may contain:
| - Instructions for verifying email.
| - "Resend verification link" form (POST to verification.send).
| - Optional logout form.
|
--}}
