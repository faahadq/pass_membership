<?php
/**
 * Plugin Name: AACA Membership 
 * Plugin URI: https://github.com/faahadq/pass_membership
 * Description: Lightweight membership helpers for Gravity Forms, ACF, and protected member content.
 * Version: 1.0.0
 * Author: Muhammad Faahad Qureshi
 * Author URI: https://github.com/faahadq
 * License: GPLv2 or later
 * Text Domain: aaca-membership
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AACA_MEMBERSHIP_CORE_VERSION', '1.0.0' );
define( 'AACA_MEMBERSHIP_CORE_FILE', __FILE__ );
define( 'AACA_MEMBERSHIP_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'AACA_MEMBERSHIP_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once AACA_MEMBERSHIP_CORE_PATH . 'includes/class-aaca-member-roles.php';
require_once AACA_MEMBERSHIP_CORE_PATH . 'includes/class-aaca-member-helpers.php';
require_once AACA_MEMBERSHIP_CORE_PATH . 'includes/class-aaca-member-shortcodes.php';

/**
 * Bootstrap plugin.
 */
function aaca_membership_core_init() {
	AACA_Member_Roles::init();
	AACA_Member_Shortcodes::init();
}
add_action( 'plugins_loaded', 'aaca_membership_core_init' );

/**
 * Activation.
 */
function aaca_membership_core_activate() {
	AACA_Member_Roles::add_roles();
}
register_activation_hook( __FILE__, 'aaca_membership_core_activate' );

/**
 * Deactivation.
 *
 * We intentionally do not remove the role on deactivation to avoid
 * accidentally affecting existing users. Role cleanup can be handled
 * manually or on uninstall if ever needed.
 */
function aaca_membership_core_deactivate() {
	// Intentionally left blank.
}
register_deactivation_hook( __FILE__, 'aaca_membership_core_deactivate' );