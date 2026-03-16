<?php
/**
 * Uninstall AACA Membership Core.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Intentionally do not remove the aaca_member role or any user meta on uninstall.
 *
 * Reason:
 * This plugin stores/reads membership data that may still be important to the site.
 * If you want destructive cleanup later, we can add an explicit cleanup routine.
 */