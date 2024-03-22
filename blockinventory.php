<?php
/**
 * Plugin Name:       BlockInventoryMager
 * Description:       A plugin of custom blocks by mager19.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.8.3
 * Author:            Mager19
 * Author URI:        https://twitter.com/mager19
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       BlockInventoryMager
 *
 * @category Blocks
 * @package  CreateBlock
 * @author   Mager19 <mager19@gmail.com>
 * @license  GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://twitter.com/mager19
 */

use Mager19\Blockinventory\models\SearchBlock;
use Mager19\Blockinventory\utils\BlockInventoryPage;

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

