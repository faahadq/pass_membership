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

		// New shortcodes.
		add_shortcode( 'aaca_member_expiry_or_status', array( __CLASS__, 'member_expiry_or_status_shortcode' ) );
		add_shortcode( 'aaca_member_meta', array( __CLASS__, 'member_meta_shortcode' ) );
		add_shortcode( 'aaca_member_address', array( __CLASS__, 'member_address_shortcode' ) );
	}

	/**
	 * Resolve target user ID.
	 *
	 * Falls back to current logged-in user.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return int
	 */
	protected static function resolve_user_id( $atts ) {
		$user_id = isset( $atts['user_id'] ) ? absint( $atts['user_id'] ) : 0;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return absint( $user_id );
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

		$user_id = self::resolve_user_id( $atts );

		if ( ! $user_id ) {
			return '';
		}

		return esc_html( AACA_Member_Helpers::get_member_full_name( $user_id ) );
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

		$user_id = self::resolve_user_id( $atts );

		if ( ! $user_id ) {
			return '';
		}

		return esc_html( AACA_Member_Helpers::get_member_expiry( $user_id ) );
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

		$user_id = self::resolve_user_id( $atts );

		if ( ! $user_id ) {
			return '';
		}

		return esc_html( AACA_Member_Helpers::get_member_status( $user_id ) );
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

		$user_id = self::resolve_user_id( $atts );

		if ( ! $user_id ) {
			return '';
		}

		return esc_html( AACA_Member_Helpers::get_member_type( $user_id ) );
	}

	/**
	 * Expiry-or-status shortcode.
	 *
	 * If member is active, return expiry date.
	 * If not active, return "Expired".
	 *
	 * Usage:
	 * [aaca_member_expiry_or_status]
	 * [aaca_member_expiry_or_status user_id="123"]
	 * [aaca_member_expiry_or_status expired_text="Expired"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_expiry_or_status_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id'      => 0,
				'expired_text' => __( 'Expired', 'aaca-membership-core' ),
			),
			$atts,
			'aaca_member_expiry_or_status'
		);

		$user_id = self::resolve_user_id( $atts );

		if ( ! $user_id ) {
			return '';
		}

		if ( AACA_Member_Helpers::is_member_active( $user_id ) ) {
			return esc_html( AACA_Member_Helpers::get_member_expiry( $user_id ) );
		}

		return esc_html( (string) $atts['expired_text'] );
	}

	/**
	 * Generic member meta shortcode.
	 *
	 * Usage:
	 * [aaca_member_meta key="company_name"]
	 * [aaca_member_meta key="member_number"]
	 * [aaca_member_meta key="notes" fallback="N/A"]
	 * [aaca_member_meta key="phone" before="<strong>" after="</strong>"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_meta_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id'  => 0,
				'key'      => '',
				'fallback' => '',
				'before'   => '',
				'after'    => '',
			),
			$atts,
			'aaca_member_meta'
		);

		$user_id = self::resolve_user_id( $atts );
		$key     = sanitize_key( $atts['key'] );

		if ( ! $user_id || '' === $key ) {
			return '';
		}

		$value = AACA_Member_Helpers::get_user_meta_value( $user_id, $key, $atts['fallback'] );

		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}

		$value = trim( (string) $value );

		if ( '' === $value ) {
			$value = (string) $atts['fallback'];
		}

		if ( '' === $value ) {
			return '';
		}

		$before = wp_kses_post( (string) $atts['before'] );
		$after  = wp_kses_post( (string) $atts['after'] );

		return $before . esc_html( $value ) . $after;
	}

	/**
	 * Compiled member address shortcode.
	 *
	 * Supported fields:
	 * address1, address2, city, state, zip, country
	 *
	 * Usage:
	 * [aaca_member_address]
	 * [aaca_member_address separator=", "]
	 * [aaca_member_address format="multiline"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function member_address_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id'   => 0,
				'separator' => ', ',
				'format'    => 'inline', // inline|multiline
			),
			$atts,
			'aaca_member_address'
		);

		$user_id = self::resolve_user_id( $atts );

		if ( ! $user_id ) {
			return '';
		}

		$address1 = trim( (string) AACA_Member_Helpers::get_user_meta_value( $user_id, 'address1', '' ) );
		$address2 = trim( (string) AACA_Member_Helpers::get_user_meta_value( $user_id, 'address2', '' ) );
		$city     = trim( (string) AACA_Member_Helpers::get_user_meta_value( $user_id, 'city', '' ) );
		$state    = trim( (string) AACA_Member_Helpers::get_user_meta_value( $user_id, 'state', '' ) );
		$zip      = trim( (string) AACA_Member_Helpers::get_user_meta_value( $user_id, 'zip', '' ) );
		$country  = trim( (string) AACA_Member_Helpers::get_user_meta_value( $user_id, 'country', '' ) );

		$line_1_parts = array_filter( array( $address1, $address2 ) );
		$line_2_left  = trim( implode( ', ', array_filter( array( $city, $state ) ) ) );
		$line_2       = trim( $line_2_left . ( $line_2_left && $zip ? ' ' : '' ) . $zip );

		$parts = array_filter(
			array(
				implode( ', ', $line_1_parts ),
				$line_2,
				$country,
			)
		);

		if ( empty( $parts ) ) {
			return '';
		}

		if ( 'multiline' === $atts['format'] ) {
			return nl2br( esc_html( implode( "\n", $parts ) ) );
		}

		$separator = isset( $atts['separator'] ) ? (string) $atts['separator'] : ', ';

		return esc_html( implode( $separator, $parts ) );
	}
}