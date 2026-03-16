=== AACA Membership Core ===
Contributors: yourname
Tags: membership, gravity forms, acf, user meta, content control
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight membership helpers for Gravity Forms, ACF, and protected member content.

== Description ==

AACA Membership Core is a lightweight plugin built to support a custom membership workflow using:

- Gravity Forms
- Gravity Forms User Registration Add-On
- ACF user fields
- GravityImport / GravityExport
- Content Control

This plugin does not try to replace a full membership platform. Instead, it provides the core logic needed for a simple and maintainable membership system.

Current features:

- Creates the custom WordPress role: AACA Member (`aaca_member`)
- Reads membership data stored in user meta / ACF
- Checks if a member is active based on `exp_date`
- Provides helper functions for templates and custom development
- Provides useful shortcodes for displaying member data

Project date format:

- Membership dates use `m/d/Y`
- Example: `12/31/2026`

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress Plugins screen
3. The plugin will create the `aaca_member` role automatically

== Usage ==

= Role =

This plugin creates:

- Role key: `aaca_member`
- Role label: `AACA Member`

= Membership logic =

Membership status is based on the `exp_date` user meta field.

Rules:

- Active Member = `exp_date` is today or in the future
- Expired Member = `exp_date` is in the past

Date format expected by the plugin:

- `m/d/Y`
- Example: `12/31/2026`

= Helper functions =

Available functions:

- `aaca_get_member_full_name( $user_id = 0 )`
- `aaca_get_member_expiry( $user_id = 0 )`
- `aaca_get_member_type( $user_id = 0 )`
- `aaca_is_member_active( $user_id = 0 )`
- `aaca_user_has_member_role( $user_id = 0 )`

If no user ID is passed, these functions use the current logged-in user.

= Shortcodes =

Available shortcodes:

- `[aaca_member_name]`
- `[aaca_member_expiry]`
- `[aaca_member_status]`
- `[aaca_member_type]`

Optional parameter:

- `user_id="123"`

Examples:

- `[aaca_member_name]`
- `[aaca_member_expiry]`
- `[aaca_member_status]`
- `[aaca_member_type]`
- `[aaca_member_status user_id="45"]`

== Planned features ==

Future versions may include:

- Content Control integration helpers
- Protected content shortcodes
- Gravity Forms user meta sync helpers
- Admin tools
- Membership renewal utilities
- Import/export support helpers

== Changelog ==

= 1.0.0 =
* Initial release
* Added `aaca_member` role
* Added membership helper functions
* Added active/expired membership logic based on `exp_date`
* Added member shortcodes

== Upgrade Notice ==

= 1.0.0 =
Initial release.