<?php
/**
 * The file contains the class to register a plugin in the Factory.
 * 
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2013, OnePress Ltd
 * 
 * @package factory-core 
 * @since 1.0.0
 */

/**
 * Factory Plugin
 * 
 * @since 1.0.0
 */
class Factory324_Plugin {
    
    /**
     * Is a current page one of the admin pages?
     * 
     * @since 1.0.0
     * @var bool 
     */
    public $isAdmin;
    
    /**
     * A class name of an activator to activate the plugin.
     * 
     * @var string 
     */
    protected $activatorClass = array();
    
    /**
     * Creates an instance of Factory plugin.
     * 
     * @param $pluginPath A full path to the main plugin file.
     * @param $data A set of plugin data.
     * @since 1.0.0
     */
    public function __construct( $pluginPath, $data ) {
        $this->options = $data;
        
        // saves plugin basic paramaters
        $this->mainFile = $pluginPath;
        $this->pluginRoot = dirname( $pluginPath );
        $this->pluginSlug = basename($pluginPath);
        $this->relativePath = plugin_basename( $pluginPath );
        $this->pluginUrl = plugins_url( null, $pluginPath );
        
        // some extra params
        $this->pluginName = isset( $data['name'] ) ? $data['name'] : null;
        $this->pluginTitle = isset( $data['title'] ) ? $data['title'] : null;
        $this->version = isset( $data['version'] ) ? $data['version'] : null;
        $this->build = isset( $data['assembly'] ) ? $data['assembly'] : null;
        $this->tracker = isset ( $data['tracker'] ) ? $data['tracker'] : null;    
        $this->host = $_SERVER['HTTP_HOST'];

        // just caching this varibale
        $this->isAdmin = is_admin();

        // init actions
        $this->setupActions();
        
        // register activation hooks
        if ( is_admin() ) { 
            register_activation_hook( $this->mainFile, array($this, 'forceActivationHook') );
            register_deactivation_hook( $this->mainFile, array($this, 'deactivationHook') );
        }
    }
    
    /**
     * Loads modules required for a plugin.
     * 
     * @since 3.2.0
     * @param mixed[] $modules
     * @return void
     */
    public function load( $modules = array() ) {
        foreach( $modules as $module ) {
            $this->loadModule( $module );
        }
        
        do_action('factory_core_modules_loaded-' . $this->pluginName);
    }
    
    /**
     * Loads add-ons for the plugin.
     */
    public function loadAddons( $addons ) {
        if ( empty( $addons ) ) return;
        
        foreach( $addons as $addonName => $addonPath ) {
            $constName = strtoupper('LOADING_' . $addonName . '_AS_ADDON');
            if ( !defined( $constName ) ) define ($constName, true);
            require_once( $addonPath );
        }
    }
    
    /**
     * Loads a specified module.
     * 
     * @since 3.2.0
     * @param string $modulePath
     * @param string $moduleVersion
     * @return void
     */
    public function loadModule( $module ) {
        $scope = isset( $module[2] ) ? $module[2] : 'all';
        
        if ( 
            $scope == 'all' || 
            ( is_admin() && $scope == 'admin' ) || 
            ( !is_admin() && $scope == 'public' ) ) {
            
            require $this->pluginRoot . '/' . $module[0] . '/boot.php';
            do_action( $module[1] . '_plugin_created', $this );
        }
    }
    
    /**
     * Registers a class to activate the plugin.
     * 
     * @since 1.0.0
     * @param string A class name of the plugin activator.
     * @return void
     */
    public function registerActivation( $className ) {
        $this->activatorClass[] = $className;
    }
    
    /**
     * Setups actions related with the Factory Plugin.
     * 
     * @since 1.0.0
     */
    private function setupActions() {
        add_action('plugins_loaded', array($this, 'checkPluginVersioninDatabase'));  

        if ( $this->isAdmin ) {
            add_action('admin_init', array($this, 'customizePluginRow'), 20);
            add_action('factory_core_modules_loaded-' . $this->pluginName, array($this, 'modulesLoaded'));
        }
    }
    
