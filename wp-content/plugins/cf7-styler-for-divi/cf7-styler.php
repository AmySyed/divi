<?php
/*
Plugin Name: Contact Form 7 Styler for Divi
Plugin URI:  https://divipeople.com
Description: Design beuatiful contact forms with <strong>Contact Form 7 Styler for Divi</strong>
Version:     1.1.6
Author:      Divi People
Author URI:  https://divipeople.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: dvppl-cf7-styler
Domain Path: /languages

Contact Form 7 Styler for Divi is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Contact Form 7 Styler for Divi is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with CF7 Styler. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

if ( ! function_exists( 'dipe_cf7_fs' ) ) {
    // Create a helper function for easy SDK access.
    function dipe_cf7_fs() {
        global $dipe_cf7_fs;

        if ( ! isset( $dipe_cf7_fs ) ) {

            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $dipe_cf7_fs = fs_dynamic_init( array(
                'id'                  => '6220',
                'slug'                => 'cf7-styler-for-divi',
                'type'                => 'plugin',
                'public_key'          => 'pk_5ed03b8670d0cfbf345aa03dc6c80',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'dipe_cf7_styler_options',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'et_divi_options',
                    ),
                ),
            ) );
        }

        return $dipe_cf7_fs;
    }

    // Init Freemius.
    dipe_cf7_fs();
    // Signal that SDK was initiated.
    do_action( 'dipe_cf7_fs_loaded' );
}

// Defines
define('DIPE_CF7_VERSION', '1.1.6' );
define('DIPE_CF7_STABLE_VERSION', '1.1.3' );
define('DIPE_CF7_URL', plugins_url( '/', __FILE__ ) );
define('DIPE_ASSETS_URL', trailingslashit( DIPE_CF7_URL . 'assets' ) );
define('DIPE_CF7_PATH', plugin_dir_path( __FILE__ ) );
define('DIPE_CF7_PLUGIN_BASE', plugin_basename( __FILE__ ) );

// Localize
load_plugin_textdomain( 
	'dvppl-cf7-styler', 
	false,
	dirname( plugin_basename( __FILE__ ) ) . '/languages/' 
);

// Final Class
if( ! class_exists( 'Dipe_Cf7_Module' ) ) {

	final class Dipe_Cf7_Module {

		private static $instance;

		private function __construct() {
			register_activation_hook( __FILE__, [$this, 'activate'] );
		}

		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Dipe_Cf7_Module ) ) {
				self::$instance = new Dipe_Cf7_Module();
				self::$instance->init();
				self::$instance->includes();
			}

			return self::$instance;
		}

		private function init() {
			add_action('divi_extensions_init', [$this, 'initialize_extension']);
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		}

		public function activate(){
			update_option('dipe_version', DIPE_CF7_VERSION);
		}

		public function enqueue_scripts() {
			$options = get_option( 'dipe_options' );
			if ( 'on' === $options['grid'] ) {
				wp_enqueue_style('dipe-grid', DIPE_ASSETS_URL . 'css/cf7-grid.css');
			}

			wp_enqueue_style('dipe-module', DIPE_ASSETS_URL . 'css/module.css');
		}

		private function includes() {
			$options = get_option( 'dipe_options' );

			if ( 'on' === $options['grid'] ) {
				require_once DIPE_CF7_PATH . 'includes/shortcode.php';
				require_once DIPE_CF7_PATH . 'includes/tag.php';
			}
			
			if ( is_admin() ) {
				require_once DIPE_CF7_PATH . 'includes/notice.php';
				require_once DIPE_CF7_PATH . 'includes/admin/admin.php';
				require_once DIPE_CF7_PATH . 'includes/admin/rollback.php';
			}
		}

		public function initialize_extension() {
			require_once DIPE_CF7_PATH . 'includes/Cf7StylerMain.php';
		}
	}
}

// Instance function
function dipe_cf7_styler_module() {
	return Dipe_Cf7_Module::instance();
}

// Kickoff
dipe_cf7_styler_module();