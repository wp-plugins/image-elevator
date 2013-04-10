<?php

class FactoryFR105AdminPage extends FactoryFR105Page {
    
    /**
     * Visible page title.
     * For example: 'License Manager'
     * @var string 
     */
    public $pageTitle;
    
    /**
     * Visible title in menu.
     * For example: 'License Manager'
     * @var string 
     */
    public $menuTitle = null;
    
    /**
     * Menu icon (only if a page is placed as a main menu).
     * For example: '~/assets/img/menu-icon.png'
     * @var string 
     */
    public $menuIcon = null;
    
    /**
     * Menu position (only if a page is placed as a main menu).
     * @link http://codex.wordpress.org/Function_Reference/add_menu_page
     * @var string 
     */
    public $menuPosition = null;
    
    /**
     * Menu type. Set it to add the page to the specified type menu.
     * For example: 'post'
     * @var type 
     */
    public $menuPostType = null;
    
    /**
     * if specified the page will be added to the given menu target as a submenu.
     * For example: 'edit.php?post_type=custom-post-type'
     * @var string
     */
    public $menuTarget = null;
    
    /**
     * Capabilities for roles that have access to work with this page.
     * Leave it empty to use inherited capabilities for custom post type menu.
     * @link http://codex.wordpress.org/Roles_and_Capabilities
     * @var array An array of the capabilities.
     */
    public $capabilitiy = null;
    
    /**
     * If true, the page will not added to the admin menu.
     * @var type 
     */
    public $internal = false;
    

    public function __construct(FactoryFR105Plugin $plugin) {
        parent::__construct($plugin);
        $this->configure();
        
        $this->id = empty($this->id) ? str_replace('adminpage', '', strtolower( get_class($this) ) ) : $this->id;
    }
    
    /**
     * May be used to configure the page before uts usage.
     */
    public function configure(){}
    
    /**
     * Actions that includes registered fot this type scritps and styles.
     * @global type $post
     * @param type $hook
     */
    public function actionAdminScripts( $hook ) {

        $this->scripts->connect();
        $this->styles->connect(); 
    }
    
    public function getResultId() {
        return $this->id . '-' . $this->plugin->pluginName;
    }
    
    /**
     * Registers admin page for the admin menu.
     */
    public function register() {
        $resultId = $this->getResultId();
        
        if ( isset($_GET['page']) && $_GET['page'] == $resultId ) {
            $this->assets($this->scripts, $this->styles);
            
            // includes styles and scripts
            if ( !$this->scripts->isEmpty() || !$this->styles->isEmpty() ) {
                add_action('admin_enqueue_scripts', array($this, 'actionAdminScripts'));
            }
        }
        
        // if this page for a custom menu page
        if ( $this->menuPostType ) {
            $this->menuTarget = 'edit.php?post_type=' . $this->menuPostType;
            if ( empty( $this->capabilities ) ) {
                $this->capabilitiy = 'edit_' . $this->menuPostType;
            }
        } 

        // sets default capabilities
        if ( empty( $this->capabilities ) ) {
            $this->capabilitiy = 'manage_options';
        }

        $this->pageTitle = !$this->pageTitle ? $this->menuTitle : $this->pageTitle;
        $this->menuTitle = !$this->menuTitle ? $this->pageTitle : $this->menuTitle;

        // submenu
        if ( $this->menuTarget ) {

            add_submenu_page( 
                $this->menuTarget, 
                $this->pageTitle, 
                $this->menuTitle, 
                $this->capabilitiy, 
                $resultId, 
                array($this, 'show') );

        // global menu
        } else {

            add_menu_page( 
                $this->pageTitle, 
                $this->menuTitle, 
                $this->capabilitiy, 
                $resultId, 
                array($this, 'show'), 
                null,
                $this->menuPosition );   
 
            add_action( 'admin_head', array($this, 'actionAdminHead'));  
        }
        
        // makes redirect to the page

        $controller = isset( $_GET['fy_page'] ) ? $_GET['fy_page'] : null;
        if ( !$controller || $controller !== $this->id ) return;

        $plugin = isset( $_GET['fy_plugin'] ) ? $_GET['fy_plugin'] : null; 
        if ( $this->plugin->pluginName !== $plugin ) return;
        
        $action = isset( $_GET['fy_action'] ) ? $_GET['fy_action'] : 'index';
        if ( !$controller || $controller !== $this->id ) return;
        
        $this->redirectToAction($action);
    }
    
    protected function redirectToAction($action, $queryArgs = array()) {
        wp_redirect( $this->getActionUrl($action, $queryArgs) );     
        exit;
    }
    
    protected function actionUrl($action = null, $queryArgs = array()) {
        echo $this->getActionUrl($action, $queryArgs); 
    }
    
    protected function getActionUrl($action = null, $queryArgs = array()) {
        $baseUrl = $this->getBaseUrl();
        
        if ( !empty( $action )) $queryArgs['action'] = $action;
        return add_query_arg($queryArgs, $baseUrl);    
    }
    
    protected function getBaseUrl() {
        $resultId = $this->getResultId();
                
        if ( $this->menuTarget ) {
            return $this->menuTarget . '&page=' . $resultId;     
        } else {
            return 'admin.php?&page=' . $resultId;     
        } 
    }
    
    public function actionAdminHead() 
    {     
        $resultId = $this->getResultId();
        
        if (!empty($this->menuIcon)) {
            $iconUrl = str_replace('~/', $this->plugin->pluginUrl . '/', $this->menuIcon);   
            
            ?>
            <style type="text/css" media="screen">
                a.toplevel_page_<?php echo $resultId ?> .wp-menu-image {
                    background: url('<?php echo $iconUrl ?>') no-repeat 6px -33px !important;
                }
                a.toplevel_page_<?php echo $resultId ?>:hover .wp-menu-image, 
                a.toplevel_page_<?php echo $resultId ?>.current .wp-menu-image {
                    background-position:6px -1px !important;
                }
            </style>
            <?php
        }
        
        if ($this->internal) {
            ?>
            <style type="text/css" media="screen">
                li.toplevel_page_<?php echo $resultId ?> {
                    display: none;
                }
            </style>
            <?php
        }
    }
}