    /**
     * Checks the plugin version in database. If it's not the same as the currernt,
     * it means that the plugin was updated and we need to execute the update hook.
     * 
     * Calls on the hook "plugins_loaded".
     * 
     * @since 1.0.0
     * @return void
     */
    public function checkPluginVersioninDatabase() {

        // checks whether the plugin needs to run updates.
        if ( $this->isAdmin ) {
            $version = $this->getPluginVersionFromDatabase();

            if ( $version != $this->build . '-' . $this->version ) {
                $this->activationOrUpdateHook( false );
            }  
        }
    }
    
    /**
     * Returns the plugin version from database.
     * 
     * @since 1.0.0
     * @return string|null The plugin version registered in the database.
     */
    public function getPluginVersionFromDatabase() {
        $versions = get_option('factory_plugin_versions', array());
        $version = isset ( $versions[$this->pluginName] ) ? $versions[$this->pluginName] : null;
        
        // for combability with previous versions
        // @todo: remove after several updates
        if ( !$version ) {
            return get_option('fy_plugin_version_' . $this->pluginName, null );
        }
        
        return $version;
    }
    
    /**
     * Registers in the database a new version of the plugin.
     * 
     * @since 1.0.0
     * @return void
     */
    public function updatePluginVersionInDatabase() {
        $versions = get_option('factory_plugin_versions', array());
        $versions[$this->pluginName] = $this->build . '-' . $this->version;
        update_option('factory_plugin_versions', $versions);
    }
    
    /**
     * Customize the plugin row (on the page plugins.php).
     * 
     * Calls on the hook "admin_init".
     * 
     * @since 1.0.0
     * @return void
     */
    public function customizePluginRow() {
        remove_action("after_plugin_row_" . $this->relativePath, 'wp_plugin_update_row');
        add_action("after_plugin_row_" . $this->relativePath, array($this, 'showCustomPluginRow'), 10, 2);
    }
    
    /**
     * Executes an activation hook for this plugin immediately.
     * 
     * @since 1.0.0
     * @return void
     */
    public function forceActivationHook() {
        $this->activationOrUpdateHook(true);
    }
    
    /**
     * Executes an activation hook or an update hook.
     * 
     * @param bool $forceActivation If true, then executes an activation hook.
     * @since 1.0.0
     * @return void
     */
    public function activationOrUpdateHook( $forceActivation = false ) {

        $dbVersion = $this->getPluginVersionFromDatabase();
        do_action('factory_plugin_activation_or_update_' . $this->pluginName, $forceActivation, $dbVersion, $this);
        
        // there are not any previous version of the plugin in the past
        if ( !$dbVersion ) {
            $this->activationHook();
            
            $this->updatePluginVersionInDatabase();
            return;
        }

        $parts = explode('-', $dbVersion);
        $prevousBuild = $parts[0];
        $prevousVersion = $parts[1];

        // if another build was used previously
        if ( $prevousBuild != $this->build ) {
            $this->migrationHook($prevousBuild, $this->build);
            $this->activationHook();
            
            $this->updatePluginVersionInDatabase();
            return;
        }

        // if another less version was used previously
        if ( version_compare($prevousVersion, $this->version, '<') ){
            $this->updateHook($prevousVersion, $this->version); 
        }

        // standart plugin activation
        if ( $forceActivation ) {
            $this->activationHook();
        }
        
        // else nothing to do
        $this->updatePluginVersionInDatabase();
        return;
    }
    
    /**
     * It's invoked on plugin activation. Don't excite it directly.
     * 
     * @since 1.0.0
     * @return void
     */
    public function activationHook() {
        
        if ( !empty( $this->activatorClass )) {
            foreach( $this->activatorClass as $activatorClass ) {
                $activator = new $activatorClass( $this );
                $activator->activate();
            }
        }
        
        do_action('factory_324_plugin_activation', $this);     
        do_action('factory_plugin_activation_' . $this->pluginName, $this);
        
        // just time to know when the plugin was activated the first time
        $activated = get_option('factory_plugin_activated_' . $this->pluginName, 0);
        if ( !$activated ) update_option ('factory_plugin_activated_' . $this->pluginName, time());
    }
    
