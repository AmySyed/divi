<?php
/**
 * Plugin Name: WP Activity Log Extension for WooCommerce
 * Plugin URI: https://wpactivitylog.com/extensions/
 * Description: A WP Activity Log plugin extension for WooCommerce
 * Text Domain: wp-security-audit-log
 * Author: WP White Security
 * Author URI: http://www.wpwhitesecurity.com/
 * Version: 1.1
 * License: GPL2
 * Network: true
 *
 * @package Wsal
 * @subpackage Wsal Custom Events Loader
 */

/*
	Copyright(c) 2020  WP White Security  (email : info@wpwhitesecurity.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'plugins_loaded', 'wsal_woocommerce_extension_init_actions' );

/**
 * Check if plugin is being installed via a multisite child site, if so, show notice.
 */
function wsal_woocommerce_extension_init_actions() {
	if ( is_multisite() && function_exists( 'is_super_admin' ) && is_super_admin() && ! is_network_admin() ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}
}

/**
 * Display admin notice if WSAL is not installed.
 */
function wsal_woocommerce_extension_install_notice() {
	$plugin_installer = new WSALExtension_PluginInstallerAction();
	$screen = get_current_screen();

	// First lets check if WSAL is installed, but not active.
	if ( $plugin_installer->is_plugin_installed( 'wp-security-audit-log/wp-security-audit-log.php' ) && ! is_plugin_active( 'wp-security-audit-log/wp-security-audit-log.php' ) ) : ?>
		<div class="notice notice-success is-dismissible wsal-addons-template-notice">
			<?php
				printf(
					'<p>%1$s &nbsp;&nbsp;<button class="activate-addon button button-primary" data-plugin-slug="wp-security-audit-log/wp-security-audit-log.php" data-plugin-download-url="%2$s" data-plugins-network="%4$s" data-nonce="%3$s">%5$s</button><span class="spinner" style="display: none; visibility: visible; float: none; margin: 0 0 0 8px;"></span></p>',
					esc_html__( 'WP Activity Log is installed but not active.', 'wp-security-audit-log' ),
					esc_url( 'https://downloads.wordpress.org/plugin/wp-security-audit-log.latest-stable.zip' ),
					esc_attr( wp_create_nonce( 'wsal-install-addon' ) ),
					( is_a( $screen, '\WP_Screen' ) && isset( $screen->id ) && 'plugins-network' === $screen->id ) ? true : false, // confirms if we are on a network or not.
					esc_html__( 'Activate WP Activity Log.', 'wp-security-audit-log' )
				);
			?>
		</div>
	<?php elseif ( ! class_exists( 'WpSecurityAuditLog' ) ) : ?>
		<div class="notice notice-success is-dismissible wsal-addons-template-notice">
			<?php
				printf(
					'<p>%1$s &nbsp;&nbsp;<button class="install-wsal button button-primary" data-plugin-slug="wp-security-audit-log/wp-security-audit-log.php" data-plugin-download-url="%2$s" data-plugins-network="%4$s" data-nonce="%3$s">%5$s</button><span class="spinner" style="display: none; visibility: visible; float: none; margin: 0 0 0 8px;"></span></p>',
					esc_html__( 'This extension requires the WP Activity Log Log plugin to work.', 'wp-security-audit-log' ),
					esc_url( 'https://downloads.wordpress.org/plugin/wp-security-audit-log.latest-stable.zip' ),
					esc_attr( wp_create_nonce( 'wsal-install-addon' ) ),
					( is_a( $screen, '\WP_Screen' ) && isset( $screen->id ) && 'plugins-network' === $screen->id ) ? true : false, // confirms if we are on a network or not.
					esc_html__( 'Install WP Activity Log.', 'wp-security-audit-log' )
				);
			?>
		</div>
	<?php
	endif;
}

add_action( 'admin_init', 'wsal_woocommerce_extension_init_install_notice' );

