<?php
/**
Plugin Name: OnePress Image Elevator
Plugin URI: http://onepress-media.com/portfolio
Description: Save tons of time, when adding images into your posts! Paste images from clipboard directly into the post editor! Write articles, tutorials, reviews, news with pleasure by using Image Elevator!
Author: OnePress
Version: 1.7.8
Author URI: http://onepress-media.com/portfolio
*/



// Loads code created via Factory

require('factory/core/start.php');
$clipImages = factory_fr105_create_plugin(__FILE__, array(
    'name'      => 'clipboard-images',
    'title'     => 'Image Elevator',
    'version'   => '1.7.8',
    'assembly'  => 'free',
    'api'       => 'http://api.byonepress.com/1.0/',
    'premium'   => 'http://codecanyon.net/item/clipboard-images-by-onepress/4311188?ref=OnePress'
));

$clipImages->load('factory/modules/licensing', 'licensing');
$clipImages->load('factory/modules/updates', 'updates');
$clipImages->load('factory/modules/onepress', 'onepress');

// Loads rest of code that is created manually via the standard wordpress plugin api.

define('IMGEVR_PLUGIN_ROOT', dirname(__FILE__));
define('IMGEVR_PLUGIN_URL', plugins_url( null, __FILE__ ));

if ( is_admin() ) include( IMGEVR_PLUGIN_ROOT . '/admin/init.php' );

        