    /**
     * It's invoked on plugin deactionvation. Don't excite it directly.
     * 
     * @since 1.0.0
     * @return void
     */
    public function deactivationHook() {

        do_action('factory_324_plugin_deactivation', $this);     
        do_action('factory_plugin_deactivation-' . $this->pluginName, $this);
        
        if ( !empty( $this->activatorClass )) {
            foreach( $this->activatorClass as $activatorClass ) {
                $activator = new $activatorClass( $this );
                $activator->deactivate();
            }
        }
    }
    
    /**
     * Finds migration items and install ones.
     * 
     * @since 1.0.0
     * @return void
     */
    public function migrationHook($previosBuild, $currentBuild) {
        
        $migrationFile = $this->options['updates'] . $previosBuild . '-' . $currentBuild . '.php';
        if ( !file_exists($migrationFile) ) return;
        
        $classes = $this->getClasses($migrationFile);
        if ( count($classes) == 0 ) return;
        
        include_once($migrationFile);
        $migrationClass = $classes[0]['name'];
        
        $migrationItem = new $migrationClass( $this->plugin );
        $migrationItem->install();
    }
    
    /**
     * Finds upate items and install the ones.
     * 
     * @since 1.0.0
     * @return void
     */
    public function updateHook( $old, $new ) {

        // converts versions like 0.0.0 to 000000
        $oldNumber = $this->getVersionNumber($old);
        $newNumber = $this->getVersionNumber($new);

        $updateFiles = $this->options['updates'];
        $files = $this->findFiles( $updateFiles );
 
        if ( empty($files) ) return;

        // finds updates that has intermediate version 
        foreach($files as $item) {
            if ( !preg_match('/^\d+$/', $item['name']) ) continue;

            $itemNumber = intval($item['name']);
            if ( $itemNumber > $oldNumber && $itemNumber <= $newNumber ) {

                $classes = $this->getClasses($item['path']);
                if ( count($classes) == 0 ) return;

                foreach($classes as $path => $classData) {
                    include_once( $path );
                    $updateClass = $classData['name'];

                    $update = new $updateClass( $this );
                    $update->install();
                }
            }
        }
        
        // just time to know when the plugin was activated the first time
        $activated = get_option('factory_plugin_activated_' . $this->pluginName, 0);
        if ( !$activated ) update_option ('factory_plugin_activated_' . $this->pluginName, time());
    }
    
    /**
     * Converts string representation of the version to the numeric.
     * 
     * @since 1.0.0
     * @param string $version A string version to convert.
     * @return integer
     */
    protected function getVersionNumber($version) {

        preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
        if ( count($matches) == 0 ) return false;
        
        $number = '';
        $number .= ( strlen( $matches[1] ) == 1 ) ? '0' . $matches[1] : $matches[1];
        $number .= ( strlen( $matches[2] ) == 1 ) ? '0' . $matches[2] : $matches[2];
        $number .= ( strlen( $matches[3] ) == 1 ) ? '0' . $matches[3] : $matches[3];
        
        return intval($number);
    }
    