function wsal_woocommerce_extension_init_install_notice() {
	// Check if main plugin is installed.
	if ( ! class_exists( 'WpSecurityAuditLog' ) && ! class_exists( 'WSAL_AlertManager' ) ) {
		// Check if the notice was already dismissed by the user.
		if ( get_option( 'wsal_forms_notice_dismissed' ) != true ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- this may be truthy and not explicitly bool
			if ( ! class_exists( 'WSALExtension_PluginInstallerAction' ) ) {
				require_once 'wp-security-audit-log/classes/PluginInstallerAction.php';
			}
			$plugin_installer = new WSALExtension_PluginInstallerAction();
			if ( is_multisite() && is_network_admin() ) {
				add_action( 'admin_notices', 'wsal_woocommerce_extension_install_notice' );
				add_action( 'network_admin_notices', 'wsal_woocommerce_extension_install_notice', 10, 1 );
			} elseif ( ! is_multisite() ) {
				add_action( 'admin_notices', 'wsal_woocommerce_extension_install_notice' );
			}

		}
	} else {
		// Reset the notice if the class is not found.
		delete_option( 'wsal_forms_notice_dismissed' );
	}
}

/**
 * Load our js file to handle ajax.
 */
function wsal_woocommerce_extension_scripts() {
	wp_enqueue_script(
		'wsal-addons-template-scripts',
		plugins_url( 'assets/js/scripts.js', __FILE__ ),
		array( 'jquery' ),
		'1.0',
		true
	);

	$script_data = array(
		'ajaxURL'           => admin_url( 'admin-ajax.php' ),
		'installing'        => esc_html__( 'Installing, please wait', 'wp-security-audit-log' ),
		'already_installed' => esc_html__( 'Already installed', 'wp-security-audit-log' ),
		'installed'         => esc_html__( 'Extension installed', 'wp-security-audit-log' ),
		'activated'         => esc_html__( 'Extension activated', 'wp-security-audit-log' ),
		'failed'            => esc_html__( 'Install failed', 'wp-security-audit-log' ),
	);

	// Send ajax url to JS file.
	wp_localize_script( 'wsal-addons-template-scripts', 'WSALWPFormsData', $script_data );
}
add_action( 'admin_enqueue_scripts', 'wsal_woocommerce_extension_scripts' );

/**
 * Update option if user clicks dismiss.
 */
function wsal_woocommerce_extension_dismiss_notice() {
	update_option( 'wsal_forms_notice_dismissed', true );
}
add_action( 'wp_ajax_wsal_woocommerce_extension_dismiss_notice', 'wsal_woocommerce_extension_dismiss_notice' );

/**
* Hook into WSAL's action that runs before sensors get loaded.
*/
add_action( 'wsal_before_sensor_load', 'wsal_woocommerce_extension_mu_plugin_add_custom_sensors_and_events_dirs' );
add_action( 'init', 'wsal_woocommerce_extension_mu_plugin_add_custom_sensors_and_events_dirs' );

/**
 * Used to hook into the `wsal_before_sensor_load` action to add some filters
 * for including custom sensor and event directories.
 *
 * @method wsal_mu_plugin_add_custom_sensors_and_events_dirs
 */
function wsal_woocommerce_extension_mu_plugin_add_custom_sensors_and_events_dirs( $sensor ) {
	add_filter( 'wsal_custom_sensors_classes_dirs', 'wsal_woocommerce_extension_mu_plugin_custom_sensors_path' );
	add_filter( 'wsal_custom_alerts_dirs', 'wsal_woocommerce_extension_mu_plugin_add_custom_events_path', 3 );
	return $sensor;
}

/**
 * Adds a new path to the sensors directory array which is checked for when the
 * plugin loads the sensors.
 *
 * @method wsal_mu_plugin_custom_sensors_path
 * @since  1.0.0
 * @param  array $paths An array containing paths on the filesystem.
 * @return array
 */
function wsal_woocommerce_extension_mu_plugin_custom_sensors_path( $paths = array() ) {
	$paths   = ( is_array( $paths ) ) ? $paths : array();
	$paths[] = trailingslashit( trailingslashit( dirname( __FILE__ ) ) . 'wp-security-audit-log' . DIRECTORY_SEPARATOR . 'custom-sensors' );
	return $paths;
}

