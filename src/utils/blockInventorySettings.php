<?php 
namespace Agency40Q\Blockinventory\utils;

class blockInventorySettings {
    
    public function __construct()
    {   
        add_action( 'admin_init', array($this,'registerSetting') );
    }

    static function registerSetting($settingName) {
        register_setting( 'blockinventory-plugin-settings-group', $settingName );
    }
}
