<?php

defined( 'ABSPATH' ) || die();

class Dipe_Admin {

	public function __construct() {
		add_action( 'admin_menu', [ __CLASS__, 'add_menu'], 99 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts'], 99 );
        add_action( 'admin_post_cf7_styler_rollback', [ $this, 'post_cf7_styler_rollback' ] );
	}

	public static function add_menu() {
        add_submenu_page( 
            'et_divi_options', 
            'CF7 Styler', 
            'CF7 Styler',
            'manage_options',
            'dipe_cf7_styler_options',
            [__CLASS__, 'render_page']
        );
	}

    public static function post_cf7_styler_rollback() {
        
        check_admin_referer( 'cf7_styler_rollback' );

        $plugin_slug = basename( DIPE_CF7_PATH, '.php' );

        $rollback = new Dipe_Rollback(
            [
                'version'     => DIPE_CF7_STABLE_VERSION,
                'plugin_name' => DIPE_CF7_PLUGIN_BASE,
                'plugin_slug' => $plugin_slug,
                'package_url' => sprintf( 'https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, DIPE_CF7_STABLE_VERSION ),
            ]
        );
        $rollback->run();
        wp_die(
            '', __( 'Rollback to Previous Version', 'dvppl-cf7-styler' ), [
            'response' => 200,
            ]
        );
    }

    public static function render_page() {

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user');
        }

        if ( isset( $_POST['options'] ) ) {

            $value = $_POST['options'];

            update_option( 'dipe_options', $value );

        }

        $options = get_option( 'dipe_options' );

    ?>
        <div class="wrap">
            <div id="dipe-header">
                <h1>
                    <?php echo __('CF7 Styler for Divi', 'dvppl-cf7-styler'); ?>
                    <span style="font-size: 10px;">v<?php echo DIPE_CF7_VERSION; ?></span>
                </h1>
                <div id="dipe-cf7-styler-tabs-wrapper" class="nav-tab-wrapper dipe-cf7-styler">
                    <a id='dipe-cf7-styler-tab-general' class='nav-tab nav-tab-active' href='#tab-general'>General</a>
                    <a id='dipe-cf7-styler-tab-debug' class='nav-tab' href='#tab-debug'>Debug</a>
                </div>                
            </div>
            
            <div class="dipe-wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <div id="tab-general" class="dipe-cf7-styler-form-page">
                                <div class="postbox dipe-postbox">
                                    <h3 class="hndle">Rollback to Previous Version</h3>
                                    <div class="inside">
                                        <p>Experiencing an issue with CF7 Styler for Divi <?php echo DIPE_CF7_VERSION; ?> ? Rollback to a previous version before the issue appeared.</p>
                                        <table class="form-table">
                                            <tbody>
                                                <tr class="cf7_styler_rollback">
                                                    <th scope="row">Rollback Version</th>
                                                    <td>
                                                        <div id="cf7_styler_rollback">
                                                            <div>
                                                                <?php echo sprintf( '<a target="_blank" href="%1$s" class="button wdc-btn wdc-rollback-button">%2$s</a>', wp_nonce_url( admin_url( 'admin-post.php?action=cf7_styler_rollback' ), 'cf7_styler_rollback' ), __('Rollback to Version ' . DIPE_CF7_STABLE_VERSION, 'dvppl-cf7-styler') );
                                                                ?>
                                                            </div>

                                                            <p class="description">
                                                                <span style="color: red;">Warning: Please backup your database before making the rollback.</span>
                                                            </p>

                                                         </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="postbox dipe-postbox">
                                    <h3 class="hndle">Grid Builder</h3>
                                    <div class="inside">
                                        <form id="dipe-cf7-styler-form" method="post">
                                            <table class="form-table">
                                                <tbody>
                                                    <tr class="cf7_styler_grid">
                                                        <th scope="row">Contact Form 7 Grid Builder</th>
                                                        <td>
                                                            <div id="cf7_styler_grid">
                                                                <div>
                                                                    <input type="hidden" name='options[grid]' value='off' />
                                                                    <input 
                                                                        type="checkbox" 
                                                                        class="checkbox"
                                                                        id="cf7-styler-grid"
                                                                        name='options[grid]'
                                                                        value='on' 
                                                                        <?php checked( $options['grid'], 'on', true ); ?>
                                                                    >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                            <?php wp_nonce_field( 'dipe_cf7_styler_action' ); ?>
                                            <p><input type="submit" value="Save All Changes" class="button button-primary"></p>
                                        </form>
                                    </div>
                                </div>


                                <div class="postbox dipe-postbox">
                                    <h3 class="hndle">Wow Divi Carousel Lite</h3>
                                    <div class="inside">
                                        <p>Wow Divi Carousel Lite is a Divi touch-enabled plugin that lets you create a beautiful responsive slider or carousel. </p>
                                        <a href="https://wordpress.org/plugins/wow-carousel-for-divi-lite/" target="_blank" class="button button-primary">FREE DOWNLOAD</a>
                                    </div>
                                </div>
                            </div>
                            <div id="tab-debug" class="dipe-cf7-styler-form-page">
                                <div class="postbox dipe-postbox">
                                    <h3 class="hndle">Debug</h3>
                                    <div class="inside">
                                        <p>Please supply the debug data below with your support requests</p>
                                        <?php

                                            if( !class_exists( 'WP_Debug_Data' ) ) {
                                                $wp_debug_data_file = ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
                                                if ( !file_exists( $wp_debug_data_file ) ) {
                                                    _e( 'Your WordPress version does not yet support the WP_Debug_Data class, please update.', 'dvppl-cf7-styler' );
                                                    return false;
                                                }
                                                require_once $wp_debug_data_file;
                                            }

                                            wp_enqueue_style( 'site-health' );
                                            wp_enqueue_script( 'site-health' );

                                            $info = WP_Debug_Data::debug_data();
                                        ?>
                                        <div class="site-health-copy-buttons">
                                            <div class="copy-button-wrapper">
                                                <button type="button" class="button copy-button" data-clipboard-text="<?php echo esc_attr( WP_Debug_Data::format( $info, 'debug')); ?>"> <?php  _e( 'Copy site info to clipboard' ); ?>
                                                </button>
                                                <span class="success" aria-hidden="true"><?php _e( 'Copied!' ); ?></span>
                                            </div>
                                        </div>
                                        <div id="health-check-debug" class="health-check-accordion">
                                            <?php 
                                                $sizes_fields = array(
                                                    'uploads_size',
                                                    'themes_size',
                                                    'plugins_size',
                                                    'wordpress_size',
                                                    'database_size',
                                                    'total_size'
                                                );

                                                foreach ( $info as $section => $details ) {

                                                    if ( !isset( $details['fields'] ) || empty($details['fields']) ) {
                                                        continue;
                                                    } ?>

                                                    <h3 class="health-check-accordion-heading">
                                                        <button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-<?php echo  esc_attr( $section ) ;
                                                            ?>" type="button">
                                                            <span class="title">
                                                                <?php 
                                                                    echo esc_html( $details['label'] ) ;
                                                                    if ( isset( $details['show_count'] ) && $details['show_count'] ) {
                                                                        printf( '(%d)', count( $details['fields'] ) );
                                                                    }
                                                                ?>
                                                            </span>
                                                            <?php if ( 'wp-paths-sizes' === $section ) : ?>
                                                                <span class="health-check-wp-paths-sizes spinner"></span>
                                                            <?php endif; ?>
                                                            <span class="icon"></span>
                                                        </button>
                                                    </h3>

                                                    <div id="health-check-accordion-block-<?php echo  esc_attr( $section ); ?>" class="health-check-accordion-panel" hidden="hidden">

                                                        <?php 
                                                            if ( isset( $details['description'] ) && !empty($details['description']) ) {
                                                                printf( '<p>%s</p>', $details['description'] );
                                                            }
                                                        ?>

                                                        <table class="widefat striped health-check-table" role="presentation">
                                                            <tbody>
                                                                <?php 
                                                                    foreach ( $details['fields'] as $field_name => $field ) {
                                                                    
                                                                        if ( is_array( $field['value'] ) ) {
                                                                            $values = '<ul>';
                                                                            foreach ( $field['value'] as $name => $value ) {
                                                                                $values .= sprintf( '<li>%s: %s</li>', esc_html( $name ), esc_html( $value ) );
                                                                            }
                                                                            $values .= '</ul>';
                                                                        } else {
                                                                            $values = esc_html( $field['value'] );
                                                                        }
                                                                        
                                                                        if ( in_array( $field_name, $sizes_fields, true ) ) {
                                                                            printf(
                                                                                '<tr><td>%s</td><td class="%s">%s</td></tr>',
                                                                                esc_html( $field['label'] ),
                                                                                esc_attr( $field_name ),
                                                                                $values
                                                                            );
                                                                        } else {
                                                                            printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $field['label'] ), $values );
                                                                        }
                                                                    }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <?php 
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="postbox-container-1" class="postbox-container">
                            <div id="side-sortables" class="meta-box-sortables ui-sortable">
                                <div class="dipe-wdc-ads">
                                    <a href="https://wowcarousel.com/?utm_source=cf7_admin&utm_medium=cpc&utm_campaign=cf7" target="_blank">
                                        <img src="<?php echo DIPE_ASSETS_URL . 'imgs/wdc.svg'?>">
                                    </a>
                                    <p><strong>We're offering <span style="color: #562cd4; font-weight: bold;">50% OFF</span> all of our plans for a limited time! Just use the discount code <span style="color: #562cd4; font-weight: bold;">SAVEBIG</span> at checkout.</strong></p>
                                    <ul>
                                        <li>Lightbox</li>
                                        <li>Hover Image</li>
                                        <li>Google Reviews</li>
                                        <li>Woo Products</li>
                                        <li>Blog Post</li>
                                        <li>Content Carousel & 6 More…</li>
                                        <li>80+ Ready Blocks</li>
                                    </ul>
                                    <a href="https://bit.ly/2NGGoAk" target="_blank" class="button button-primary">Get Started</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function enqueue_scripts() {

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_enqueue_script( 'dipe-admin-js', DIPE_ASSETS_URL . 'js/admin.js', ['jquery'], '', true );
        wp_enqueue_style( 'dipe-admin', DIPE_ASSETS_URL . 'css/admin.css' );
    }

}

new Dipe_Admin;