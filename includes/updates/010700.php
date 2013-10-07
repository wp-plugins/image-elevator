<?php

/**
 * Adds new options.
 */
class ImgevrUpdate010700 extends FactoryFR109Update {

    public function install() {

        add_option('imgelv_clipboard_enable', true);
        add_option('imgelv_dragdrop_enable', true);
        
        add_option('imgelv_compression_max_size', 400);  
        add_option('imgelv_compression_quality', 80);   
    }
}