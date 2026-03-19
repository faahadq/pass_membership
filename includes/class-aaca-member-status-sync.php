<?php
/**
 * Member daily status sync.
 *
 * Updates user_status based on exp_date for aaca_member users only.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AACA_Member_Status_Sync {

	/**
	 * Cron hook name.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'aaca_membership_daily_status_sync';

	/**
	 * Last sync option key.
	 *
	 * @var string
	 */
	const LAST_SYNC_OPTION = 'aaca_membership_last_status_sync';

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_schedule_event' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_daily_sync' ) );
	}

	/**
	 * Schedule event if missing.
	 *
	 * @return void
	 */
	public static function maybe_schedule_event() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * On plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}

		self::run_daily_sync();
	}

	/**
	 * On plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		while ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
			$timestamp = wp_next_scheduled( self::CRON_HOOK );
		}
	}

	/**
	 * Run daily member status sync.
	 *
	 * Only processes users with role aaca_member.
	 *
	 * user_status:
	 * 1 = active
	 * 0 = inactive / expired
	 *
	 * @return void
	 */
	public static function run_daily_sync() {
		$user_ids = get_users(
			array(
				'role'   => 'aaca_member',
				'fields' => 'ID',
				'number' => -1,
			)
		);

		if ( empty( $user_ids ) || ! is_array( $user_ids ) ) {
			update_option( self::LAST_SYNC_OPTION, current_time( 'mysql' ) );
			return;
		}

		foreach ( $user_ids as $user_id ) {
			$user_id = absint( $user_id );

			if ( ! $user_id ) {
				continue;
			}

			$is_active = AACA_Member_Helpers::is_member_active( $user_id ) ? '1' : '0';

			update_user_meta( $user_id, 'user_status', $is_active );

			if ( function_exists( 'update_field' ) ) {
				update_field( 'user_status', ( '1' === $is_active ? 1 : 0 ), 'user_' . $user_id );
			}
		}

		update_option( self::LAST_SYNC_OPTION, current_time( 'mysql' ) );
	}

	/**
	 * Get last sync datetime.
	 *
	 * @return string
	 */
	public static function get_last_sync_time() {
		return (string) get_option( self::LAST_SYNC_OPTION, '' );
	}

	/**
	 * Get active member count.
	 *
	 * @return int
	 */
	public static function get_active_member_count() {
		$user_ids = get_users(
			array(
				'role'       => 'aaca_member',
				'fields'     => 'ID',
				'number'     => -1,
				'meta_key'   => 'user_status',
				'meta_value' => '1',
			)
		);

		return is_array( $user_ids ) ? count( $user_ids ) : 0;
	}

	/**
	 * Get inactive member count.
	 *
	 * @return int
	 */
	public static function get_inactive_member_count() {
		$user_ids = get_users(
			array(
				'role'   => 'aaca_member',
				'fields' => 'ID',
				'number' => -1,
			)
		);

		if ( empty( $user_ids ) || ! is_array( $user_ids ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $user_ids as $user_id ) {
			if ( ! AACA_Member_Helpers::is_member_active( $user_id ) ) {
				$count++;
			}
		}

		return $count;
	}
}