<?php
/**
 * Audit Log View Class
 *
 * Class file for Audit Log View.
 *
 * @since 1.0.0
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audit Log Viewer Page
 *
 * @package Wsal
 */
class WSAL_Views_AuditLog extends WSAL_AbstractView {

	/**
	 * Listing view object (Instance of WSAL_AuditLogListView).
	 *
	 * @var object
	 */
	protected $_view;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * WSAL Adverts.
	 *
	 * @since 3.2.4
	 *
	 * @var array
	 */
	private $adverts;

	/**
	 * Audit Log View Arguments.
	 *
	 * @since 3.3.1.1
	 *
	 * @var stdClass
	 */
	private $page_args;

	/**
	 * Stores the value of the last view the user requested.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $user_last_view = '';

	/**
	 * Method: Constructor
	 *
	 * @param WpSecurityAuditLog $plugin - Instance of WpSecurityAuditLog.
	 */
	public function __construct( WpSecurityAuditLog $plugin ) {
		parent::__construct( $plugin );
		add_action( 'wp_ajax_AjaxInspector', array( $this, 'AjaxInspector' ) );
		add_action( 'wp_ajax_AjaxRefresh', array( $this, 'AjaxRefresh' ) );
		add_action( 'wp_ajax_AjaxSetIpp', array( $this, 'AjaxSetIpp' ) );
		add_action( 'wp_ajax_AjaxSearchSite', array( $this, 'AjaxSearchSite' ) );
		add_action( 'wp_ajax_AjaxSwitchDB', array( $this, 'AjaxSwitchDB' ) );
		add_action( 'wp_ajax_wsal_download_failed_login_log', array( $this, 'wsal_download_failed_login_log' ) );
		add_action( 'wp_ajax_wsal_download_404_log', array( $this, 'wsal_download_404_log' ) );
		add_action( 'wp_ajax_wsal_freemius_opt_in', array( $this, 'wsal_freemius_opt_in' ) );
		add_action( 'wp_ajax_wsal_exclude_url', array( $this, 'wsal_exclude_url' ) );
		add_action( 'wp_ajax_wsal_dismiss_advert', array( $this, 'wsal_dismiss_advert' ) );
		add_action( 'wp_ajax_wsal_dismiss_notice_disconnect', array( $this, 'dismiss_notice_disconnect' ) );
		add_action( 'wp_ajax_wsal_dismiss_notice_addon_available', array( $this, 'dismiss_notice_addon_available' ) );
		add_action( 'wp_ajax_wsal_dismiss_wp_pointer', array( $this, 'dismiss_wp_pointer' ) );
		add_action( 'all_admin_notices', array( $this, 'AdminNoticesPremium' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_pointers' ), 1000 );
		add_filter( 'wsal_pointers_toplevel_page_wsal-auditlog', array( $this, 'register_privacy_pointer' ), 10, 1 );
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );

		if ( $this->_plugin->settings()->is_infinite_scroll() ) {
			add_action( 'wp_ajax_wsal_infinite_scroll_events', array( $this, 'infinite_scroll_events' ) );
		}

		// Check plugin version for to dismiss the notice only until upgrade.
		$this->_version = WSAL_VERSION;

		// Set adverts array.
		$this->adverts = array(
			0 => array(
				'head' => __( 'Get instantly alerted of critical changes via SMS & email, search the activity log, generate user reports, see who is logged in and more!', 'wp-security-audit-log' ),
				'desc' => __( 'Upgrade to premium to unlock these powerful activity log features & more!', 'wp-security-audit-log' ),
			),
			1 => array(
				'head' => __( 'Instant SMS & email alerts, search & filters, reports, users sessions management and much more!', 'wp-security-audit-log' ),
				'desc' => __( 'Upgrade to premium to get more out of your activity logs!', 'wp-security-audit-log' ),
			),
			2 => array(
				'head' => __( 'See who logged in on your site in real-time, generate reports, get SMS & email alerts of critical changes and more!', 'wp-security-audit-log' ),
				'desc' => __( 'Unlock these and other powerful features with WP Activity Log Premium.', 'wp-security-audit-log' ),
			),
		);

		// Setup the users last view by getting the value from user meta.
		$last_view            = get_user_meta( get_current_user_id(), 'wsal-selected-main-view', true );
		$this->user_last_view = ( in_array( $last_view, $this->supported_view_types(), true ) ) ? $last_view : 'list';
	}

	/**
	 * Add premium extensions notice.
	 *
	 * Notices:
	 *   1. Plugin advert.
	 *   2. DB disconnection notice.
	 *   3. Freemius opt-in/out notice.
	 */
	public function AdminNoticesPremium() {
		$is_current_view = $this->_plugin->views->GetActiveView() == $this;

		// Check if any of the extensions is activated.
		if (
			! class_exists( 'WSAL_NP_Plugin' )
			&& ! class_exists( 'WSAL_Ext_Plugin' )
			&& ! class_exists( 'WSAL_Rep_Plugin' )
			&& ! class_exists( 'WSAL_SearchExtension' )
			&& ! class_exists( 'WSAL_UserSessions_Plugin' )
			&& 'anonymous' !== $this->_plugin->GetGlobalSetting( 'freemius_state', 'anonymous' ) // Anonymous mode option.
		) {
			$get_transient_fn         = $this->_plugin->IsMultisite() ? 'get_site_transient' : 'get_transient'; // Check for multisite.
			$wsal_is_advert_dismissed = $get_transient_fn( 'wsal-is-advert-dismissed' ); // Check if advert has been dismissed.
			$wsal_premium_advert      = $this->_plugin->GetGlobalSetting( 'premium-advert', false ); // Get the advert to display.
			$wsal_premium_advert      = false !== $wsal_premium_advert ? (int) $wsal_premium_advert : 0; // Set the default.

			if ( current_user_can( 'manage_options' ) && $is_current_view && ! $wsal_is_advert_dismissed ) : ?>
				<div class="updated wsal_notice">
					<div class="wsal_notice__wrapper">
						<div class="wsal_notice__content">
							<img src="<?php echo esc_url( WSAL_BASE_URL ); ?>img/wsal-logo@2x.png">
							<p>
								<strong><?php echo isset( $this->adverts[ $wsal_premium_advert ]['head'] ) ? esc_html( $this->adverts[ $wsal_premium_advert ]['head'] ) : false; ?></strong><br>
								<?php echo isset( $this->adverts[ $wsal_premium_advert ]['desc'] ) ? esc_html( $this->adverts[ $wsal_premium_advert ]['desc'] ) : false; ?>
							</p>
						</div>
						<!-- /.wsal_notice__content -->
						<div class="wsal_notice__btns">
							<?php
							// Trial link arguments.
							$trial_args = array(
								'page'          => 'wsal-auditlog-pricing',
								'billing_cycle' => 'annual',
								'trial'         => 'true',
							);

							// Buy Now button link.
							$buy_now    = add_query_arg( 'page', 'wsal-auditlog-pricing', admin_url( 'admin.php' ) );
							$trial_link = add_query_arg( $trial_args, admin_url( 'admin.php' ) );

							// If user is not super admin and website is multisite then change the URL.
							if ( $this->_plugin->IsMultisite() && ! is_super_admin() ) {
								$buy_now    = 'https://wpactivitylog.com/pricing/';
								$trial_link = 'https://wpactivitylog.com/pricing/';
							} elseif ( $this->_plugin->IsMultisite() && is_super_admin() ) {
								$buy_now    = add_query_arg( 'page', 'wsal-auditlog-pricing', network_admin_url( 'admin.php' ) );
								$trial_link = add_query_arg( $trial_args, network_admin_url( 'admin.php' ) );
							}

							$more_info = add_query_arg(
								array(
									'utm_source'   => 'plugin',
									'utm_medium'   => 'referral',
									'utm_campaign' => 'WSAL',
									'utm_content'  => 'tell+me+more',
								),
								'https://wpactivitylog.com/features/'
							);
							?>
							<?php wp_nonce_field( 'wsal_dismiss_advert', 'wsal-dismiss-advert', false, true ); ?>
							<a href="<?php echo esc_url( $buy_now ); ?>" class="button button-primary wsal_notice__btn"><?php esc_html_e( 'UPGRADE NOW', 'wp-security-audit-log' ); ?></a>
							<a href="<?php echo esc_url( $trial_link ); ?>" class="button button-primary"><?php esc_html_e( 'Start Free Trial', 'wp-security-audit-log' ); ?></a>
							<a href="<?php echo esc_url( $more_info ); ?>" target="_blank"><?php esc_html_e( 'Tell me more', 'wp-security-audit-log' ); ?></a>
							<br>
							<a href="javascript:;" data-advert="<?php echo esc_attr( $wsal_premium_advert ); ?>" onclick="wsal_dismiss_advert(this)" class="wsal_notice__btn_dismiss" title="<?php esc_attr_e( 'Dismiss the banner', 'wp-security-audit-log' ); ?>"><?php esc_html_e( 'Close', 'wp-security-audit-log' ); ?></a>
						</div>
						<!-- /.wsal_notice__btns -->
					</div>
					<!-- /.wsal_notice__wrapper -->
				</div>
				<?php
			endif;
		}

		// Get DB connector.
		$db_config  = WSAL_Connector_ConnectorFactory::GetConfig(); // Get DB connector configuration.
		$wsal_db    = $this->_plugin->getConnector( $db_config )->getConnection(); // Get DB connection.
		$connection = true;
		if ( isset( $wsal_db->dbh->errno ) ) {
			$connection = 0 !== (int) $wsal_db->dbh->errno ? false : true; // Database connection error check.
		} elseif ( is_wp_error( $wsal_db->error ) ) {
			$connection = false;
		}

		// Add connectivity notice.
		$notice_dismissed = get_transient( 'wsal-dismiss-notice-disconnect' );
		if ( ! $connection && false === $notice_dismissed && $is_current_view ) {
			?>
			<div class="notice notice-error is-dismissible" id="wsal-notice-connect-issue">
				<p><?php esc_html_e( 'There are connectivity issues with the database where the WordPress activity log is stored. The logs will be temporary buffered in the WordPress database until the connection is fully restored.', 'wp-security-audit-log' ); ?></p>
				<?php wp_nonce_field( 'wsal_dismiss_notice_disconnect', 'wsal-dismiss-notice-disconnect', false, true ); ?>
			</div>
			<?php
		}

		// Check anonymous mode.
		if ( 'anonymous' === $this->_plugin->GetGlobalSetting( 'freemius_state', 'anonymous' ) ) { // If user manually opt-out then don't show the notice.
			if (
				wsal_freemius()->is_anonymous() // Anonymous mode option.
				&& wsal_freemius()->is_not_paying() // Not paying customer.
				&& wsal_freemius()->has_api_connectivity() // Check API connectivity.
				&& $is_current_view
				&& $this->_plugin->settings()->CurrentUserCan( 'edit' ) // Have permission to edit plugin settings.
			) {
				if ( ! is_multisite() || ( is_multisite() && is_network_admin() ) ) :
					?>
					<div class="notice notice-success">
						<p><strong><?php esc_html_e( 'Help WP Activity Log improve.', 'wp-security-audit-log' ); ?></strong></p>
						<p><?php echo esc_html__( 'Gathering non-sensitive diagnostic data about the plugin install helps us improve the plugin. When you opt-in, you also subscribe to our announcements (you can opt-out at any time). If you would rather opt-out, we will not collect any data.', 'wp-security-audit-log' ) . ' <a href="https://wpactivitylog.com/support/kb/non-sensitive-diagnostic-data/" target="_blank">' . esc_html__( 'Read more about what we collect.', 'wp-security-audit-log' ) . '</a>'; ?></p>
						<p>
							<a href="javascript:;" class="button button-primary" onclick="wsal_freemius_opt_in(this)" data-opt="yes"><?php esc_html_e( 'Sure, opt-in', 'wp-security-audit-log' ); ?></a>
							<a href="javascript:;" class="button" onclick="wsal_freemius_opt_in(this)" data-opt="no"><?php esc_html_e( 'No, thank you', 'wp-security-audit-log' ); ?></a>
							<input type="hidden" id="wsal-freemius-opt-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wsal-freemius-opt' ) ); ?>" />
						</p>
					</div>
					<?php
				endif;
			}
		}

		// Display add-on available notice.
		$screen = get_current_screen();
		$notice_already_dismissed = $this->_plugin->GetGlobalSetting( 'addon_available_notice_dismissed' );
		if ( $screen->base === 'toplevel_page_wsal-auditlog' && $is_current_view && ! $notice_already_dismissed || $screen->base === 'toplevel_page_wsal-auditlog-network' && $is_current_view && ! $notice_already_dismissed ) {
			// Grab list of installed plugins.
			$all_plugins      = get_plugins();
			$plugin_filenames = array();
			foreach ( $all_plugins as $plugin => $info ) {
				// here we strip all of the plugin slug, leaving just the filename itself. Neat!
				$plugin_filenames[] = preg_replace( '/\\.[^.\\s]{3,4}$/', '', substr( basename( json_encode( $plugin ) ), 0, -1 ) );
			}

			// Grab list of plugins we have addons for.
			$predefined_plugins       = WSAL_PluginInstallAndActivate::get_installable_plugins();
			$predefined_plugins_check = array_column( $predefined_plugins, 'addon_for' );

			// Loop through plugins and create an array of slugs, we will compare these agains the plugins we have addons for.
			$we_have_addon = array_intersect( $plugin_filenames, $predefined_plugins_check );

			$display_notice           = false;

			if ( isset( $we_have_addon ) && is_array( $we_have_addon ) ) {
				$addon_names = '';
				$i           = 0;
				foreach ( $we_have_addon as $addon ) {
					$addon_slug         = array_search( $addon, array_column( $predefined_plugins, 'addon_for', 'plugin_slug' ) );
					$is_addon_installed = is_plugin_active( $addon_slug );

					// Check if a function from the addon exists, just in case.
					if ( $addon === 'wpforms' && function_exists( 'wsal_wpforms_init_actions' ) || $addon === 'bbpress' && function_exists( 'wsal_bbpress_init_actions' ) ) {
						continue;
					}

					if ( ! $is_addon_installed ) {
						$addon = str_replace( '-', ' ', $addon);
						if ( $addon === 'bbpress' ) {
							$addon = 'bbPress';
						}
						if ( $addon === 'wpforms' ) {
							$addon = 'WPForms';
						}
						if ( $addon === 'woocommerce' ) {
							$addon = 'WooCommerce';
						}
						if ( empty( $addon_names ) ) {
							$addon_names .= $addon;
							$button_label = esc_html__( 'Install Extension', 'wp-security-audit-log' );
						} else {
							$addon_names .= ' & ' .$addon;
							$button_label = esc_html__( 'Install Extensions', 'wp-security-audit-log' );
						}
						$display_notice = true;
					}
					$i++;
				}
				?>
				<?php if ( $display_notice ) : ?>
				<div class="notice notice-information is-dismissible" id="wsal-notice-addon-available">
					<p><?php $message = printf(
						/* translators: %1$s: is the user name, %2$s is the website name */
						'%1$s %2$s %3$s %4$s. <a href="%6$s" class="button button-primary">%5$s</a>',
						esc_html__( 'You have', 'wp-security-audit-log' ),
						$addon_names,
						esc_html__( 'installed. Keep a log of changes in', 'wp-security-audit-log' ),
						$addon_names,
						$button_label,
						esc_url( add_query_arg( 'page', 'wsal-togglealerts#tab-third-party-plugins', network_admin_url( 'admin.php' ) ) )
					); ?></p>
					<?php wp_nonce_field( 'wsal_dismiss_notice_addon_available', 'wsal-dismiss-notice-addon-available', false, true ); ?>
				</div>
				<?php endif;
			}
		}
	}

	/**
	 * Method: Ajax handler for dismissing DB disconnect issue.
	 */
	public function dismiss_notice_disconnect() {
		// Get $_POST array arguments.
		$post_array_args = array(
			'nonce' => FILTER_SANITIZE_STRING,
		);
		$post_array      = filter_input_array( INPUT_POST, $post_array_args );

		// Verify nonce.
		if ( wp_verify_nonce( $post_array['nonce'], 'wsal_dismiss_notice_disconnect' ) ) {
			set_transient( 'wsal-dismiss-notice-disconnect', 1, 6 * HOUR_IN_SECONDS );
			die();
		}
		die( 'Nonce verification failed!' );
	}

	/**
	 * Method: Ajax handler for dismissing addon notice.
	 */
	public function dismiss_notice_addon_available() {
		// Get $_POST array arguments.
		$post_array_args = array(
			'nonce' => FILTER_SANITIZE_STRING,
		);
		$post_array      = filter_input_array( INPUT_POST, $post_array_args );

		// Verify nonce.
		if ( wp_verify_nonce( $post_array['nonce'], 'wsal_dismiss_notice_addon_available' ) ) {
			$this->_plugin->SetGlobalSetting( 'addon_available_notice_dismissed', true );
			die();
		}
		die( 'Nonce verification failed!' );
	}


	/**
	 * Method: Check if view has shortcut link.
	 */
	public function HasPluginShortcutLink() {
		return true;
	}

	/**
	 * Method: Get View Title.
	 */
	public function GetTitle() {
		return __( 'Activity Log Viewer', 'wp-security-audit-log' );
	}

	/**
	 * Method: Get View Icon.
	 */
	public function GetIcon() {
		return $this->_wpversion < 3.8
			? $this->_plugin->GetBaseUrl() . '/img/logo-main-menu.png'
			: $this->get_icon_encoded();
	}

	/**
	 * Returns an encoded SVG strin gfor the menu icon.
	 *
	 * @method get_icon_encoded
	 * @since
	 * @return [type]
	 */
	private function get_icon_encoded() {
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTEycHgiIGhlaWdodD0iMTEwcHgiIHZpZXdCb3g9IjAgMCAxMTIgMTEwIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IHNrZXRjaHRvb2wgNTIuNiAoNjc0OTEpIC0gaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoIC0tPgogICAgPHRpdGxlPkE2QUQyNDUyLUZERDItNDIwQS05ODMzLTQ3QkJDOTlBQjEzNzwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggc2tldGNodG9vbC48L2Rlc2M+CiAgICA8ZyBpZD0iV1BTQUwtU2NyZWVucyIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9IkN1c3RvbS1pY29ucyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEwMjQuMDAwMDAwLCAtNDYxLjAwMDAwMCkiIGZpbGw9IiNGRkZGRkYiIGZpbGwtcnVsZT0ibm9uemVybyI+CiAgICAgICAgICAgIDxnIGlkPSJDdXN0b20tSWNvbnMiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE1MS4wMDAwMDAsIDIyOC4wMDAwMDApIj4KICAgICAgICAgICAgICAgIDxnIGlkPSJMb2dvIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg4MDcuMDAwMDAwLCAyMDQuNjY2NjY3KSI+CiAgICAgICAgICAgICAgICAgICAgPGcgaWQ9IkF0b21zLS8taWNvbnMtLy1jdXN0b20tcmV2ZXJzZWQtYXVkaXQtbG9nIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1Mi4wMDAwMDAsIDE1LjAwMDAwMCkiPgogICAgICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNzAuMDg0ODc0NSw3OC4zNjU1MDAyIEM3Ny44MDAwOTQyLDc4LjM2NTUwMDIgODQuMDU0OTE2MSw3Mi4xMTA2NzgxIDg0LjA1NDkxNjEsNjQuMzk0NTE5MiBDODQuMDU0OTE2MSw1Ni4xMTk1NTg2IDc2Ljg3Njg5NzUsNDkuNTk2MTM2IDY4LjU1MzEwMDYsNTAuNTExODE5NSBDNjkuMDMxMTM0Myw1MS45MjE1MDIzIDY5LjMwMjU1MjIsNTMuNDI2MDQwNiA2OS4zMDI1NTIyLDU0Ljk5NzI1OTQgQzY5LjMwMjU1MjIsNjIuMTk1MDAwNyA2My44NTcyODgzLDY4LjExNzM1OTMgNTYuODYyNDA2Myw2OC44Nzk5NTkyIEM1OC43MzMyMTc5LDc0LjM5Mjg0MjkgNjMuOTM5OTM0Niw3OC4zNjU1MDAyIDcwLjA4NDg3NDUsNzguMzY1NTAwMiBaIE03MC4wNDA3MzIyLDI1LjU3NzA1NTcgTDEwOC4yNzMwOTUsMjkuNjk5MDM5OCBDMTI0LjgyOTU5LDYzLjc3MDkxNTUgMTA2LjA0NTQwMiwxMDIuODE5NDEzIDY5Ljk4ODEzOTEsMTExLjUyNDUxIEM2OS41NDEwOTc4LDExMS40MTU1NjcgNjkuMTE5NDEzOCwxMTEuMjgzMTQ1IDY4LjY3ODAwNzUsMTExLjE2NzYyOCBMNjguNjc2MTI5Miw5My44NzIwMTIyIEM2OC42NzIzNzI1LDkzLjg3MjAxMjIgNjguNjY5NTU1LDkzLjg3MjAxMjIgNjguNjY1Nzk4NCw5My44NzIwMTIyIEM1My4wNzQ3NjI5LDkzLjEyOTEzNDYgNDAuNjE4NjUxNiw4MC4yMTE4OTM5IDQwLjYxODY1MTYsNjQuNDM4NjYgQzQwLjYxODY1MTYsNTQuOTA4MDM5MSA0Ny41ODI1NDEsMzYuMDA5MjcyNSA2OC42NjU3OTg0LDM1LjAwNjI0NyBDNjguNjY5NTU1LDM1LjAwNjI0NyA2OC42NzIzNzI1LDM1LjAwNTMwNzggNjguNjc2MTI5MiwzNS4wMDUzMDc4IEw2OC42ODE3NjQxLDI1LjcyOTIgQzY5LjczMTc0NzcsMjUuNjEwODY1NSA2OS44NTc1OTU1LDI1LjU5NDg5OTggNzAuMDQwNzMyMiwyNS41NzcwNTU3IFogTTExNC40MjkxMTksODEuNTc0NjE1NCBDMTIyLjYxMjA0MSw2My43NjgwOTU4IDEyMi4wNzAxNDUsNDMuMDc0NTkwOCAxMTIuOTg4NDQ0LDI0LjU2NjUxNjggTDcwLjAwMDE2MTEsMTkuODI2NTY0IEwyNy4wMTA5MzkyLDI0LjU2NjUxNjggQzE3LjkyOTIzODQsNDMuMDczNjUxNiAxNy4zODczNDE2LDYzLjc2NzE1NjYgMjUuNTcxMjAzMiw4MS41NzQ2MTU0IEMzMy43ODIzMDA1LDk5LjQ0MjE4MDcgNDkuOTUwOTIxMiwxMTIuNDc0OTM4IDcwLjAwMDE2MTEsMTE3LjQxNzc1IEM5MC4wNDk0MDEsMTEyLjQ3NDkzOCAxMDYuMjE4MDIyLDk5LjQ0MzExOTggMTE0LjQyOTExOSw4MS41NzQ2MTU0IFogTTExNy40NDEwMTQsMjAuNTMwOTM1NyBDMTI4LjAwNzUzLDQwLjk4MDI1OTIgMTI4LjgyODM1OCw2NC4xMTA4OTE0IDExOS42OTIxODYsODMuOTkyOTYwNyBDMTEwLjYzOTU5OSwxMDMuNjkwMDE1IDkyLjc3MTA5NDIsMTE3Ljk4NTk0NiA3MC42NjY5NjY3LDEyMy4yMTUyMDMgTDcwLjAwMDE2MTQsMTIzLjM3MjA0MyBMNjkuMzMzMzU2LDEyMy4yMTUyMDMgQzQ3LjIyOTIyODUsMTE3Ljk4NTk0NiAyOS4zNTk3ODQ1LDEwMy42OTAwMTUgMjAuMzA4MTM2OCw4My45OTI5NjA3IEMxMS4xNzE5NjQ1LDY0LjExMDg5MTQgMTEuOTkxODUzMyw0MC45NzkzMiAyMi41NTkzMDkyLDIwLjUzMDkzNTcgTDIzLjI3MjEzMzUsMTkuMTUyMjQ1MyBMNzAuMDAwMTYxNCwxNCBMMTE2LjcyODE4OSwxOS4xNTIyNDUzIEwxMTcuNDQxMDE0LDIwLjUzMDkzNTcgWiIgaWQ9ImN1c3RvbS1yZXZlcnNlZC1hdWRpdC1sb2ciPjwvcGF0aD4KICAgICAgICAgICAgICAgICAgICA8L2c+CiAgICAgICAgICAgICAgICA8L2c+CiAgICAgICAgICAgIDwvZz4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==';
	}

	/**
	 * Method: Get View Name.
	 */
	public function GetName() {
		return __( 'Log Viewer', 'wp-security-audit-log' );
	}

	/**
	 * Method: Get View Weight.
	 */
	public function GetWeight() {
		return 1;
	}

	/**
	 * Method: Get View.
	 */
	protected function GetView() {
		// Set page arguments.
		if ( ! $this->page_args ) {
			$this->page_args = new stdClass();

			// @codingStandardsIgnoreStart
			$this->page_args->page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : false;
			$this->page_args->site_id = $this->_plugin->settings()->get_view_site_id();

			// Order arguments.
			$this->page_args->order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : false;
			$this->page_args->order    = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : false;

			// Search arguments.
			$this->page_args->search_term    = ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : false;
			$this->page_args->search_filters = ( isset( $_REQUEST['filters'] ) && is_array( $_REQUEST['filters'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['filters'] ) ) : false;
			// @codingStandardsIgnoreEnd
		}

		// Set events listing view class.
		if ( is_null( $this->_view ) ) {
			// Set the requested view based on POST or GET value. We only care
			// if the view is 'grid' specifically.
			$requested_view = $this->detect_view_type();

			// If 'grid' is requested use it otherwise use list view by default.
			if ( 'grid' !== $requested_view ) {
				$this->_view = new WSAL_AuditLogListView( $this->_plugin, $this->page_args );
			} else {
				$this->_view = new WSAL_AuditLogGridView( $this->_plugin, $this->page_args );
			}

			// if the requested view didn't match the view users last viewed
			// then update their preference.
			if ( $requested_view !== $this->user_last_view ) {
				update_user_meta( get_current_user_id(), 'wsal-selected-main-view', ( in_array( $requested_view, array( 'list', 'grid' ), true ) ) ? $requested_view : 'list' );
				$this->user_last_view = $requested_view;
			}
		}
		return $this->_view;
	}

	/**
	 * Helper to store the views that are supported for the plugins lists.
	 *
	 * @method supported_view_types
	 * @since  4.0.0
	 * @return array
	 */
	public function supported_view_types() {
		return array(
			'list',
			'grid',
		);
	}

	/**
	 * Helper to get the current user selected view.
	 *
	 * @method detect_view_type
	 * @since  4.0.0
	 * @return string
	 */
	public function detect_view_type() {
		// First check if there is a GET/POST request for a specific view.
		if ( defined( 'DOING_AJAX' ) ) {
			$requested_view = ( isset( $_POST['view'] ) ) ? wp_unslash( filter_input( INPUT_POST, 'view', FILTER_SANITIZE_STRING ) ) : '';
		} else {
			$requested_view = ( isset( $_GET['view'] ) ) ? wp_unslash( filter_input( INPUT_GET, 'view', FILTER_SANITIZE_STRING ) ) : '';
		}

		// When there is no GET/POST view requested use the user value.
		if ( empty( $requested_view ) ) {
			$requested_view = $this->user_last_view;
		}

		// return the requested view. This is 'list' by default.
		return ( in_array( $requested_view, $this->supported_view_types(), true ) ) ? $requested_view : 'list';
	}

	/**
	 * Handle Audit Log Form Submission
	 *
	 * @since 3.2.3
	 */
	public function handle_form_submission() {
		// Global WP page now variable.
		global $pagenow;

		// Only run the function on audit log custom page.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // @codingStandardsIgnoreLine
		if ( 'admin.php' !== $pagenow ) {
			return;
		} elseif ( 'wsal-auditlog' !== $page ) { // Page is admin.php, now check auditlog page.
			return; // Return if the current page is not auditlog's.
		}

		// Verify nonce for security.
		if ( isset( $_GET['_wpnonce'] ) ) {
			check_admin_referer( 'bulk-logs' );
		}

		// @codingStandardsIgnoreStart
		$wpnonce     = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : false; // View nonce.
		$search      = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : false; // Search.
		$site_id     = isset( $_GET['wsal-cbid'] ) ? (int) sanitize_text_field( $_GET['wsal-cbid'] ) : false; // Site id.
		$search_save = ( isset( $_REQUEST['wsal-save-search-name'] ) && ! empty( $_REQUEST['wsal-save-search-name'] ) ) ? trim( sanitize_text_field( $_REQUEST['wsal-save-search-name'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( ! empty( $wpnonce ) ) {
			// Remove args array.
			$remove_args = array(
				'_wp_http_referer',
				'_wpnonce',
				'wsal_as_widget_ip',
				'load_saved_search_field',
				'view',
			);

			if ( empty( $site_id ) ) {
				$remove_args[] = 'wsal-cbid';
			}

			if ( empty( $search_save ) ) {
				$remove_args[] = 'wsal-save-search-name';
			}

			if ( empty( $search ) ) {
				$remove_args[] = 's';
			}
			wp_safe_redirect( remove_query_arg( $remove_args ) );
			exit();
		}
	}

	/**
	 * Render view table of Audit Log.
	 *
	 * @since 1.0.0
	 */
	public function Render() {
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
		}

		// Verify nonce for security.
		if ( isset( $_GET['_wpnonce'] ) ) {
			check_admin_referer( 'bulk-logs' );
		}

		$this->GetView()->prepare_items();
		?>
		<form id="audit-log-viewer" method="get">
			<div id="audit-log-viewer-content">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->page_args->page ); ?>" />
				<input type="hidden" id="wsal-cbid" name="wsal-cbid" value="<?php echo esc_attr( empty( $this->page_args->site_id ) ? '0' : $this->page_args->site_id ); ?>" />
				<input type="hidden" id="view" name="view" value="<?php echo ( isset( $_GET['view'] ) && 'grid' === wp_unslash( $_GET['view'] ) ) ? 'grid' : 'list'; ?>" />
				<?php
				/**
				 * Hook: `wsal_auditlog_before_view`
				 *
				 * This action hook is triggered before displaying the audit log view.
				 *
				 * @param WSAL_AuditLogListView $this->_view - Audit log view object.
				 */
				do_action( 'wsal_auditlog_before_view', $this->GetView() );

				// Display the audit log list.
				$this->GetView()->display();

				/**
				 * Hook: `wsal_auditlog_after_view`
				 *
				 * This action hook is triggered after displaying the audit log view.
				 *
				 * @param WSAL_AuditLogListView $this->_view - Audit log view object.
				 */
				do_action( 'wsal_auditlog_after_view', $this->GetView() );
				?>
			</div>
		</form>

		<?php
		if (
			! $this->_plugin->GetGlobalBooleanSetting( 'setup-complete', false )
			&& ! $this->_plugin->GetGlobalBooleanSetting( 'setup-modal-dismissed', false )
		) :
			?>
			<div data-remodal-id="wsal-setup-modal">
				<button data-remodal-action="close" class="remodal-close"></button>
				<p><?php esc_html_e( 'Thank you for installing WP Activity Log. Do you want to run the wizard to configure the basic plugin settings?', 'wp-security-audit-log' ); ?></p>
				<br>
				<button data-remodal-action="confirm" class="remodal-confirm"><?php esc_html_e( 'Yes', 'wp-security-audit-log' ); ?></button>
				<button data-remodal-action="cancel" class="remodal-cancel"><?php esc_html_e( 'No', 'wp-security-audit-log' ); ?></button>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					var wsal_setup_modal = jQuery( '[data-remodal-id="wsal-setup-modal"]' );
					wsal_setup_modal.remodal().open();

					jQuery(document).on('confirmation', wsal_setup_modal, function () {
						<?php $this->_plugin->SetGlobalBooleanSetting( 'setup-modal-dismissed', true ); ?>
						window.location = '<?php echo esc_url( add_query_arg( 'page', 'wsal-setup', admin_url( 'index.php' ) ) ); ?>';
					});

					jQuery(document).on('cancellation', wsal_setup_modal, function () {
						<?php $this->_plugin->SetGlobalBooleanSetting( 'setup-modal-dismissed', true ); ?>
					});

					jQuery(document).on('closed', wsal_setup_modal, function () {
						<?php $this->_plugin->SetGlobalBooleanSetting( 'setup-modal-dismissed', true ); ?>
					});
				});
			</script>
			<?php
		endif;

		$is_search_view = class_exists( 'WSAL_SearchExtension' ) && ( ! empty( $this->page_args->search_filters ) || ! empty( $this->page_args->search_term ) );
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				WsalAuditLogInit(
					<?php
					echo wp_json_encode(
						array(
							'ajaxurl'     => admin_url( 'admin-ajax.php' ),
							'tr8n'        => array(
								'numofitems' => __( 'Please enter the number of alerts you would like to see on one page:', 'wp-security-audit-log' ),
								'searchback' => __( 'All Sites', 'wp-security-audit-log' ),
								'searchnone' => __( 'No Results', 'wp-security-audit-log' ),
							),
							'autorefresh' => array(
								'enabled' => ! $is_search_view ? $this->_plugin->settings()->IsRefreshAlertsEnabled() : false,
								'token'   => $this->_plugin->settings()->is_infinite_scroll() ? $this->get_total_events() : $this->GetView()->get_total_items(),
							),
						)
					);
					?>
				);
			} );
		</script>
		<?php
	}

	/**
	 * Ajax callback to display meta data inspector.
	 */
	public function AjaxInspector() {
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			die( 'Access Denied.' );
		}

		// Filter $_GET array for security.
		$get_array = filter_input_array( INPUT_GET );

		if ( ! isset( $get_array['occurrence'] ) ) {
			die( 'Occurrence parameter expected.' );
		}

		// Get selected db.
		$selected_db      = get_transient( 'wsal_wp_selected_db' );
		$selected_db_user = (int) get_transient( 'wsal_wp_selected_db_user' );

		// Check if archive db is enabled and the current user matches the one who selected archive db.
		if ( ! empty( $selected_db ) && 'archive' === $selected_db && get_current_user_id() === $selected_db_user ) {
			$this->_plugin->settings()->SwitchToArchiveDB(); // Switch to archive DB.
		}

		$occ = new WSAL_Models_Occurrence();
		$occ->Load( 'id = %d', array( (int) $get_array['occurrence'] ) );
		$alert_meta = $occ->GetMetaArray();
		unset( $alert_meta['ReportText'] );

		// Set WSAL_Ref class scripts and styles.
		WSAL_Ref::config( 'stylePath', esc_url( $this->_plugin->GetBaseDir() ) . '/css/wsal-ref.css' );
		WSAL_Ref::config( 'scriptPath', esc_url( $this->_plugin->GetBaseDir() ) . '/js/wsal-ref.js' );

		echo '<!DOCTYPE html><html><head>';
		echo '<style type="text/css">';
		echo 'html, body { margin: 0; padding: 0; }';
		echo '</style>';
		echo '</head><body>';
		wsal_r( $alert_meta );
		echo '</body></html>';
		die;
	}

	/**
	 * Ajax callback to refrest the view.
	 */
	public function AjaxRefresh() {
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			die( 'Access Denied.' );
		}

		// Filter $_POST array for security.
		$post_array = filter_input_array( INPUT_POST );

		// If log count is not set then return error.
		if ( ! isset( $post_array['logcount'] ) ) {
			die( 'Log count parameter expected.' );
		}

		// Total number of alerts.
		$old = (int) $post_array['logcount'];

		// Check if the user is viewing archived db.
		$is_archive = false;
		if ( $this->_plugin->settings()->IsArchivingEnabled() ) {
			$selected_db = get_transient( 'wsal_wp_selected_db' );
			if ( $selected_db && 'archive' === $selected_db ) {
				$is_archive = true;
			}
		}

		// Check for new total number of alerts.
		$occ = new WSAL_Models_Occurrence();
		$new = (int) $occ->Count();

		// If the current view is archive then don't refresh.
		if ( $is_archive ) {
			echo 'false';
		} else {
			// If the count is changed, then return the new count.
			echo $old === $new ? 'false' : esc_html( $new );
		}
		die;
	}

	/**
	 * Ajax callback to set number of alerts to
	 * show on a single page.
	 */
	public function AjaxSetIpp() {
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			die( 'Access Denied.' );
		}

		// Filter $_POST array for security.
		$post_array = filter_input_array( INPUT_POST );

		if ( ! isset( $post_array['count'] ) ) {
			die( 'Count parameter expected.' );
		}
		$this->_plugin->settings()->SetViewPerPage( (int) $post_array['count'] );
		die;
	}

	/**
	 * Ajax callback to search.
	 */
	public function AjaxSearchSite() {
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			die( 'Access Denied.' );
		}

		// Filter $_POST array for security.
		$post_array = filter_input_array( INPUT_POST );

		if ( ! isset( $post_array['search'] ) ) {
			die( 'Search parameter expected.' );
		}
		$grp1 = array();
		$grp2 = array();

		$search = $post_array['search'];

		foreach ( $this->GetView()->get_sites() as $site ) {
			if ( stripos( $site->blogname, $search ) !== false ) {
				$grp1[] = $site;
			} elseif ( stripos( $site->domain, $search ) !== false ) {
				$grp2[] = $site;
			}
		}
		die( json_encode( array_slice( $grp1 + $grp2, 0, 7 ) ) );
	}

	/**
	 * Ajax callback to switch database.
	 */
	public function AjaxSwitchDB() {
		// Filter $_POST array for security.
		$post_array = filter_input_array( INPUT_POST );

		if ( isset( $post_array['selected_db'] ) ) {
			set_transient( 'wsal_wp_selected_db', $post_array['selected_db'], HOUR_IN_SECONDS );
			set_transient( 'wsal_wp_selected_db_user', get_current_user_id(), HOUR_IN_SECONDS );
		}
	}

	/**
	 * Ajax callback to download failed login log.
	 */
	public function wsal_download_failed_login_log() {
		// Get post array through filter.
		$download_nonce = filter_input( INPUT_POST, 'download_nonce', FILTER_SANITIZE_STRING );
		$alert_id       = filter_input( INPUT_POST, 'alert_id', FILTER_SANITIZE_NUMBER_INT );

		// Verify nonce.
		if ( ! empty( $download_nonce ) && wp_verify_nonce( $download_nonce, 'wsal-download-failed-logins' ) ) {
			// Get alert by id.
			$alert     = new WSAL_Models_Occurrence();
			$alert->id = (int) $alert_id;

			// Get users using alert meta.
			$users = $alert->GetMetaValue( 'Users', array() );

			// Check if there are any users.
			if ( ! empty( $users ) && is_array( $users ) ) {
				// Prepare content.
				$content = implode( ',', $users );
				echo esc_html( $content );
			} else {
				echo esc_html__( 'No users found.', 'wp-security-audit-log' );
			}
		} else {
			echo esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' );
		}
		die();
	}

	/**
	 * Ajax callback to download 404 log.
	 */
	public function wsal_download_404_log() {
		// Get post array through filter.
		$nonce    = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
		$filename = filter_input( INPUT_POST, 'log_file', FILTER_SANITIZE_STRING );

		// If file name is empty then return error.
		if ( empty( $filename ) ) {
			// Nonce verification failed.
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Log file does not exist.', 'wp-security-audit-log' ),
				)
			);
			die();
		}

		// Verify nonce.
		if ( ! empty( $filename ) && ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wsal-download-404-log-' . $filename ) ) {

			// Get basename to prevent path traversal attack.
			$filename = basename( $filename );

			// Construct log file path to eliminate the risks of path traversal attack.
			$log_file_path = $this->_plugin->settings()->get_working_dir_path( '404s' ) . $filename;

			// Request the file.
			$response = file_get_contents( $log_file_path, true );

			// Check if the response is valid.
			if ( $response ) {
				// Return the file body.
				echo wp_json_encode(
					array(
						'success'      => true,
						'filename'     => $filename,
						'file_content' => $response,
					)
				);
			} else {
				// Request failed.
				echo wp_json_encode(
					array(
						'success' => false,
						'message' => esc_html__( 'Request to get log file failed.', 'wp-security-audit-log' ),
					)
				);
			}
		} else {
			// Nonce verification failed.
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
		}
		die();
	}

	/**
	 * Ajax callback to handle freemius opt in/out.
	 */
	public function wsal_freemius_opt_in() {
		// Die if not have access.
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			die( 'Access Denied.' );
		}

		// Get post array through filter.
		$nonce  = filter_input( INPUT_POST, 'opt_nonce', FILTER_SANITIZE_STRING ); // Nonce.
		$choice = filter_input( INPUT_POST, 'choice', FILTER_SANITIZE_STRING ); // Choice selected by user.

		// Verify nonce.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wsal-freemius-opt' ) ) {
			// Nonce verification failed.
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		// Check if choice is not empty.
		if ( ! empty( $choice ) ) {
			if ( 'yes' === $choice ) {
				if ( ! is_multisite() ) {
					wsal_freemius()->opt_in(); // Opt in.
				} else {
					// Get sites.
					$sites      = Freemius::get_sites();
					$sites_data = array();

					if ( ! empty( $sites ) ) {
						foreach ( $sites as $site ) {
							$sites_data[] = wsal_freemius()->get_site_info( $site );
						}
					}
					wsal_freemius()->opt_in( false, false, false, false, false, false, false, false, $sites_data );
				}

				// Update freemius state.
                $this->_plugin->SetGlobalSetting( 'freemius_state', 'in' );
			} elseif ( 'no' === $choice ) {
				if ( ! is_multisite() ) {
					wsal_freemius()->skip_connection(); // Opt out.
				} else {
					wsal_freemius()->skip_connection( null, true ); // Opt out for all websites.
				}

				// Update freemius state.
				$this->_plugin->SetGlobalSetting( 'freemius_state', 'skipped' );
			}

			echo wp_json_encode(
				array(
					'success' => true,
					'message' => esc_html__( 'Freemius opt choice selected.', 'wp-security-audit-log' ),
				)
			);
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Freemius opt choice not found.', 'wp-security-audit-log' ),
				)
			);
		}
		exit();
	}

	/**
	 * Method: Render header of the view.
	 */
	public function Header() {
		add_thickbox();

		// Darktooltip styles.
		wp_enqueue_style(
			'darktooltip',
			$this->_plugin->GetBaseUrl() . '/css/darktooltip.css',
			array(),
			'0.4.0'
		);

		// Remodal styles.
		wp_enqueue_style( 'wsal-remodal', $this->_plugin->GetBaseUrl() . '/css/remodal.css', array(), '1.1.1' );
		wp_enqueue_style( 'wsal-remodal-theme', $this->_plugin->GetBaseUrl() . '/css/remodal-default-theme.css', array(), '1.1.1.1' );

		// Audit log styles.
		wp_enqueue_style(
			'auditlog',
			$this->_plugin->GetBaseUrl() . '/css/auditlog.css',
			array(),
			filemtime( $this->_plugin->GetBaseDir() . '/css/auditlog.css' )
		);
	}

	/**
	 * Method: Render footer of the view.
	 */
	public function Footer() {
		wp_enqueue_script( 'jquery' );

		// Darktooltip js.
		wp_enqueue_script(
			'darktooltip', // Identifier.
			$this->_plugin->GetBaseUrl() . '/js/jquery.darktooltip.js', // Script location.
			array( 'jquery' ), // Depends on jQuery.
			'0.4.0', // Script version.
			true
		);

		// Remodal script.
		wp_enqueue_script(
			'wsal-remodal-js',
			$this->_plugin->GetBaseUrl() . '/js/remodal.min.js',
			array(),
			'1.1.1',
			true
		);

		// WP Suggest Script.
		wp_enqueue_script( 'suggest' );

		// Audit log script.
		wp_register_script(
			'auditlog',
			$this->_plugin->GetBaseUrl() . '/js/auditlog.js',
			array(),
			filemtime( $this->_plugin->GetBaseDir() . '/js/auditlog.js' ),
			true
		);
		$audit_log_data = array(
			'page'                => isset( $this->page_args->page ) ? $this->page_args->page : false,
			'siteId'              => isset( $this->page_args->site_id ) ? $this->page_args->site_id : false,
			'orderBy'             => isset( $this->page_args->order_by ) ? $this->page_args->order_by : false,
			'order'               => isset( $this->page_args->order ) ? $this->page_args->order : false,
			'searchTerm'          => isset( $this->page_args->search_term ) ? $this->page_args->search_term : false,
			'searchFilters'       => isset( $this->page_args->search_filters ) ? $this->page_args->search_filters : false,
			'viewerNonce'         => wp_create_nonce( 'wsal_auditlog_viewer_nonce' ),
			'infiniteScroll'      => $this->_plugin->settings()->is_infinite_scroll(),
			'userView'            => ( in_array( $this->user_last_view, $this->supported_view_types(), true ) ) ? $this->user_last_view : 'list',
			'installAddonStrings' => array(
				'defaultButton'   => esc_html( 'Install and activate extension', 'wp-security-audit-log' ),
				'installingText'  => esc_html( 'Installing extension', 'wp-security-audit-log' ),
				'otherInstalling' => esc_html( 'Other extension installing', 'wp-security-audit-log' ),
				'addonInstalled'  => esc_html( 'Installed', 'wp-security-audit-log' ),
				'installedReload' => esc_html( 'Installed... reloading page', 'wp-security-audit-log' ),
				'buttonError'     => esc_html( 'Problem enabling', 'wp-security-audit-log' ),
				'msgError'        => sprintf(
					/* translators: 1 - an opening link tag, 2 - the closing tag. */
					__( '<br>An error occurred when trying to install and activate the plugin. Please try install it again from the %1$sevent settings%2$s page.', 'wp-security-audit-log' ),
					'<a href="' . esc_url( add_query_arg( 'page', 'wsal-togglealerts#tab-third-party-plugins', network_admin_url( 'admin.php' ) ) ) . '">',
					'</a>'
				),
			),
		);
		wp_localize_script( 'auditlog', 'wsalAuditLogArgs', $audit_log_data );
		wp_enqueue_script( 'auditlog' );
	}

	/**
	 * Method: Load WSAL Notice Pointer.
	 *
	 * @param string $hook_suffix - Current hook suffix.
	 * @since 3.2
	 */
	public function load_pointers( $hook_suffix ) {
		// Don't run on WP < 3.3.
		if ( get_bloginfo( 'version' ) < '3.3' ) {
			return;
		}

		// Don't display notice if the wizard notice is showing.
		if (
			! $this->_plugin->GetGlobalBooleanSetting( 'setup-complete', false )
			&& ! $this->_plugin->GetGlobalBooleanSetting( 'setup-modal-dismissed', false )
		) {
			return;
		}

		// Get screen id.
		$screen    = get_current_screen();
		$screen_id = $screen->id;

		// Get pointers for this screen.
		$pointers = apply_filters( 'wsal_pointers_' . $screen_id, array() );

		if ( ! $pointers || ! is_array( $pointers ) ) {
			return;
		}

		// Get dismissed pointers.
		$dismissed      = explode( ',', (string) $this->_plugin->GetGlobalSetting( 'dismissed-privacy-notice', true ) );
		$valid_pointers = array();

		// Check pointers and remove dismissed ones.
		foreach ( $pointers as $pointer_id => $pointer ) {
			// Sanity check.
			if (
				in_array( $pointer_id, $dismissed )
				|| empty( $pointer )
				|| empty( $pointer_id )
				|| empty( $pointer['target'] )
				|| empty( $pointer['options'] )
			) {
				continue;
			}
			$pointer['pointer_id'] = $pointer_id;

			// Add the pointer to $valid_pointers array.
			$valid_pointers['pointers'][] = $pointer;
		}

		// No valid pointers? Stop here.
		if ( empty( $valid_pointers ) ) {
			return;
		}

		// Add pointers style to queue.
		wp_enqueue_style( 'wp-pointer' );

		// Add pointers script to queue. Add custom script.
		wp_enqueue_script(
			'auditlog-pointer',
			$this->_plugin->GetBaseUrl() . '/js/auditlog-pointer.js',
			array( 'wp-pointer' ),
			filemtime( $this->_plugin->GetBaseDir() . '/js/auditlog-pointer.js' ),
			true
		);

		// Add pointer options to script.
		wp_localize_script( 'auditlog-pointer', 'wsalPointer', $valid_pointers );
	}

	/**
	 * Method: Register privacy pointer for WSAL.
	 *
	 * @param array $pointer - Current screen pointer array.
	 * @return array
	 * @since 3.2
	 */
	public function register_privacy_pointer( $pointer ) {
		$is_current_view = $this->_plugin->views->GetActiveView() == $this;
		if ( current_user_can( 'manage_options' ) && $is_current_view && ! isset( $pointer['wsal_privacy'] ) ) {
			$pointer['wsal_privacy'] = array(
				'target'  => '#toplevel_page_wsal-auditlog .wp-first-item',
				'options' => array(
					'content'  => sprintf(
						'<h3> %s </h3> <p> %s </p> <p><strong>%s</strong></p>',
						__( 'WordPress Activity Log', 'wp-security-audit-log' ),
						__( 'When a user makes a change on your website the plugin will keep a record of that event here. Right now there is nothing because this is a new install.', 'wp-security-audit-log' ),
						__( 'Thank you for using WP Activity Log', 'wp-security-audit-log' )
					),
					'position' => array(
						'edge'  => 'left',
						'align' => 'top',
					),
				),
			);
		}
		return $pointer;
	}

	/**
	 * Method: Ajax request handler to exclude URL from
	 * the event.
	 *
	 * @since 3.2.2
	 */
	public function wsal_exclude_url() {
		// Die if user does not have permission to disable.
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'edit' ) ) {
			echo '<p>' . esc_html__( 'Error: You do not have sufficient permissions to exclude this URL.', 'wp-security-audit-log' ) . '</p>';
			die();
		}

		// Set filter input args.
		$filter_input_args = array(
			'nonce' => FILTER_SANITIZE_STRING,
			'url'   => FILTER_SANITIZE_STRING,
		);

		// Filter $_POST array for security.
		$post_array = filter_input_array( INPUT_POST, $filter_input_args );

		if ( isset( $post_array['nonce'] ) && ! wp_verify_nonce( $post_array['nonce'], 'wsal-exclude-url-' . $post_array['url'] ) ) {
			die();
		}

		$excluded_urls = $this->_plugin->GetGlobalSetting( 'excluded-urls' );
		if ( isset( $excluded_urls ) && '' !== $excluded_urls ) {
			$excluded_urls .= ',' . esc_url( $post_array['url'] );
		} else {
			$excluded_urls = esc_url( $post_array['url'] );
		}
		$this->_plugin->SetGlobalSetting( 'excluded-urls', $excluded_urls );
		$settings_exclude_url = add_query_arg(
			array(
				'page' => 'wsal-settings',
				'tab'  => 'exclude-objects',
			),
			admin_url( 'admin.php' )
		);
		echo '<p>URL ' . esc_html( $post_array['url'] ) . ' is no longer being monitored.<br />Enable the monitoring of this URL again from the <a href="' . esc_url( $settings_exclude_url ) . '">Excluded Objects</a> tab in the plugin settings.</p>';
		die;
	}

	/**
	 * Method: Ajax request handler to dismiss adverts.
	 *
	 * @since 3.2.4
	 */
	public function wsal_dismiss_advert() {
		// Die if user does not have permission to dismiss.
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'You do not have sufficient permissions to dismiss this notice.', 'wp-security-audit-log' ),
				)
			);
			die();
		}

		// Filter $_POST array for security.
		// @codingStandardsIgnoreStart
		$nonce  = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		$advert = isset( $_POST['advert'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['advert'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( ! empty( $nonce ) && ! wp_verify_nonce( $nonce, 'wsal_dismiss_advert' ) ) {
			// Nonce verification failed.
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
			die();
		}

		$advert = 2 === $advert ? '0' : $advert + 1;
		$this->_plugin->SetGlobalSetting( 'premium-advert', $advert );
		$set_transient_fn = $this->_plugin->IsMultisite() ? 'set_site_transient' : 'set_transient';
		$set_transient_fn( 'wsal-is-advert-dismissed', true, MONTH_IN_SECONDS );
		echo wp_json_encode(
			array(
				'success' => true,
			)
		);
		die();
	}

	/**
	 * Method: Ajax request handler to dismiss pointers.
	 *
	 * @since 3.2.4
	 */
	public function dismiss_wp_pointer() {
		// @codingStandardsIgnoreStart
		$pointer = sanitize_text_field( wp_unslash( $_POST['pointer'] ) );
		// @codingStandardsIgnoreEnd

		if ( $pointer != sanitize_key( $pointer ) ) {
			wp_die( 0 );
		}

		$dismissed = array_filter( explode( ',', (string) $this->_plugin->GetGlobalSetting( 'dismissed-privacy-notice', true ) ) );

		if ( in_array( $pointer, $dismissed ) ) {
			wp_die( 0 );
		}

		$dismissed[] = $pointer;
		$dismissed   = implode( ',', $dismissed );

		$this->_plugin->SetGlobalSetting( 'dismissed-privacy-notice', $dismissed );
		wp_die( 1 );
	}

	/**
	 * Infinite Scroll Events AJAX Hanlder.
	 *
	 * @since 3.3.1.1
	 */
	public function infinite_scroll_events() {
		// Check user permissions.
		if ( ! $this->_plugin->settings()->CurrentUserCan( 'view' ) ) {
			die( esc_html__( 'Access Denied', 'wp-security-audit-log' ) );
		}

		// Verify nonce.
		if ( isset( $_POST['wsal_viewer_security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wsal_viewer_security'] ) ), 'wsal_auditlog_viewer_nonce' ) ) {
			// Get $_POST arguments.
			$paged = isset( $_POST['page_number'] ) ? sanitize_text_field( wp_unslash( $_POST['page_number'] ) ) : 0;

			// Query events.
			$events_query = $this->GetView()->query_events( $paged );
			if ( ! empty( $events_query['items'] ) ) {
				foreach ( $events_query['items'] as $event ) {
					$this->GetView()->single_row( $event );
				}
			}
			exit();
		} else {
			die( esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ) );
		}
	}

	/**
	 * Return the total number of events in audit log.
	 *
	 * @return int
	 */
	public function get_total_events() {
		$occ = new WSAL_Models_Occurrence();
		return (int) $occ->Count();
	}
}
