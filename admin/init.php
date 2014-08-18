<?php

include(IMGEVR_PLUGIN_ROOT . '/admin/activation.php');
    include_once(IMGEVR_PLUGIN_ROOT . '/admin/pages/how-to-use.php');


    include(IMGEVR_PLUGIN_ROOT . '/admin/pages/license-manager.php');



/**
 * Adds scripts and styles in the admin area.
 */
function imgevr_admin_assets() {
    wp_enqueue_script('clipboard-images', IMGEVR_PLUGIN_URL . '/assets/admin/js/image-elevator.global.js', array('jquery'));
    wp_enqueue_style('clipboard-images', IMGEVR_PLUGIN_URL . '/assets/admin/css/image-elevator.global.css');
    
    wp_enqueue_style('jquery-qtip-2', IMGEVR_PLUGIN_URL . '/assets/admin/css/jquery.qtip.min.css');
    wp_enqueue_script('jquery-qtip-2', IMGEVR_PLUGIN_URL . '/assets/admin/js/jquery.qtip.min.js', array('jquery'));
    ?>
    <script>
        window.imgevr_clipboard_active = true;
        window.imgevr_dragdrop_active = false;
    </script>
    <?php
    

    
    ?>
    <style>
        .notice-clipboard-images.factory-hero .factory-inner-wrap {
            padding-left: 60px !important;
            background: url("<?php echo IMGEVR_PLUGIN_URL . '/assets/admin/img/notice-background.png' ?>") 2px 0px no-repeat;
        }
    </style>
    <?php
}

add_action( 'admin_print_styles', 'imgevr_admin_assets' );

include(IMGEVR_PLUGIN_ROOT . '/admin/notices.php');
include(IMGEVR_PLUGIN_ROOT . '/admin/ajax/image-uploading.php');

function imgevr_add_plugin($plugin_array) {  
   $plugin_array['imgelevator'] = IMGEVR_PLUGIN_URL . '/assets/admin/js/image-elevator.tinymce.js';
   return $plugin_array;  
}  

function imgevr_mce_options( $options ) {
    $options['paste_data_images'] = false;
    $options['paste_preprocess'] = 'function(plugin, args) { args.content = window.clipboardContext.processPastedContent( args.content ); }';    
    return $options;
}

function imgevr_mce_css( $mce_css ) {
    if ( ! empty( $mce_css ) ) $mce_css .= ',';
    $mce_css .= IMGEVR_PLUGIN_URL . "/assets/admin/css/editor.css";
    return $mce_css;
}

function imgevr_media_buttons() {
    $screen = get_current_screen();

    if ( $screen->parent_base !== 'edit' ) return;
    global $clipImages;
    ?>
    <?php ?>
    <a class='button image-insert-controller' style="margin-right: 2px;" href='#'><span></span></a>
    <a class='button imgevr-get-premium' href='<?php echo admin_url('admin.php') . '?page=how-to-use-clipboard-images&onp_sl_page=premium' ?>'><span>Get Image Elevator Premium</span></a>
    <?php 
 ?>
    <script>
        window.clipboardImagesAssets = '<?php echo IMGEVR_PLUGIN_URL . '/assets/admin' ?>';
    </script>
    <?php
}

add_filter('mce_css', 'imgevr_mce_css');
add_filter('mce_external_plugins', 'imgevr_add_plugin'); 
add_action( 'media_buttons', 'imgevr_media_buttons', 20 );
add_filter( 'tiny_mce_before_init', 'imgevr_mce_options', 1, 50 );

/**
 * Returns an URL where we should redirect a user after success activation of the plugin.
 * 
 * @since 3.1.0
 * @return string
 */
function onp_imgevr_license_manager_success_button() {
    return 'Learn how to use the plugin <i class="fa fa-lightbulb-o"></i>';
}
add_action('onp_license_manager_success_button_clipboard-images', 'onp_imgevr_license_manager_success_button');

/**
 * Returns an URL where we should redirect a user after success activation of the plugin.
 * 
 * @since 3.1.0
 * @return string
 */
function onp_imgevr_license_manager_success_redirect() {
    global $sociallocker;
    
    $args = array(
        'fy_plugin' => 'clipboard-images',
        'fy_page' => 'how-to-use'
    );

    return admin_url( 'admin.php?' . http_build_query( $args ) );
}
add_action('onp_license_manager_success_redirect_clipboard-images',  'onp_imgevr_license_manager_success_redirect');