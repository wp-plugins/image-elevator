<?php

/**
 * Adds scripts and styles in the admin area.
 */
function imgevr_admin_assets() {
    wp_enqueue_script('clipboard-images', IMGEVR_PLUGIN_URL . '/assets/admin/js/image-elevator.global.js', array('jquery'));
    wp_enqueue_style('clipboard-images', IMGEVR_PLUGIN_URL . '/assets/admin/css/image-elevator.global.css');
    
    wp_enqueue_style('qtip', IMGEVR_PLUGIN_URL . '/assets/admin/css/jquery.qtip.min.css');
    wp_enqueue_script('qtip', IMGEVR_PLUGIN_URL . '/assets/admin/js/jquery.qtip.min.js', array('jquery'));
    ?>
    <script>
        window.imgevr_clipboard_active = true;
        window.imgevr_dragdrop_active = false;
    </script>
    <?php
    

    
    ?>
    <style>
        .onp-notice.image-elevator .onp-notice-inner-wrap {
            padding-left: 60px !important;
            background: url("<?php echo IMGEVR_PLUGIN_URL . '/assets/admin/img/notice-background.png' ?>") 2px 0px no-repeat;
        }
    </style>
    <?php
}
add_action('admin_enqueue_scripts', 'imgevr_admin_assets');

include(IMGEVR_PLUGIN_ROOT . '/admin/notices.php');
include(IMGEVR_PLUGIN_ROOT . '/admin/ajax/image-uploading.php');

function imgevr_add_plugin($plugin_array) {  
   $plugin_array['imgelevator'] = IMGEVR_PLUGIN_URL . '/assets/admin/js/image-elevator.tinymce.js';
   return $plugin_array;  
}  

function imgevr_mce_css( $mce_css ) {
    if ( ! empty( $mce_css ) ) $mce_css .= ',';
    $mce_css .= IMGEVR_PLUGIN_URL . "/assets/admin/css/editor.css";
    return $mce_css;
}

function imgevr_media_buttons() {
    $screen = get_current_screen();

    if ( $screen->parent_base !== 'edit' ) return;
        
    ?>
    <a class='button image-insert-controller' href='#'><span></span></a>
    <script>
        window.clipboardImagesAssets = '<?php echo IMGEVR_PLUGIN_URL . '/assets/admin' ?>';
    </script>
    <?php
}

add_filter('mce_css', 'imgevr_mce_css');
add_filter('mce_external_plugins', 'imgevr_add_plugin'); 
add_action( 'media_buttons', 'imgevr_media_buttons', 20 );