{{--
|--------------------------------------------------------------------------
| About Page View
|--------------------------------------------------------------------------
|
| This view displays the public "About" page of the website.
| Accessible by all users (guests and authenticated users).
|
| Key route:
| - GET /about → about
|
| Middleware:
| - 'web' (session, CSRF, cookies)
|
| Typical content:
| - Site description, mission statement
| - Team or company information
| - Links to contact or other public pages
|
| Data passed to this view:
| - None by default (static content)
|
| Security notes:
| - Do not expose user-specific information
| - Sanitize all dynamic content if added
| - Ensure Blade escaping is used for any variables
|
--}}
