<?php

class FactoryFormFR109UrlFormControl extends FactoryFormFR109TextboxFormControl 
{
    public $type = 'url';
        
    public function getValue($name) {
        $value = parent::getValue($name);
        if ( empty($value) ) return null;
        
        if ( substr($value, 0, 4) != 'http' ) $value = 'http://' . $value;
        return $value;
    } 
}