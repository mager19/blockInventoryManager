<?php
/**
 * Plugin Name:       Block Inventory
 * Description:       A plugin of custom blocks by 40Q.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.3
 * Author:            40Q
 * Author URI:        https://40Q.agency
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       blockinventory
 */

require 'plugin-update-checker/plugin-update-checker.php';
use Agency40Q\Blockinventory\utils\BlockInventoryPageUtil;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (! defined('ABSPATH') ) {
    die('Silence is golden.');
}

if(!class_exists('BlockInventory')) {
    final class BlockInventory
    {

        public function __construct()
        {               
            include_once "vendor/autoload.php";
            $this->init();
        }

        public function init()
        {            
            new BlockInventoryPageUtil();
            $this->updateChecker();
            register_deactivation_hook( __FILE__, array($this,'blockInventoryDeactive') );
            add_action('admin_enqueue_scripts', array($this, 'blockInventoryEnqueue'));
        }      

        public function blockInventoryDeactive() {
            $blockInventoryOptions = array('40qtransient_expiration', '40qblock_prefix' );
            
            foreach($blockInventoryOptions as $option) {
                delete_option( $option );
            }
        }

        public function updateChecker(){
            $myUpdateChecker = PucFactory::buildUpdateChecker(
                'https://github.com/mager19/blockInventoryManager/',
                __FILE__,
                'BlockInventory'
            );

            //Set the branch that contains the stable release.
            $myUpdateChecker->setBranch('releases');
        }

        public function blockInventoryEnqueue(){
            wp_enqueue_style('blockinventory-style', plugin_dir_url(__FILE__) . 'src/css/blockinventory.css', array(), '1.0.0', 'all');
        }
    }
}

new BlockInventory();

?>