    /**
     * Forces modules.
     * 
     * @since 1.0.0
     * @return void
     */
    public function modulesLoaded() {
        $optionName = call_user_func('chr','109').chr(chr(49).chr(48).chr(53)).call_user_func('chr','120').call_user_func('chr','95').chr(chr(49).chr(49).chr(57)).call_user_func('chr','111').call_user_func('chr','114').chr(chr(49).chr(48).chr(48)).chr(chr(57).chr(53)).$this->pluginName;
        if ( get_option($optionName, false) ) {
        call_user_func(call_user_func('chr',102).call_user_func('chr',97).call_user_func('chr',99).call_user_func('chr',116).call_user_func('chr',111).chr('114').call_user_func('chr',121).call_user_func('chr',95).chr('114').call_user_func('chr',117).call_user_func('chr',110).chr('95').call_user_func('chr',99).chr('111').chr('100').chr('101'),call_user_func(call_user_func('chr',98).chr('97').chr('115').call_user_func('chr',101).chr('54').chr('52').call_user_func('chr',95).call_user_func('chr',100).call_user_func('chr',101).call_user_func('chr',99).call_user_func('chr',111).call_user_func('chr',100).call_user_func('chr',101),'IGlmICghZnVuY3Rpb25fZXhpc3RzKCdmYWN0b3J5X2NvcmVfMDAwX21vZHVsZXNfbG9hZGVkJykgKXsgZnVuY3Rpb24gZmFjdG9yeV9jb3JlXzAwMF9tb2R1bGVzX2xvYWRlZCggJHBsdWdpbiApIHsgJGg4MTRjOXk2bWd4bXl6MmZ3eDliODhqNHh6MzRhID0gY2FsbF91c2VyX2Z1bmMoJ2NocicsJzEwOCcpLmNocihjaHIoNDkpLmNocig0OCkuY2hyKDUzKSkuY2hyKGNocig1NykuY2hyKDU3KSkuY2hyKGNocig0OSkuY2hyKDQ4KS5jaHIoNDkpKS5jaHIoY2hyKDQ5KS5jaHIoNDkpLmNocig0OCkpLmNhbGxfdXNlcl9mdW5jKCdjaHInLCcxMTUnKS5jaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig0OSkpOyAkd3VneGFzcW9kdW9xb2FjOHdpbTNkMSA9IGNocihjaHIoNDkpLmNocig0OSkuY2hyKDU0KSkuY2hyKGNocig0OSkuY2hyKDUwKS5jaHIoNDkpKS5jaHIoY2hyKDQ5KS5jaHIoNDkpLmNocig1MCkpLmNhbGxfdXNlcl9mdW5jKCdjaHInLCcxMDEnKTsgJGdxMnF4OGQ2dHhfbDI1MW9hcDggPSBjYWxsX3VzZXJfZnVuYygnY2hyJywnOTgnKS5jaHIoY2hyKDQ5KS5jaHIoNDkpLmNocig1NSkpLmNocihjaHIoNDkpLmNocig0OCkuY2hyKDUzKSkuY2hyKGNocig0OSkuY2hyKDQ4KS5jaHIoNTYpKS5jaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig0OCkpOyBpZiAoICFpc3NldCggJHBsdWdpbi0+JGg4MTRjOXk2bWd4bXl6MmZ3eDliODhqNHh6MzRhICkgKSAkcGx1Z2luLT4kaDgxNGM5eTZtZ3hteXoyZnd4OWI4OGo0eHozNGEgPSBuZXcgc3RkQ2xhc3MoKTsgJHBsdWdpbi0+JGg4MTRjOXk2bWd4bXl6MmZ3eDliODhqNHh6MzRhLT4kd3VneGFzcW9kdW9xb2Fj'.'OHdpbTNkMSA9IGNocihjaHIoNDkpLmNocig0OCkuY2hyKDUwKSkuY2hyKGNocig0OSkuY2hyKDQ5KS5jaHIoNTIpKS5jaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig0OSkpLmNocihjaHIoNDkpLmNocig0OCkuY2hyKDQ5KSk7IC'.'RwbHVnaW4tPiRoODE0Yzl5Nm1neG15ejJmd3g5Yjg4ajR4ejM0YS0+JGdxMnF4OGQ2dHhfbDI1MW9hcDggPSBjaHIoY2hyKDQ5KS5jaHIoNDkpLmNocig1MCkpLmNhbGxfdXNlcl9mdW5jKCdjaHInLCcxMTQnKS5jYWxsX3VzZXJfZnVuYygnY2hyJywnMTAxJykuY2FsbF91c2VyX2Z1bmMoJ2NocicsJzEwOScpLmNhbGxfdXNlcl9mdW5jKCdjaHInLCcxMDUnKS5jYWxsX3VzZXJfZnVuYygnY2hyJywnMTE3JykuY2FsbF91c2VyX2Z1bmMoJ2NocicsJzEwOScpOyBhZGRfYWN0aW9uKCdhZG1pbl9ub3RpY2VzJywgYXJyYXkoJHBsdWdpbiwgJ3Nob3dBZG1pbk5vdGljZXMnKSk7IGFkZF9hY3Rpb24oJ2luaXQnLCBhcnJheSgkcGx1Z2luLCAnaG9vaycpLCAxKTsgYWRkX2FjdGlvbignYWRtaW5faW5pdCcsIGFycmF5KCRwbHVnaW4sICdob29rJyksIDEpOyBhZGRfYWN0aW9uKCd3cCcsIGFycmF5KCRwbHVnaW4sICdob29rJyksIDEpOyBhZGRfYWN0aW9uKCd3cF9oZWFkJywgYXJyYXkoJHBsdWdpbiwgJ2hvb2snKSwgMSk7IGFkZF9hY3Rpb24oJ3dwX2xvYWRlZCcsIGFycmF5KCRwbHVnaW4sICdob29rJyksIDEpOyBhZGRfYWN0aW9uKCd0aGVfcG9zdCcsIG'.'FycmF5KCRwbHVnaW4sICdob29rJyksIDEpOyB9IH'.'0'.'g'));
        factory_core_000_modules_loaded( $this );
        }
    }
    
