<?php

add_action('admin_enqueue_scripts', 'factory_fontawesome_301_load_assets');   
function factory_fontawesome_301_load_assets() {
    wp_enqueue_style('factory-fontawesome-301', FACTORY_FONTAWESOME_301_URL . '/assets/css/font-awesome.css');
}