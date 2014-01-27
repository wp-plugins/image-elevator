<?php
/**
 * Factory Font Awersome
 * 
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2013, OnePress Ltd
 * 
 * @package factory-bootstrap 
 * @since 1.0.0
 */

// module provides function only for the admin area
if ( !is_admin() ) return;

if (defined('FACTORY_FONTAWESOME_301_LOADED')) return;
define('FACTORY_FONTAWESOME_301_LOADED', true);

define('FACTORY_FONTAWESOME_301_DIR', dirname(__FILE__));
define('FACTORY_FONTAWESOME_301_URL', plugins_url(null,  __FILE__ ));

include_once(FACTORY_FONTAWESOME_301_DIR . '/functions.php');