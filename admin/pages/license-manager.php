<?php
#build: free, premium

/**
 * License page is a place where a user can check updated and manage the license.
 */
class ClipImagePluginManagerAdminPage extends OnePressFR110LicenseManagerAdminPage  {
 
    public $purchaseUrl = 'http://codecanyon.net/item/image-elevator-for-wordpress/4311188/?ref=OnePress';
    public $purchasePrice = '$18';
    
    public function configure() {
        $this->internal = true;
    }
}

$clipImages->registerPage('ClipImagePluginManagerAdminPage');