/**
 * Adds a new path to the custom events directory array which is checked for
 * when the plugin loads all of the events.
 *
 * @method wsal_mu_plugin_add_custom_events_path
 * @since  1.0.0
 * @param  array $paths An array containing paths on the filesystem.
 * @return array
 */
function wsal_woocommerce_extension_mu_plugin_add_custom_events_path( $paths ) {
	$paths   = ( is_array( $paths ) ) ? $paths : array();
	$paths[] = trailingslashit( dirname( __FILE__ ) ) . 'wp-security-audit-log';
	return $paths;
}

/**
 * Adds new custom event objects for our plugin
 *
 * @method wsal_woocommerce_extension_add_custom_event_objects
 * @since  1.0.0
 * @param  array $objects An array of default objects.
 * @return array
 */
function wsal_woocommerce_extension_add_custom_event_objects( $objects ) {
	$new_objects = array(
		'woocommerce-product'  => __( 'WooCommerce Product', 'wp-security-audit-log' ),
		'woocommerce-store'    => __( 'WooCommerce Store', 'wp-security-audit-log' ),
		'woocommerce-coupon'   => __( 'WooCommerce Coupon', 'wp-security-audit-log' ),
		'woocommerce-category' => __( 'WooCommerce Category', 'wp-security-audit-log' ),
		'woocommerce-tag'      => __( 'WooCommerce Tag', 'wp-security-audit-log' ),
		'woocommerce-order'    => __( 'WooCommerce Order', 'wp-security-audit-log' ),
	);

	// combine the two arrays.
	$objects = array_merge( $objects, $new_objects );

	return $objects;
}

/**
 * Adds new ignored CPT for our plugin
 *
 * @method wsal_woocommerce_extension_add_custom_event_object_text
 * @since  1.0.0
 * @param  array $post_types An array of default post_types.
 * @return array
 */
function wsal_woocommerce_extension_add_custom_ignored_cpt( $post_types ) {
	$new_post_types = array(
		'product',             // WooCommerce Product CPT.
		'shop_coupon',         // WooCommerse Coupon CPT.
		'shop_order',          // WooCommerce Order CPT.
		'shop_order_refund',   // WooCommerce Order Refund CPT.
		'product_variation',   // WooCommerce Product Variation CPT.
		'wc_product_tab',      // WooCommerce Product Tab CPT.
	);

	// combine the two arrays.
	$post_types = array_merge( $post_types, $new_post_types );
	return $post_types;
}

/**
 * Adds new meta formatting for our plugion
 *
 * @method wsal_woocommerce_extension_add_custom_meta_format
 * @since  1.0.0
 */
function wsal_woocommerce_extension_add_custom_meta_format( $value, $name ) {
	$check_value = (string) $value;

	if ( '%EditorLinkProduct%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			return '<a target="_blank" href="' . esc_url( $value ) . '">' . __( 'View product in editor', 'wp-security-audit-log' ) . '</a>';
		} else {
			return '';
		}
	}

	if ( '%StockOrderID%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			$order     = get_post( $value );
			$new_order = new WC_Order( $value );
			$editor_title = wsal_woocommerce_extension_get_order_title( $new_order );
			$editor_link  = wsal_woocommerce_extension_get_editor_link( $order );
			return '<br>' . __( 'Order name:', 'wp-security-audit-log' ) . ' <a target="_blank" href="' . esc_url( $editor_link['value'] ) . '">' . $editor_title . '</a>';
		} else {
			return '';
		}
	}

	if ( '%EditorLinkCoupon%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			return '<a target="_blank" href="' . esc_url( $value ) . '">' . __( 'View coupon in editor', 'wp-security-audit-log' ) . '</a>';
		} else {
			return '';
		}
	}

	if ( '%OldCouponUsageMetaValue%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			return 'Previous usage restriction: <strong>' . $check_value . '</strong><br>';
		} else {
			return '';
		}
	}
	if ( '%NewCouponUsageMetaValue%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			return 'New usage restriction: <strong>' . $check_value . '</strong><br>';
		} else {
			return '';
		}
	}

	return $value;
}

