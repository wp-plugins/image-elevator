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
if (defined('FACTORY_FORM_FR105_LOADED')) return;
define('FACTORY_FORM_FR105_LOADED', true);

// Absolute path and URL to the files and resources of the module.
define('FACTORY_FORM_FR105_DIR', dirname(__FILE__));
define('FACTORY_FORM_FR105_URL', plugins_url(null,  __FILE__ ));

// - Includes parts

include(FACTORY_FORM_FR105_DIR. '/form.class.php');
include(FACTORY_FORM_FR105_DIR. '/metabox-form.class.php');

// control base
include(FACTORY_FORM_FR105_DIR. '/controls/form-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/form-standart-control.class.php');

// default controls
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/textbox-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/url-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/integer-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/editor-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/hidden-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/list-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/default-controls/textarea-control.class.php');

// mendeleev controls
include(FACTORY_FORM_FR105_DIR. '/controls/mendeleev-controls/radio-control.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/mendeleev-controls/checkbox-control.class.php');

// service controls
include(FACTORY_FORM_FR105_DIR. '/controls/service-controls/form-item.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/service-controls/form-group.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/service-controls/form-tab-item.class.php');
include(FACTORY_FORM_FR105_DIR. '/controls/service-controls/form-tab.class.php');

// register form controls
FactoryForm::register('textbox', 'FactoryFormFR105TextboxFormControl');
FactoryForm::register('url', 'FactoryFormFR105UrlFormControl');
FactoryForm::register('textarea', 'FactoryFormFR105TextareaFormControl');
FactoryForm::register('list', 'factoryFormFR105ListFormControl');
FactoryForm::register('integer', 'FactoryFormFR105IntegerFormControl');
FactoryForm::register('hidden', 'FactoryFormFR105HiddenFormControl');
FactoryForm::register('editor', 'FactoryFormFR105EditorFormControl');

FactoryForm::register('mv-radio', 'FactoryFormFR105PiRadioFormControl');
FactoryForm::register('mv-checkbox', 'FactoryFormFR105CheckboxFormControl');