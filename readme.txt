=== DPS Login Security ===
Contributors: dpsmedia
Tags: login, security, admin, authentication, brute force, rate limiting, custom login
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 6.0
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhanced WordPress login security with custom login page, rate limiting, and protection against brute force attacks.

== Description ==

DPS Login Security provides comprehensive protection for your WordPress login area with advanced security features:

* **Custom Login Page**: Hide default WordPress login and create a branded custom login experience
* **Rate Limiting**: Protect against brute force attacks with configurable attempt limits and blocking
* **Advanced Security**: Disable dangerous features like file editing, XML-RPC, and error reporting
* **Branding Options**: Customize login page with your own HTML, CSS, and branding
* **Mobile Responsive**: Fully responsive design that works on all devices

= Key Features =

* Hide default WordPress login URLs (/wp-login.php, /wp-admin)
* Custom login slug (e.g., yourdomain.com/secret-login)
* Brute force protection with rate limiting
* Custom HTML/CSS for login page left panel
* Disable WordPress file editing
* Hide WordPress version information
* Disable error reporting in production
* Compatible with Nginx/Apache environments

= Perfect For =

* Business websites needing enhanced security
* WordPress agencies managing client sites
* E-commerce stores protecting customer data
* Educational institutions
* Government websites

== Installation ==

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Settings > DPS Login Security to configure
4. Set your custom login slug and security preferences
5. Test your new login page

== Frequently Asked Questions ==

= Will this break my site? =

No. The plugin is designed to be non-intrusive and can be safely deactivated at any time.

= Does this work with multisite? =

Yes, the plugin is multisite compatible and can be network activated.

= What about my existing users? =

All existing user accounts continue to work normally. Only the login URL changes.

= Can I customize the login page? =

Yes, you can add custom HTML and CSS to create a branded login experience.

= What if I forget my custom login URL? =

You can always access the admin area directly if you're already logged in, or access via FTP to check your settings.

== Screenshots ==

1. Admin settings page showing all security options
2. Custom login page with default branding
3. Rate limiting configuration options

== Changelog ==

= 6.0 =
* Hard flush rewrite rules on activation and after slug changes
* Auto-detect missing rewrite rule and schedule flush to prevent 404
* Apply sensible defaults on activation (no need to click Save first)
* Fix open redirect after login using `wp_safe_redirect` + `wp_validate_redirect`
* Rate limiting reworked: only failed logins increment; 403 response and disabled form while blocked
* Add admin tool to clear a specific blocked IP + blocked IPs list
* Self-heal: auto-create rate-limit table if activation hook didn’t run
* Allow lost password/reset actions through; allow admin-ajax/admin-post unauthenticated endpoints
* Safer error output and correct textdomain loading path
* Don’t create `.htaccess` when missing; only clean if present

= 5.5 =
* Added rate limiting feature to prevent brute force attacks
* Improved XSS protection with proper sanitization
* Added WordPress.org compliance improvements
* Enhanced file permission checks
* Added text domain for internationalization

= 5.4 =
* Removed redundant features for Nginx environments
* Cleaned up XML-RPC and directory browsing protection
* Performance optimizations

== Upgrade Notice ==

= 6.0 =
This version overhauls rewrite handling and rate limiting. It hard-flushes rewrites when needed, applies safer redirects, and improves blocking behavior (403 + disabled form). Please backup before upgrading.

== Security ==

This plugin follows WordPress security best practices:
* All input is properly sanitized and escaped
* CSRF protection with WordPress nonces
* SQL injection prevention with prepared statements
* XSS protection with output escaping
* File permission validation

== Privacy ==

DPS Login Security does not:
* Collect personal data
* Track user activity
* Send data to external services
* Use third-party analytics

All data is stored locally in your WordPress database.

== Plugin Support ==

For support and feature requests:
* WordPress.org support forums: https://wordpress.org/support/plugin/dps-login-security/
* Official website: https://dps.media/

== Developer Information ==

The plugin includes several action and filter hooks for developers:
* `dps_login_security_before_login` - Before authentication
* `dps_login_security_after_login` - After successful login
* `dps_login_security_rate_limit_check` - Modify rate limiting behavior

== License ==

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
