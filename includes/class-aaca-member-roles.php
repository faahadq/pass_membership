<?php
/**
 * Member roles.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AACA_Member_Roles {

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_add_roles' ) );
	}

	/**
	 * Add plugin roles.
	 *
	 * @return void
	 */
	public static function add_roles() {
		add_role(
			'aaca_member',
			__( 'PAS Member', 'aaca-membership-core' ),
			array(
				'read' => true,
			)
		);
	}

	/**
	 * Ensure role exists.
	 *
	 * @return void
	 */
	public static function maybe_add_roles() {
		if ( null === get_role( 'aaca_member' ) ) {
			self::add_roles();
		}
	}
}