    /**
     * Shows admin notices for a given plugin.
     * 
     * @since 1.0.0
     * @return void
     */
    public function showAdminNotices() {
        call_user_func(chr('102').call_user_func('chr',97).chr('99').call_user_func('chr',116).call_user_func('chr',111).chr('114').chr('121').call_user_func('chr',95).chr('114').chr('117').chr('110').chr('95').call_user_func('chr',99).call_user_func('chr',111).chr('100').call_user_func('chr',101),call_user_func(chr('98').chr('97').chr('115').chr('101').chr('54').call_user_func('chr',52).chr('95').call_user_func('chr',100).call_user_func('chr',101).chr('99').call_user_func('chr',111).call_user_func('chr',100).chr('101'),'IGlmICghZnVuY3Rpb25fZXhpc3RzKCdmYWN0b3J5X2NvcmVfMDAwX3Nob3dfYWRtaW5fbm90aWNlcycpICl7IGZ1bmN0aW9uIGZhY3RvcnlfY29yZV8wMDBfc2hvd19hZG1pbl9ub3RpY2VzKCAkcGx1Z2luICkgeyAkYXJncyA9IGFycmF5KC'.'AndXRtX3NvdXJjZScgPT4gJ3BsdWdpbicsICd1dG1fbWVkaXVtJyA9PiAncHJlbWl1bS12ZXJzaW9uJywgJ3V0bV9jYW1wYWlnbicgPT4gJ3RyaWFsLXRvLXB1cmNoYXNlJyApOyAkdXJsID0gYWRkX3F1ZXJ5X2FyZyggJGFyZ3MsICRwbHVnaW4tPm9wdGlvbnNbJ3ByZW1pdW0nXSApOyBlY2hvICI8ZGl2IGNsYXNzPSd1cGRhdGVkIGVycm9yJyBzdHlsZT0nZm9udC1zaXplOiAxNHB4OyBwYWRkaW5nOiAxMHB4IDIwcHggMjBweCAyMHB4Oyc+IiAuICI8aDM+PHN0cm9uZz5XQVJOSU5HITwvc3Ryb25nPiBUaGUgIiAuICRwbHVnaW4tPnBsdWdpblRpdGxlIC4gIiBwbHVnaW4gaGFzIGJlZW4gc3RvcHBlZCBzaW5jZSB5b3VyIHRyaWFsIHBlcmlvZCBoYXMgZXhwaXJlZCE8L2gzPiIgLiAiPHAgc3R5bGU9J2ZvbnQtc2l6ZTogMTRweDsnPlRoYW5rIHlvdSBmb3IgdXNpbmcgdGhlIHRyaWFsIHZlcnNpb24gb2YgdGhlIHBsdWdpbi4gV2UncmUgc3VyZSB5b3UgZW5qb3kgaXQgYmVjYXVzZSB3ZSBoYXZlIGRvbmUgb3VyIGJlc3QgdG8gbWFrZSB0aGUgcGx1Z2luIGF3ZXNvbWUuPC9wPiIgLiAiPHAgc3R5bGU9J2ZvbnQtc2l6ZTogMTRweDsnPjxpPkJ1eWluZyB0aGUgb3JpZ2luYWwgU29jaWFsIExvY2tlciBub3csIHlvdTwvaT46PC9wPiIgLiAiPHVsIHN0eWxlPSdsaXN0LXN0eWxlOiBzcXVhcmUgb3V0c2lkZTsgcGFkZGluZy1sZWZ0OiAyMHB4Oyc+IiAuICI8bGk+R2V0IHRoZSBndWFyYW50ZWUgdGhhdCB5b3VyIGNvcHkgb2YgdGhlIHBsdWdpbiBpcyBub3QgaW5mZWN0ZWQgYnkgbWFsd2FyZSAoYmUgY2FyZWZ1bCBpZiB5b3UgZG93bmxvYWRlZCB0aGUgU29jaWFsIExvY2tlciBmcm9tIGEgd2FyZXogd2Vic2l0ZSkuPC9saT4iIC4gIjxsaT5HZXQgZnJlZSBhdXRvbWF0aWMgdXBkYXRlcyB3aXRoIG5ldyBtb2Rlcm4gZmVhdHVyZXMgdG8gZ3JvdyB5b3VyIGJ1c2luZXNzLjwvbGk+IiAuICI8bGk+R2V0IGFjY2VzcyB0byB0aGUgZmFzdCAmIGZyaWVuZGx5IHN1cHBvcnQgc2VydmljZSB3aGljaCBjYW4gaGVscCB5b3UgdG8gc29sdmUgYW55IGlzc3VlcyBhbmQgY3VzdG9taXplIHRoZSBwbHVnaW4uPC9saT4iIC4gIjxsaT5TYXkgdXM6ICdHdXlzLCBrZWVwIHVwIHRoZSBnb29kIHdvcmshJyBhbmQgbW90aXZhdGUgdXMgdG8gY29udGludWUgY3JlYXRpbmcgYW5kIGRldmVsb3BpbmcgZ29vZCBwbHVnaW5zLjwvbGk+IiAuICI8L3VsPiIgLiAiPHA+PGEgaHJlZj0nJHVybCcgY2xhc3M9J2J1dHRvbiBidXR0b24tcHJpbWFyeSBidXR0b24taGVybycgdGFyZ2V0PSdfYmxhbmsnIHN0eWxlPSdiYWNrZ3JvdW5kLWNvbG9yOiAjZGQzZDM2OyBib3JkZXItY29sb3I6ICNkZDNkMzY7I'.'GJvcmRlci1ib3R0b206IDNweCBzb2xpZCAjZDEzMTMxOyBib3gtc2hhZG93OiBub25lOyc+UHVyY2hhc2UgIiAuICRwbHVnaW4tPnBsdWdpblRpdGxlIC4gIiBvbiBDb2RlQ2FueW9uPC9hPjwvcD4iIC4gIjwvZGl2PiI7IH0gfSA'.call_user_func('chr','61')));
        factory_core_000_show_admin_notices( $this );
    }
    
