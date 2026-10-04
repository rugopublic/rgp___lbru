{{--
|--------------------------------------------------------------------------
| Contact Page View
|--------------------------------------------------------------------------
|
| This view displays the public "Contact" page of the website.
| Accessible by all users (guests and authenticated users).
|
| Key route:
| - GET /contact → contact
|
| Middleware:
| - 'web' (session, CSRF, cookies)
|
| Typical content:
| - Contact information (address, phone, email)
| - Optional contact form for user messages
| - Optional map or social media links
|
| Data passed to this view:
| - None by default (static content)
| - If a contact form exists, validation errors and old input may be passed
|
| Security notes:
| - Ensure CSRF protection for any forms
| - Validate and sanitize user-submitted data
| - Do not expose sensitive internal information
|
--}}
