<?php 

namespace Mager19\Blockinventory\utils;

use Mager19\Blockinventory\utils;

class BlockInventoryPage
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
    }

    public function add_plugin_page()
    {   
        $menu_slug = 'blockinventory-options';
        add_menu_page('Blockinventory', 'Blockinventory', 'read', $menu_slug);

        $subpage = new BlockInventorySubPage();
    }
}





