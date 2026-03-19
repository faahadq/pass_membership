<?php
/**
 * Frontend access protection for protected pages.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AACA_Member_Access {

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'protect_selected_pages' ), -100 );
	}

	/**
	 * Protect selected pages.
	 *
	 * @return void
	 */
	public static function protect_selected_pages() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_trackback() || is_robots() ) {
			return;
		}

		if ( ! class_exists( 'AACA_Member_Admin' ) || ! class_exists( 'AACA_Member_Helpers' ) ) {
			return;
		}

		$protected_page_ids = AACA_Member_Admin::get_protected_page_ids();

		if ( empty( $protected_page_ids ) || ! is_array( $protected_page_ids ) ) {
			return;
		}

		$protected_page_ids = array_filter( array_map( 'absint', $protected_page_ids ) );

		if ( empty( $protected_page_ids ) ) {
			return;
		}

		$current_page_id = self::get_current_page_id();

		if ( ! $current_page_id || ! in_array( $current_page_id, $protected_page_ids, true ) ) {
			return;
		}

		if ( ! headers_sent() ) {
			nocache_headers();
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
		}

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			self::redirect_unauthorized();
		}

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			self::redirect_unauthorized();
		}

		if ( ! AACA_Member_Helpers::user_has_member_role( $user_id ) ) {
			self::redirect_unauthorized();
		}

		if ( ! AACA_Member_Helpers::is_member_active( $user_id ) ) {
			self::redirect_unauthorized();
		}
	}

	/**
	 * Resolve current page ID.
	 *
	 * @return int
	 */
	protected static function get_current_page_id() {
		$object_id = get_queried_object_id();

		if ( $object_id ) {
			$post = get_post( $object_id );

			if ( $post instanceof WP_Post && 'page' === $post->post_type ) {
				return (int) $object_id;
			}
		}

		global $post;

		if ( $post instanceof WP_Post && 'page' === $post->post_type ) {
			return (int) $post->ID;
		}

		$queried_object = get_queried_object();

		if ( $queried_object instanceof WP_Post && 'page' === $queried_object->post_type ) {
			return (int) $queried_object->ID;
		}

		return 0;
	}

	/**
	 * Redirect unauthorized users.
	 *
	 * @return void
	 */
	protected static function redirect_unauthorized() {
		$redirect_url = '';

		if ( class_exists( 'AACA_Member_Admin' ) ) {
			$redirect_url = AACA_Member_Admin::get_redirect_url();
		}

		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}

		$redirect_url = esc_url_raw( $redirect_url );

		if ( empty( $redirect_url ) ) {
			$redirect_url = home_url( '/' );
		}

		$current_url = self::get_current_url();

		if ( $current_url && untrailingslashit( $current_url ) === untrailingslashit( $redirect_url ) ) {
			return;
		}

		wp_safe_redirect( $redirect_url, 302 );
		exit;
	}

	/**
	 * Get current URL.
	 *
	 * @return string
	 */
	protected static function get_current_url() {
		if ( empty( $_SERVER['HTTP_HOST'] ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}

		$scheme = is_ssl() ? 'https://' : 'http://';

		return $scheme . wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] );
	}
}