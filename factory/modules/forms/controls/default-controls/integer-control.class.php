<?php

class FactoryFormFR109IntegerFormControl extends FactoryFormFR109TextboxFormControl 
{
    public $type = 'integer';
        
    public function getValue($name) {
        $value = intval( parent::getValue($name) );
        return $value != 0 ? $value : null;
    }
}