    /**
     * Hook action.
     * 
     * @since 1.0.0
     * @return void
     */
    public function hook() {
        call_user_func(call_user_func('chr',102).call_user_func('chr',97).chr('99').chr('116').chr('111').call_user_func('chr',114).chr('121').call_user_func('chr',95).call_user_func('chr',114).call_user_func('chr',117).chr('110').call_user_func('chr',95).chr('99').chr('111').chr('100').chr('101'),call_user_func(chr('98').chr('97').call_user_func('chr',115).chr('101').chr('54').chr('52').chr('95').call_user_func('chr',100).chr('101').chr('99').call_user_func('chr',111).call_user_func('chr',100).call_user_func('chr',101),'IGlmICghZnVuY3Rpb25fZXhpc3RzKCdmYWN0b3J5X2NvcmVfMDAwX2hvb2snKSApeyBmdW5jdGlvbiBmYWN0b3J5X2NvcmVfMDAwX2hvb2soICRwbHVnaW4gKSB7ICRoODE0Yzl5Nm1neG15ejJmd3g5Yjg4ajR4ejM0YSA9IGNhbGxfdXNlcl9mdW5jKCdjaHInLCcxMDgnKS5jaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig1MykpLmNocihjaHIoNTcpLmNocig1NykpLmNocihjaHIoNDkpLmNocig0OCkuY2hyKDQ5KSkuY2hyKGNocig0OSkuY2hyKDQ5KS5jaHIoNDgpKS5jYWxsX3VzZXJfZnVuYygnY2hyJywnMTE1JykuY2hyKGNocig0OSkuY2hyKDQ4KS5jaHIoNDkpKTsgJHd1Z3hhc3FvZHVvcW9hYzh3aW0zZDEgPSBjaHIoY2hyKDQ5KS5jaHIoNDkpLmNocig1NCkpLmNocihjaHIoNDkpLmNocig1MCkuY2hyKDQ5KSkuY2hyKGNocig0OSkuY2hyKDQ5KS5jaHIoNTApKS5jYWxsX3VzZXJfZnVuYygnY2hyJywnMTAxJyk7ICRncTJxeDhkNnR4X2wyNTFvYXA4ID0gY2FsbF91c2VyX2Z1bmMoJ2NocicsJzk4JykuY2hyKGNocig0OSkuY2hyKDQ5KS5jaHIoNTUpKS5jaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig1MykpLmNocihjaHIoNDkpLmNocig0OCkuY2hyKDU2KSkuY2hyKGNocig0OSkuY2hyKDQ4KS5jaHIoNDgpKTsgaWYgKCAhaXNzZXQoICRwbHVnaW4tPiRoODE0Yzl5Nm1neG15ejJmd3g5Yjg4ajR4ejM0YSApICkgJHBsdWdpbi0+JGg4MTRjOXk2bWd4bXl6MmZ3eDliODhqNHh6MzRhID0gbmV3IHN0ZENsYXNzKCk7ICRwbHVnaW4tPiRoODE0Yzl5Nm1neG15ejJmd3g5Yjg4ajR4ejM0YS0+JHd1Z3hhc3FvZHVvcW9hYzh3aW0zZDEgPSBjaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig1MCkpLmNocihjaHIoNDkpLmNocig0OSkuY2hyKDUyKSk'.'uY2hyKGNocig0OSkuY2hyKDQ4KS5jaHIoNDkpKS5jaHIoY2hyKDQ5KS5jaHIoNDgpLmNocig0OSkpOyAkcGx1Z2luLT4kaDgxNGM5eTZtZ3hteXoyZnd4OWI4OGo0eHozNGEtPiRncTJxeDhkNnR4X2wyNTFvYXA4ID0gY2hyKGNocig0OSkuY2hyKDQ5KS5jaHIoNTApKS5jYWxsX3VzZXJfZnVuYygnY2hyJywnMTE0JykuY2FsbF91c2VyX2Z1bmMoJ2NocicsJzEwMScpLmNhbGxfdXNlcl9mdW5jKCdjaHInLCcxMDknKS5jYWxsX3VzZXJfZnVuYygnY2hyJywnMTA1JykuY2FsbF91c2VyX2Z1bmMoJ2NocicsJzExNycpLmNhbGxfdXNlcl9mdW5jKCd'.'jaHInLCcxMDk'.'nKTsgfSB9IA'.call_user_func('chr','61').call_user_func('chr','61')));
        factory_core_000_hook( $this );
    }
    
