<?php
/**
 * Member helper functions.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AACA_Member_Helpers {

	/**
	 * Safely get a user meta value.
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get_user_meta_value( $user_id, $key, $default = '' ) {
		$user_id = absint( $user_id );
		$key     = sanitize_key( $key );

		if ( ! $user_id || ! $key ) {
			return $default;
		}

		$value = get_user_meta( $user_id, $key, true );

		if ( '' === $value || null === $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Get member full name.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_member_full_name( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id ) {
			return '';
		}

		$first_name = trim( (string) get_user_meta( $user_id, 'first_name', true ) );
		$last_name  = trim( (string) get_user_meta( $user_id, 'last_name', true ) );
		$full_name  = trim( $first_name . ' ' . $last_name );

		if ( '' !== $full_name ) {
			return $full_name;
		}

		$user = get_userdata( $user_id );

		if ( $user && ! empty( $user->display_name ) ) {
			return (string) $user->display_name;
		}

		return '';
	}

	/**
	 * Get member expiry date from user meta / ACF.
	 *
	 * Stored format for this project is m/d/Y, e.g. 12/31/2026.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_member_expiry( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id ) {
			return '';
		}

		return (string) self::get_user_meta_value( $user_id, 'exp_date', '' );
	}

	/**
	 * Get member type.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_member_type( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id ) {
			return '';
		}

		return (string) self::get_user_meta_value( $user_id, 'member_type', '' );
	}

	/**
	 * Parse project membership date (m/d/Y) into timestamp.
	 *
	 * @param string $date_string Date string.
	 * @return int|false
	 */
	public static function parse_membership_date( $date_string ) {
		$date_string = trim( (string) $date_string );

		if ( '' === $date_string ) {
			return false;
		}

		$date = \DateTime::createFromFormat( 'm/d/Y', $date_string );

		if ( ! $date ) {
			return false;
		}

		$errors = \DateTime::getLastErrors();

		if ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) {
			return false;
		}

		$date->setTime( 23, 59, 59 );

		return $date->getTimestamp();
	}

	/**
	 * Check if user has the aaca_member role.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function user_has_member_role( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user || empty( $user->roles ) || ! is_array( $user->roles ) ) {
			return false;
		}

		return in_array( 'aaca_member', $user->roles, true );
	}

	/**
	 * Check if member is active based on exp_date.
	 *
	 * Active means exp_date is today or in the future.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_member_active( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$expiry = self::get_member_expiry( $user_id );

		if ( '' === $expiry ) {
			return false;
		}

		$expiry_timestamp = self::parse_membership_date( $expiry );

		if ( ! $expiry_timestamp ) {
			return false;
		}

		$now = current_time( 'timestamp' );

		return $expiry_timestamp >= $now;
	}

	/**
	 * Get member status label.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_member_status( $user_id = 0 ) {
		return self::is_member_active( $user_id )
			? __( 'Active', 'aaca-membership-core' )
			: __( 'Expired', 'aaca-membership-core' );
	}
}

/**
 * Public wrapper: get member full name.
 *
 * @param int $user_id User ID.
 * @return string
 */
function aaca_get_member_full_name( $user_id = 0 ) {
	return AACA_Member_Helpers::get_member_full_name( $user_id );
}

/**
 * Public wrapper: get member expiry.
 *
 * @param int $user_id User ID.
 * @return string
 */
function aaca_get_member_expiry( $user_id = 0 ) {
	return AACA_Member_Helpers::get_member_expiry( $user_id );
}

/**
 * Public wrapper: get member type.
 *
 * @param int $user_id User ID.
 * @return string
 */
function aaca_get_member_type( $user_id = 0 ) {
	return AACA_Member_Helpers::get_member_type( $user_id );
}

/**
 * Public wrapper: check if member is active.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function aaca_is_member_active( $user_id = 0 ) {
	return AACA_Member_Helpers::is_member_active( $user_id );
}

/**
 * Public wrapper: check if user has member role.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function aaca_user_has_member_role( $user_id = 0 ) {
	return AACA_Member_Helpers::user_has_member_role( $user_id );
}