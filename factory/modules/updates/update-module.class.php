<?php

/**
 * Class is used to manage the updates data.
 */
class FactoryFR109UpdateFR109Module {
    
    public function __construct( $plugin ) {
        $plugin->updates = new FactoryFR109UpdateFR109Manager( $plugin );
    }
}

add_action('factory_fr109_load_updates', 'factory_update_fr109s_module_load');
function factory_update_fr109s_module_load( $plugin ) {
    new FactoryFR109UpdateFR109Module( $plugin ); 
}