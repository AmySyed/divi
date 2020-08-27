<?php
add_action( 'admin_init', 'dipe_cf7_plugin_is_loaded' );

if ( ! function_exists( 'dipe_cf7_installed' ) ) 
{
	function dipe_cf7_installed() 
    {
		$file_path = 'contact-form-7/wp-contact-form-7.php';
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $file_path ] );
	}
}

function dipe_cf7_admin_notice() 
{

	$plugin = 'contact-form-7/wp-contact-form-7.php';

	if( dipe_cf7_installed() ){

		if( ! current_user_can( 'activate_plugins' ) ) return;

		$cf7_install_active_url = wp_nonce_url(
			'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 
			'activate-plugin_' . $plugin
		);

    	$message = __(
    		'Contact Form 7 Styler for Divi requires Coctact Form 7 plugin to be active. Please activate Contact Form 7 to continue.', 
    		'dvppl-cf7-styler'
    	);

		$button_text = __(
			'Activate Contact Form 7', 
			'dvppl-cf7-styler' 
		);
		$button = '<a href="' . $cf7_install_active_url . '" class="button-primary">' . $button_text . '</a>';

	} else {

		if (!current_user_can('install_plugins')) return;

		$cf7_install_active_url = wp_nonce_url( 
			self_admin_url( 'update.php?action=install-plugin&plugin=contact-form-7' ), 
			'install-plugin_contact-form-7'
		);

    	$message = __( 
    		'Contact Form 7 Styler for Divi plugin to be installed and activated. Please install Contact Form 7 to continue.', 
    		'dvppl-cf7-styler'
    	);

		$button_text = __(
			'Install Contact Form 7', 
			'dvppl-cf7-styler'
		);

		$button = '<a href="' . $cf7_install_active_url . '" class="button-primary install-now button">' . $button_text . '</a>';
	}

	printf('
		<div id="dvppl-cf7-install-notice" class="dvppl-cf7-install-notice notice is-dismissible">
			<p style="display: flex; align-items: center; padding:10px 10px 10px 0;">
				%1$s &nbsp; %2$s
			</p>
		</div>', 
		esc_html( $message ),
		$button
	);
}

function dipe_cf7_plugin_is_loaded() 
{

	if (!is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
		add_action( 'admin_notices', 'dipe_cf7_admin_notice' );
	}
}

function dipe_ajax_set_admin_notice_viewed() 
{

    if ( empty( $_REQUEST['notice_id'] ) ) {
        wp_die();
    }

    $notices = get_option( '_dipe_cf7_notice' );
    if ( empty( $notices ) ) {
        $notices = [];
    }

    $notices[ $_REQUEST['notice_id'] ] = 'true';

    update_option( '_dipe_cf7_notice', $notices );

    if ( ! wp_doing_ajax() ) {
        wp_safe_redirect( admin_url() );
        die;
    }

    wp_die();
}

function dipe_cf7_get_install_time() 
{
    $installed_time = get_option( '_dipe_cf7_installed_time' );

    if ( ! $installed_time ) {
        $installed_time = time();

        update_option( '_dipe_cf7_installed_time', $installed_time );
    }

    return $installed_time;
}

function dipe_notice_rate_us() 
{

    $notice_id = 'rate_us_feedback';

    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    if ( strtotime( '+24 hours', dipe_cf7_get_install_time() ) > time() ) {
        return false;
    }
    
    if ( dipe_is_user_notice_viewed( $notice_id ) ) {
        return false;
    }

    $screen = get_current_screen();
 
    if ( $screen->id !== 'dashboard' ) return;

    $dismiss_url = add_query_arg( [
        'action' => 'dipe_set_admin_notice_viewed',
        'notice_id' => esc_attr( $notice_id ),
    ], admin_url( 'admin-post.php' ) );

    ?>
    <div class="notice updated is-dismissible dipe-message dipe-message-dismissed" data-notice_id="<?php echo $notice_id; ?>">
        <div class="dipe-message-inner">
            <div class="dipe-message-icon">
                <div class="dipe-logo-wrapper">
                    <img src="<?php echo DIPE_CF7_URL . 'assets/imgs/icon-256x256.png'; ?>">
                </div>
            </div>
            <div class="dipe-message-content">
                <p><strong><?php echo __( 'Show Your Love: ', 'dvppl-cf7-styler' ); ?></strong> <?php _e( 'We love to have you in Divi People family. We are making it more awesome everyday. Take your 2 minutes to review the plugin and spread the love to encourage us to keep it going.', 'dvppl-cf7-styler' ); ?></p>
                <p class="dipe-message-actions">
                    <a href="https://wordpress.org/support/plugin/cf7-styler-for-divi/reviews/#new-post" target="_blank" class="button button-primary"><?php _e( 'Happy To Help', 'dvppl-cf7-styler' ); ?></a>
                    <a href="<?php echo esc_url_raw( $dismiss_url ); ?>" class="button dipe-button-notice-dismiss"><?php _e( 'Hide Notification', 'dvppl-cf7-styler' ); ?></a>
                </p>
            </div>
        </div>
    </div>
    <?php
        return true;
}

function dipe_install_wdcl() 
{

    $notice_id = 'install_wdcl';

    if( ! current_user_can( 'manage_options' ) ) {
        return false;
    }
    
    if( dipe_is_user_notice_viewed( $notice_id ) ) {
        return false;
    }

    ?>
    <div class="notice updated is-dismissible dipe-message dipe-message-dismissed" data-notice_id="<?php echo $notice_id; ?>">
        <div class="dipe-message-inner">
            <div class="dipe-message-icon">
                <div class="dipe-logo-wrapper">
                    <img src="https://ps.w.org/wow-carousel-for-divi-lite/assets/icon-256x256.png">
                </div>
            </div>
            <div class="dipe-message-content">
                <p>
                    <strong> <?php echo __( 'Wow Divi Carousel Lite: ', 'dvppl-cf7-styler' ); ?> </strong> 
                    <?php _e( "It's Divi touch-enabled carousel plugin that lets you create a beautiful responsive slider or carousel.", 'dvppl-cf7-styler' ); ?>
                </p>

                <p class="dipe-message-actions">
                    <a href="https://wordpress.org/plugins/wow-carousel-for-divi-lite/" target="_blank" class="button button-primary dipe-button-notice-wdcl">
                        <?php _e( 'FREE DOWNLOAD', 'dvppl-cf7-styler' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <?php
    return true;
}

function dipe_is_user_notice_viewed( $notice_id ) {
    $notices = get_option( '_dipe_cf7_notice' );
    if ( empty( $notices ) || empty( $notices[ $notice_id ] ) ) {
        return false;
    }
    return true;
}

add_action( 'admin_notices', 'dipe_notice_rate_us', 20 );
add_action( 'admin_notices', 'dipe_install_wdcl', 20 );
add_action( 'wp_ajax_dipe_set_admin_notice_viewed', 'dipe_ajax_set_admin_notice_viewed' );