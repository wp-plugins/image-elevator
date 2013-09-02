<?php

abstract class FactoryFR109Update {
    
    /**
     * Current plugin
     * @var FactoryPlugin
     */
    var $plugin;
    
    public function __construct( FactoryFR109Plugin $plugin ){
        $this->plugin = $plugin;
    }
    
    abstract function install();
}

?>
