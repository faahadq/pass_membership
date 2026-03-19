=== AACA Membership Core ===
Contributors: yourname
Tags: membership, gravity forms, acf, user meta, protected pages, member shortcodes
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight membership helpers for Gravity Forms, ACF, protected member pages, and member shortcodes.

== Description ==

AACA Membership Core is a lightweight custom membership plugin built to support a simple PAS membership workflow using:

- Gravity Forms
- Gravity Forms User Registration Add-On
- ACF user fields
- GravityImport / GravityExport
- Custom protected member pages

This plugin is intentionally lightweight. It does not try to replace a full membership platform. Instead, it adds the core logic needed to manage membership data stored in user meta / ACF and to protect selected pages for active members only.

Current features include:

- Creates the custom WordPress role: PAS Member (`aaca_member`)
- Reads membership data from WordPress user meta / ACF
- Checks if a member is active based on `exp_date`
- Runs a daily cron job to update `user_status`
- Adds an admin settings page under Users
- Lets admins choose protected pages
- Redirects unauthorized users away from protected pages
- Provides helper functions for custom development
- Provides shortcodes for member data output

Project date format:

- Membership dates use `m/d/Y`
- Example: `12/31/2026`

== Features ==

= Role =

This plugin creates and uses:

- Role key: `aaca_member`
- Role label: `PAS Member`

= Membership logic =

Membership status is based on the `exp_date` user meta field.

Rules:

- Active Member = `exp_date` is today or in the future
- Expired Member = `exp_date` is in the past

Expected date format:

- `m/d/Y`
- Example: `12/31/2026`

= Daily status sync =

The plugin includes a daily WP-Cron job that checks all users with the `aaca_member` role and updates:

- `user_status = 1` for active members
- `user_status = 0` for inactive / expired members

The plugin also includes a manual sync button in the admin page.

= Protected pages =

The plugin includes an admin settings page where admins can:

- Select protected pages
- Define a redirect URL for unauthorized users
- View active/inactive member counts
- Run the member sync manually

Protected pages allow access only to:

- Admins
- Logged-in users with role `aaca_member`
- Active members only

All other users are redirected to the configured URL.

Note for managed hosts and CDNs:
Protected/member-only pages should be excluded from page cache. On platforms like WP Engine, public cache may serve a cached guest version unless those URLs are excluded from cache and purged after changes.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress Plugins screen
3. The plugin will create the `aaca_member` role automatically
4. Go to Users > PAS Membership
5. Select protected pages and set a redirect URL
6. Save settings
7. Exclude protected page URLs from server/CDN cache if needed

== Admin Page ==

The plugin adds:

Users > PAS Membership

This page includes:

- Active member count
- Inactive member count
- Last sync time
- Manual sync button
- Protected page selector
- Redirect URL field
- Selected protected pages list

== Helper Functions ==

Available helper functions:

- `aaca_get_member_full_name( $user_id = 0 )`
- `aaca_get_member_expiry( $user_id = 0 )`
- `aaca_get_member_type( $user_id = 0 )`
- `aaca_is_member_active( $user_id = 0 )`
- `aaca_user_has_member_role( $user_id = 0 )`

If no user ID is passed, these functions use the current logged-in user.

== Shortcodes ==

= Basic shortcodes =

- `[aaca_member_name]`
- `[aaca_member_expiry]`
- `[aaca_member_status]`
- `[aaca_member_type]`

Examples:

- `[aaca_member_name]`
- `[aaca_member_expiry]`
- `[aaca_member_status]`
- `[aaca_member_type]`
- `[aaca_member_status user_id="45"]`

= Expiry or status shortcode =

Returns the expiry date if the member is active, otherwise returns "Expired".

Shortcode:

- `[aaca_member_expiry_or_status]`

Examples:

- `[aaca_member_expiry_or_status]`
- `[aaca_member_expiry_or_status user_id="45"]`
- `[aaca_member_expiry_or_status expired_text="Membership Expired"]`

= Generic member meta shortcode =

Returns any user meta value for the member.

Shortcode:

- `[aaca_member_meta key="member_number"]`

Supported examples:

- `[aaca_member_meta key="member_number"]`
- `[aaca_member_meta key="company_name"]`
- `[aaca_member_meta key="phone"]`
- `[aaca_member_meta key="aaca_member_number"]`
- `[aaca_member_meta key="comp" fallback="N/A"]`

Available attributes:

- `key` = required meta key
- `user_id` = optional user ID
- `fallback` = optional fallback text
- `before` = optional HTML/text before the value
- `after` = optional HTML/text after the value

Example:

- `[aaca_member_meta key="phone" before="Phone: "]`

= Compiled address shortcode =

Builds the member address from these fields:

- `address1`
- `address2`
- `city`
- `state`
- `zip`
- `country`

Shortcode:

- `[aaca_member_address]`

Examples:

- `[aaca_member_address]`
- `[aaca_member_address format="multiline"]`
- `[aaca_member_address separator=", "]`

Available attributes:

- `user_id` = optional user ID
- `separator` = separator for inline format
- `format` = `inline` or `multiline`

== Data Fields ==

Common membership fields used by this plugin may include:

- `pa_member_since`
- `member_number`
- `address1`
- `address2`
- `city`
- `state`
- `zip`
- `country`
- `phone`
- `member_type`
- `exp_date`
- `spouse_first_name`
- `spouse_last_name`
- `company_name`
- `comp`
- `notes`
- `aaca_member_number`
- `aaca_expiration`
- `aaca_life`
- `user_status`

The plugin reads these from user meta / ACF user fields.

== Notes ==

- Membership expiry format is expected to be `m/d/Y`
- Protected pages should not be fully cached for guests
- This plugin is designed to stay lightweight and focused
- Gravity Forms / ACF remain the source of membership data

== Changelog ==

= 1.1.0 =
* Updated role label to PAS Member
* Added daily member status sync
* Added admin settings page
* Added protected pages settings
* Added redirect URL setting
* Added frontend protected page access control
* Added manual sync button
* Added `[aaca_member_expiry_or_status]`
* Added `[aaca_member_meta]`
* Added `[aaca_member_address]`

= 1.0.0 =
* Initial release
* Added `aaca_member` role
* Added membership helper functions
* Added active/expired membership logic based on `exp_date`
* Added member shortcodes

== Upgrade Notice ==

= 1.1.0 =
Adds daily status sync, protected page settings, admin tools, and new member shortcodes.