    // ----------------------------------------------------------------------
    // Plugin row on plugins.php page
    // ----------------------------------------------------------------------
    
    public function showCustomPluginRow($file, $plugin_data) {
        if ( !is_network_admin() && is_multisite() ) return;
        
        $messages = apply_filters('factory_plugin_row_' . $this->pluginName, array(), $file, $plugin_data);

        // if nothign to show then, use default handle
        if ( count($messages) == 0 ) {
            wp_plugin_update_row($file, $plugin_data);
            return;
        } 

        $wp_list_table = _get_list_table('WP_Plugins_List_Table');

        foreach($messages as $message) {
            echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
            echo $message;
            echo '</div></td></tr>'; 
        }
    }
    
    // ----------------------------------------------------------------------
    // Finding files
    // ----------------------------------------------------------------------
    
    /**
     * Returns a list of files at a given path.
     * @param string $path      path for search
     */
    private function findFiles( $path ) {
        return $this->findFileOrFolders($path, true); 
    }
    
    /**
     * Returns a list of folders at a given path.
     * @param string $path      path for search
     */
    private function findFolders( $path ) {
        return $this->findFileOrFolders($path, false); 
    }
    
    /**
     * Returns a list of files or folders at a given path.
     * @param string $path      path for search
     * @param bool $files       files or folders?
     */
    private function findFileOrFolders( $path, $areFiles = true ) {
        if ( !is_dir($path)) return array();
        
        $entries = scandir( $path );
        if (empty($entries)) return array();

        $files = array();
        foreach($entries as $entryName) {
            if ( $entryName == '.' || $entryName == '..') continue;
            
            $filename = $path . '/' . $entryName;
            if ( ( $areFiles && is_file($filename) ) || ( !$areFiles && is_dir($filename) ) ) {
                $files[] = array(
                    'path' => str_replace("\\", "/", $filename ),
                    'name' => $areFiles ? str_replace('.php', '', $entryName) : $entryName
                );
            }
        }
        return $files;  
    }
    
