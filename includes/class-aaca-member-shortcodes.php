<?php
/**
 * Member shortcodes.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AACA_Member_Shortcodes {

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'aaca_member_name', array( __CLASS__, 'member_name_shortcode' ) );
		add_shortcode( 'aaca_member_expiry', array( __CLASS__, 'member_expiry_shortcode' ) );
		add_shortcode( 'aaca_member_status', array( __CLASS__, 'member_status_shortcode' ) );
		add_shortcode( 'aaca_member_type', array( __CLASS__, 'member_type_shortcode' ) );
	}

	/**
	 * Member name shortcode.
	 *
	 * Usage: [aaca_member_name user_id="123"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_name_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => 0,
			),
			$atts,
			'aaca_member_name'
		);

		return esc_html( AACA_Member_Helpers::get_member_full_name( absint( $atts['user_id'] ) ) );
	}

	/**
	 * Member expiry shortcode.
	 *
	 * Usage: [aaca_member_expiry user_id="123"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_expiry_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => 0,
			),
			$atts,
			'aaca_member_expiry'
		);

		return esc_html( AACA_Member_Helpers::get_member_expiry( absint( $atts['user_id'] ) ) );
	}

	/**
	 * Member status shortcode.
	 *
	 * Usage: [aaca_member_status user_id="123"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_status_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => 0,
			),
			$atts,
			'aaca_member_status'
		);

		return esc_html( AACA_Member_Helpers::get_member_status( absint( $atts['user_id'] ) ) );
	}

	/**
	 * Member type shortcode.
	 *
	 * Usage: [aaca_member_type user_id="123"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_type_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => 0,
			),
			$atts,
			'aaca_member_type'
		);

		return esc_html( AACA_Member_Helpers::get_member_type( absint( $atts['user_id'] ) ) );
	}
}