<?php
/**
 * Plugin Name:       Plugins40Q Block Inventory
 * Description:       A plugin of custom blocks by Plugins40Q.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Plugins40Q
 * Author URI:        https://Plugins40Q.agency
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       BlockInventory
 */

use Plugins40Q\Blockinventory\utils\BlockInventoryPage;

if (! defined('ABSPATH') ) {
    die('Silence is golden.');
}

if(!class_exists('BlockInventoryMager')) {
    
    final class BlockInventoryMager
    {

        public function __construct()
        {   
            
            include_once "vendor/autoload.php";

            $this->init();
        }

        public function init()
        {            
            $blockInventoryPage = new BlockInventoryPage();
        }      
    }
}


$blockInventoryMager = new BlockInventoryMager;

?>