/**
 * Adds new meta formatting for our plugion
 *
 * @method wsal_woocommerce_extension_add_custom_meta_format_value
 * @since  1.0.0
 */
 function wsal_woocommerce_extension_add_custom_meta_format_value( $value, $name ) {
 	$check_value = (string) $value;
	if ( '%ProductTagLink%' === $name ) {
 		if ( 'NULL' !== $check_value ) {
 			return '<a target="_blank" href="' . esc_url( $value ) . '">' . __( 'View tag', 'wp-security-audit-log' ) . '</a>';
 		} else {
 			return '';
 		}
 	}
	if ( '%EditorLinkProduct%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			return '<a target="_blank" href="' . esc_url( $value ) . '">' . __( 'View product in editor', 'wp-security-audit-log' ) . '</a>';
		} else {
			return '';
		}
	}
	if ( '%EditorLinkCoupon%' === $name ) {
		if ( 'NULL' !== $check_value ) {
			return '<a target="_blank" href="' . esc_url( $value ) . '">' . __( 'View coupon in editor', 'wp-security-audit-log' ) . '</a>';
		} else {
			return '';
		}
	}
 	return $value;
 }

 function wsal_woocommerce_extension_load_public_sensors( $value ) {
	$value[] = 'FrontendWooCommerce';
 	return $value;
 }

 /**
	* Get editor link.
	*
	* @param WP_Post $post        - Product post object.
	* @return array  $editor_link - Name and value link.
	*/
function wsal_woocommerce_extension_get_editor_link( $post ) {
	 // Meta value key.
	 if ( 'shop_order' === $post->post_type ) {
		 $name = 'EditorLinkOrder';
	 } else {
		 $name = 'EditorLinkProduct';
	 }

	 // Get editor post link URL.
	 $value = get_edit_post_link( $post->ID );

	 // If the URL is not empty then set values.
	 if ( ! empty( $value ) ) {
		 $editor_link = array(
			 'name'  => $name, // Meta key.
			 'value' => $value, // Meta value.
		 );
	 } else {
		 // Get post object.
		 $post = get_post( $post->ID );

		 // Set URL action.
		 if ( 'revision' === $post->post_type ) {
			 $action = '';
		 } else {
			 $action = '&action=edit';
		 }

		 // Get and check post type object.
		 $post_type_object = get_post_type_object( $post->post_type );
		 if ( ! $post_type_object ) {
			 return;
		 }

		 // Set editor link manually.
		 if ( $post_type_object->_edit_link ) {
			 $link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
		 } else {
			 $link = '';
		 }

		 $editor_link = array(
			 'name'  => $name, // Meta key.
			 'value' => $link, // Meta value.
		 );
	 }

	 return $editor_link;
 }

 function wsal_woocommerce_extension_get_order_title( $order ) {
	 if ( ! $order ) {
		 return false;
	 }
	 if ( is_int( $order ) ) {
		 $order = new WC_Order( $order );
	 }
	 if ( ! $order instanceof WC_Order ) {
		 return false;
	 }

	 if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
		 $buyer = trim( sprintf( '%1$s %2$s', $order->get_billing_first_name(), $order->get_billing_last_name() ) );
	 } elseif ( $order->get_billing_company() ) {
		 $buyer = trim( $order->get_billing_company() );
	 } elseif ( $order->get_customer_id() ) {
		 $user  = get_user_by( 'id', $order->get_customer_id() );
		 $buyer = ucwords( $user->display_name );
	 }
	 return '#' . $order->get_order_number() . ' ' . $buyer;
 }

/**
 * Add our filters.
 */
add_filter( 'wsal_link_filter', 'wsal_woocommerce_extension_add_custom_meta_format_value', 10, 2 );
add_filter( 'wsal_meta_formatter_custom_formatter', 'wsal_woocommerce_extension_add_custom_meta_format', 10, 2 );
add_filter( 'wsal_event_objects', 'wsal_woocommerce_extension_add_custom_event_objects' );
add_filter( 'wsal_ignored_custom_post_types', 'wsal_woocommerce_extension_add_custom_ignored_cpt' );
add_filter( 'wsal_load_public_sensors', 'wsal_woocommerce_extension_load_public_sensors' );
