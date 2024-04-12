<?php
/**
 * Plugin Name:       Commvault Block Inventory
 * Description:       A plugin of custom blocks by 40q.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            40q
 * Author URI:        https://40q.agency
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       BlockInventory
 *
 */

use Commvault\Blockinventory\utils\BlockInventoryPage;

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
