<?php
/**
 * Factory Forms
 * 
 * Factory Forms is an important part of the Factory that provides a declarative
 * way to build forms without any extra html or css markup.
 */

// Module provides function for the admin area only
if ( !is_admin() ) return;

// Checks if the one is already loaded.
// We prevent to load the same version of the module twice.
if (defined('FACTORY_FORM_FR109_LOADED')) return;
define('FACTORY_FORM_FR109_LOADED', true);

// Absolute path and URL to the files and resources of the module.
define('FACTORY_FORM_FR109_DIR', dirname(__FILE__));
define('FACTORY_FORM_FR109_URL', plugins_url(null,  __FILE__ ));

// - Includes parts

include(FACTORY_FORM_FR109_DIR. '/html-helpers.class.php');
include(FACTORY_FORM_FR109_DIR. '/form.class.php');
include(FACTORY_FORM_FR109_DIR. '/metabox-form.class.php');

// control base
include(FACTORY_FORM_FR109_DIR. '/controls/form-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/form-standart-control.class.php');

// default controls
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/textbox-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/url-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/integer-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/editor-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/hidden-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/list-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/default-controls/textarea-control.class.php');

// mendeleev controls
include(FACTORY_FORM_FR109_DIR. '/controls/mendeleev-controls/radio-control.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/mendeleev-controls/checkbox-control.class.php');

// service controls
include(FACTORY_FORM_FR109_DIR. '/controls/service-controls/form-item.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/service-controls/form-group.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/service-controls/form-tab-item.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/service-controls/form-tab.class.php');
include(FACTORY_FORM_FR109_DIR. '/controls/service-controls/form-collapsed.class.php');

// register form controls
FactoryFormFR109::register('textbox', 'FactoryFormFR109TextboxFormControl');
FactoryFormFR109::register('url', 'FactoryFormFR109UrlFormControl');
FactoryFormFR109::register('textarea', 'FactoryFormFR109TextareaFormControl');
FactoryFormFR109::register('list', 'factoryFormFR109ListFormControl');
FactoryFormFR109::register('integer', 'FactoryFormFR109IntegerFormControl');
FactoryFormFR109::register('hidden', 'FactoryFormFR109HiddenFormControl');
FactoryFormFR109::register('editor', 'FactoryFormFR109EditorFormControl');

FactoryFormFR109::register('mv-radio', 'FactoryFormFR109PiRadioFormControl');
FactoryFormFR109::register('mv-checkbox', 'FactoryFormFR109CheckboxFormControl');