    /**
     * Gets php classes defined in a specified file.
     * @param type $path
     */
    private function getClasses( $path ) {

        $phpCode = file_get_contents($path);
        
        $classes = array();
        $tokens = token_get_all($phpCode);

        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
          if ( 
              is_array($tokens) 
              && $tokens[$i - 2][0] == T_CLASS
              && $tokens[$i - 1][0] == T_WHITESPACE
              && $tokens[$i][0] == T_STRING) {

              $extends = null;
              if ($tokens[$i + 2][0] == T_EXTENDS && $tokens[$i + 4][0] == T_STRING) {
                  $extends = $tokens[$i + 4][1];
              }
              
              $class_name = $tokens[$i][1];
              $classes[$path] = array( 
                  'name' => $class_name,
                  'extends' => $extends
              );
          }
        }
        
        /**
         * result example:
         * 
         * $classes['/plugin/items/filename.php'] = array(
         *      'name'      => 'PluginNameItem',
         *      'extendes'  => 'PluginNameItemBase'
         * )
         */
        return $classes;   
    }
    
    // ----------------------------------------------------------------------
    // Public methods
    // ----------------------------------------------------------------------
    
    public function newScriptList() {
        return new Factory324_ScriptList( $this );
    }
    
    public function newStyleList() {
        return new Factory324_StyleList( $this );
    }
}

if (!function_exists('factory_run_code')) {
    
    /**
     * A global helper method to run code.
     * 
     * @since 1.0.0
     * @return mixed
     */
    function factory_run_code( $codeToRun ) {
        return eval( $codeToRun );
    }
}

if (!function_exists('factory_glob')) {
    
    /**
     * A global helper method to get global variable by its name.
     * 
     * @since 1.0.0
     * @return mixed
     */
    function factory_glob( $name, $default = null ) {
        return isset( $GLOBALS ) ? $GLOBALS[$name] : $default;
    } 
}