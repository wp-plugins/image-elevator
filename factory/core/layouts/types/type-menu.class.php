<?php

class FactoryFR109TypeMenu {
    
    public $icon;
    
    /**
     * A custom post type that is configurated by this instance.
     * @var FactoryType 
     */
    public $type = null;
    
    public function __construct(FactoryFR109Type $type) {
        $this->type = $type;
    }
    
    public function insertAfter($position)
    {
        
    }
    
    public function insertBefore($position)
    {
        
    }
    
    public function insertAt($position)
    {
        
    }
    
    public function append()
    {
        
    }
    
    public function prepend()
    {
        
    }
}