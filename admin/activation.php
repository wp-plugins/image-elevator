<?php

class ImageElevatorActivate extends OnpLicensing300_Activator {
    
    public function activate() {
        parent::activate();
        
        add_option('imgelv_clipboard_enable', true);
        add_option('imgelv_dragdrop_enable', true);
        
        add_option('imgelv_compression_max_size', 400);  
        add_option('imgelv_compression_quality', 80);   
    } 
}

$clipImages->registerActivation('ImageElevatorActivate');