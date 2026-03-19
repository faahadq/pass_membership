<?php
/**
 * Plugin admin/settings page.
 *
 * @package AACA_Membership_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AACA_Member_Admin {

	/**
	 * Option key for all plugin settings.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'aaca_membership_settings';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'aaca-membership-settings';

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_aaca_membership_run_sync', array( __CLASS__, 'handle_manual_sync' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register admin page.
	 *
	 * @return void
	 */
	public static function register_admin_page() {
		add_users_page(
			__( 'PAS Membership', 'aaca-membership-core' ),
			__( 'PAS Membership', 'aaca-membership-core' ),
			'manage_options',
			self::MENU_SLUG,
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Current screen hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( 'users_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'aaca-membership-admin',
			AACA_MEMBERSHIP_CORE_URL . 'assets/admin-membership.css',
			array(),
			AACA_MEMBERSHIP_CORE_VERSION
		);

		wp_enqueue_script(
			'aaca-membership-admin',
			AACA_MEMBERSHIP_CORE_URL . 'assets/admin-membership.js',
			array(),
			AACA_MEMBERSHIP_CORE_VERSION,
			true
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			'aaca_membership_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => self::get_default_settings(),
			)
		);

		add_settings_section(
			'aaca_membership_access_section',
			__( 'Access Settings', 'aaca-membership-core' ),
			'__return_false',
			self::MENU_SLUG
		);

		add_settings_field(
			'protected_pages',
			__( 'Protected Pages', 'aaca-membership-core' ),
			array( __CLASS__, 'render_protected_pages_field' ),
			self::MENU_SLUG,
			'aaca_membership_access_section'
		);

		add_settings_field(
			'redirect_url',
			__( 'Redirect URL', 'aaca-membership-core' ),
			array( __CLASS__, 'render_redirect_url_field' ),
			self::MENU_SLUG,
			'aaca_membership_access_section'
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function get_default_settings() {
		return array(
			'protected_pages' => array(),
			'redirect_url'    => '',
		);
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$settings = get_option( self::OPTION_KEY, array() );
		$settings = is_array( $settings ) ? $settings : array();

		return wp_parse_args( $settings, self::get_default_settings() );
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$input  = is_array( $input ) ? $input : array();
		$output = self::get_default_settings();

		if ( isset( $input['protected_pages'] ) && is_array( $input['protected_pages'] ) ) {
			$page_ids = array_map( 'absint', $input['protected_pages'] );
			$page_ids = array_filter( $page_ids );
			$page_ids = array_values( array_unique( $page_ids ) );

			$validated = array();

			foreach ( $page_ids as $page_id ) {
				if ( 'page' === get_post_type( $page_id ) ) {
					$validated[] = $page_id;
				}
			}

			$output['protected_pages'] = $validated;
		}

		if ( isset( $input['redirect_url'] ) ) {
			$output['redirect_url'] = esc_url_raw( trim( (string) $input['redirect_url'] ) );
		}

		return $output;
	}

	/**
	 * Render protected pages field.
	 *
	 * @return void
	 */
	public static function render_protected_pages_field() {
		$settings       = self::get_settings();
		$selected_pages = isset( $settings['protected_pages'] ) && is_array( $settings['protected_pages'] )
			? array_map( 'absint', $settings['protected_pages'] )
			: array();

		$pages = get_pages(
			array(
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC',
			)
		);

		?>
		<div class="aaca-page-picker">
			<div class="aaca-page-picker__toolbar">
				<input
					type="text"
					class="regular-text aaca-page-filter"
					placeholder="<?php echo esc_attr__( 'Search pages...', 'aaca-membership-core' ); ?>"
				/>
				<div class="aaca-page-picker__actions">
					<button type="button" class="button aaca-select-all-pages"><?php esc_html_e( 'Select All', 'aaca-membership-core' ); ?></button>
					<button type="button" class="button aaca-clear-all-pages"><?php esc_html_e( 'Clear All', 'aaca-membership-core' ); ?></button>
				</div>
			</div>

			<p class="aaca-page-picker__count">
				<span class="aaca-selected-count"><?php echo esc_html( count( $selected_pages ) ); ?></span>
				<?php esc_html_e( 'page(s) selected', 'aaca-membership-core' ); ?>
			</p>

			<div class="aaca-page-picker__list">
				<?php foreach ( $pages as $page ) : ?>
					<?php $checked = in_array( (int) $page->ID, $selected_pages, true ); ?>
					<label class="aaca-page-option" data-title="<?php echo esc_attr( strtolower( $page->post_title ) ); ?>">
						<input
							type="checkbox"
							name="<?php echo esc_attr( self::OPTION_KEY ); ?>[protected_pages][]"
							value="<?php echo esc_attr( $page->ID ); ?>"
							<?php checked( $checked ); ?>
						/>
						<span class="aaca-page-option__text">
							<strong><?php echo esc_html( $page->post_title ); ?></strong>
							<small>#<?php echo esc_html( $page->ID ); ?></small>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

			<p class="description">
				<?php esc_html_e( 'Select pages that should be restricted to admins and active PAS members.', 'aaca-membership-core' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render redirect URL field.
	 *
	 * @return void
	 */
	public static function render_redirect_url_field() {
		$settings     = self::get_settings();
		$redirect_url = isset( $settings['redirect_url'] ) ? (string) $settings['redirect_url'] : '';
		?>
		<input
			type="url"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[redirect_url]"
			value="<?php echo esc_attr( $redirect_url ); ?>"
			class="regular-text"
			placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"
		/>
		<p class="description">
			<?php esc_html_e( 'Unauthorized users will be redirected here.', 'aaca-membership-core' ); ?>
		</p>
		<?php
	}

	/**
	 * Manual sync handler.
	 *
	 * @return void
	 */
	public static function handle_manual_sync() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'aaca-membership-core' ) );
		}

		check_admin_referer( 'aaca_membership_run_sync' );

		AACA_Member_Status_Sync::run_daily_sync();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'     => self::MENU_SLUG,
					'sync_run' => '1',
				),
				admin_url( 'users.php' )
			)
		);
		exit;
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_count   = AACA_Member_Status_Sync::get_active_member_count();
		$inactive_count = AACA_Member_Status_Sync::get_inactive_member_count();
		$last_sync      = AACA_Member_Status_Sync::get_last_sync_time();
		$settings       = self::get_settings();

		?>
		<div class="wrap aaca-membership-admin-wrap">
			<h1><?php esc_html_e( 'PAS Membership', 'aaca-membership-core' ); ?></h1>

			<?php if ( isset( $_GET['sync_run'] ) && '1' === $_GET['sync_run'] ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Membership status sync completed successfully.', 'aaca-membership-core' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="aaca-stats-grid">
				<div class="aaca-card aaca-stat-card">
					<h2><?php esc_html_e( 'Active Members', 'aaca-membership-core' ); ?></h2>
					<p class="aaca-stat-number"><?php echo esc_html( $active_count ); ?></p>
				</div>

				<div class="aaca-card aaca-stat-card">
					<h2><?php esc_html_e( 'Inactive Members', 'aaca-membership-core' ); ?></h2>
					<p class="aaca-stat-number"><?php echo esc_html( $inactive_count ); ?></p>
				</div>

				<div class="aaca-card aaca-stat-card">
					<h2><?php esc_html_e( 'Last Sync', 'aaca-membership-core' ); ?></h2>
					<p class="aaca-stat-text">
						<?php echo $last_sync ? esc_html( $last_sync ) : esc_html__( 'Not run yet', 'aaca-membership-core' ); ?>
					</p>
				</div>
			</div>

			<div class="aaca-card">
				<h2><?php esc_html_e( 'Manual Sync', 'aaca-membership-core' ); ?></h2>
				<p><?php esc_html_e( 'Run the expiry/status sync immediately.', 'aaca-membership-core' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="aaca_membership_run_sync" />
					<?php wp_nonce_field( 'aaca_membership_run_sync' ); ?>
					<?php submit_button( __( 'Run Sync Now', 'aaca-membership-core' ), 'primary', 'submit', false ); ?>
				</form>
			</div>

			<div class="aaca-card">
				<h2><?php esc_html_e( 'Access Settings', 'aaca-membership-core' ); ?></h2>

				<form method="post" action="options.php">
					<?php
					settings_fields( 'aaca_membership_settings_group' );
					do_settings_sections( self::MENU_SLUG );
					submit_button( __( 'Save Settings', 'aaca-membership-core' ) );
					?>
				</form>
			</div>

			<?php if ( ! empty( $settings['protected_pages'] ) ) : ?>
				<div class="aaca-card">
					<h2><?php esc_html_e( 'Selected Protected Pages', 'aaca-membership-core' ); ?></h2>
					<ul class="aaca-protected-pages-list">
						<?php foreach ( $settings['protected_pages'] as $page_id ) : ?>
							<?php
							$page = get_post( $page_id );
							if ( ! $page || 'page' !== $page->post_type ) {
								continue;
							}
							?>
							<li>
								<span><?php echo esc_html( $page->post_title ); ?></span>
								<a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>">
									<?php esc_html_e( 'Edit', 'aaca-membership-core' ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get saved protected page IDs.
	 *
	 * @return array
	 */
	public static function get_protected_page_ids() {
		$settings = self::get_settings();

		return isset( $settings['protected_pages'] ) && is_array( $settings['protected_pages'] )
			? array_map( 'absint', $settings['protected_pages'] )
			: array();
	}

	/**
	 * Get saved redirect URL.
	 *
	 * @return string
	 */
	public static function get_redirect_url() {
		$settings = self::get_settings();

		return isset( $settings['redirect_url'] ) ? (string) $settings['redirect_url'] : '';
	}
}