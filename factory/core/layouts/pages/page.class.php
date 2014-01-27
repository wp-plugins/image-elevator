<?php

class FactoryFR110Page {
    
    /**
     * Current Factory Plugin.
     * @var FactoryPlugin
     */
    public $plugin;
    
    /**
     * Page id used to call.
     * @var string 
     */
    public $id;
    
    public function __construct( FactoryFR110Plugin $plugin = null ) {
        $this->plugin = $plugin;
        $this->scripts = new FactoryFR110ScriptList( $plugin );
        $this->styles = new FactoryFR110StyleList( $plugin ); 
    }

    public function assets(FactoryFR110ScriptList $scripts, FactoryFR110StyleList $styles) {}
        
    /**
     * Shows page.
     */
    public function show() {
        
        if ( $this->result ) {
            echo $this->result;
        } else {
            $action = isset( $_GET['action'] ) ? $_GET['action'] : 'index';
            $this->executeByName( $action );  
        }
    }
    
    public function executeByName( $action ) {
        $actionFunction = $action . 'Action';

        $cancel = $this->OnActionExecuting($action);
        if ( $cancel === false ) return;
        
        call_user_func_array(array($this,$actionFunction), array());
        $this->OnActionExected($action);  
    }
    
    protected function OnActionExecuting( $action ) {}
    
    protected function OnActionExected( $action ) {}
    
    protected function script( $path ) {
        wp_enqueue_script( $path, $path, array('jquery'), false, true );
    }
    
    /**
     * Renders a template.
     * @param string $path
     * @param mixed $model
     */
    protected function template($path, $model, $bodyContent = null) {
        $layout = null;
        $file = $this->plugin->templateRoot . '/' . $path . '.tpl.php';
        
        ob_start();
        include($file);
        $content = ob_get_contents();
        ob_end_clean();
        
        if ( !empty($content) ) {
            $content = str_replace('{pagebody}', $bodyContent, $content);
        }
        
        if ( !empty($layout) ) {
            $this->template($layout, $model, $content);
        } else {
            echo $content;
        }
    }
}