<?php
/**
Plugin Name: OnePress Image Elevator
Plugin URI: http://onepress-media.com/portfolio
Description: Save tons of time, when adding images into your posts! Paste images from clipboard directly into the post editor! Write articles, tutorials, reviews, news with pleasure by using Image Elevator!
Author: OnePress
Version: 1.8.8
Author URI: http://onepress-media.com/portfolio
*/



define('IMGEVR_PLUGIN_ROOT', dirname(__FILE__));
define('IMGEVR_PLUGIN_URL', plugins_url( null, __FILE__ ));

#comp merge
// the merge command allows to merge all files into one on compiling

require('libs/factory/core/boot.php');
require('libs/factory/bootstrap/boot.php');
require('libs/factory/notices/boot.php');
require('libs/factory/pages/boot.php');
require('libs/factory/forms/boot.php');
require('libs/others/mendeleev-controls/boot.php');
#endcomp


    #comp merge
    require('libs/onepress/licensing/boot.php');
    require('libs/onepress/updates/boot.php');
    #endcomp



global $clipImages;
$clipImages = new Factory300_Plugin(__FILE__, array(
    'name'      => 'clipboard-images',
    'title'     => 'Image Elevator',
    'version'   => '1.8.8',
    'assembly'  => 'free',
    'api'       => 'http://api.byonepress.com/1.1/',
    'premium'   => 'http://codecanyon.net/item/clipboard-images-by-onepress/4311188?ref=OnePress',
    'updates'   => IMGEVR_PLUGIN_ROOT . '/includes/updates/',
    'tracker'   => /*@var:tracker*/'0ec2f14c9e007ba464c230b3ddd98384'/*@*/,
));

// Loads rest of code that is created manually via the standard wordpress plugin api.
if ( is_admin() ) include( IMGEVR_PLUGIN_ROOT . '/admin/init.php' );

        