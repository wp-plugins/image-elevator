<?php
#build: free, premium

/**
 * License page is a place where a user can check updated and manage the license.
 */
class ClipImagePluginManagerAdminPage extends OnePressFR105LicenseManagerAdminPage  {
 
    public $purchasePrice = '$18';
    
    public function configure() {
        $this->internal = true;
    }
}