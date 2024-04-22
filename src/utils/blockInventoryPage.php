<?php 

namespace Agency40Q\Blockinventory\utils;


class BlockInventoryPage
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
    }

    public function add_plugin_page()
    {   
        $menu_slug = 'blockinventory-options';
        add_menu_page('Blockinventory', 'Block Inventory', 'read', $menu_slug,  '', 'dashicons-welcome-widgets-menus');

        $subpage = new BlockInventorySubPage();
    }
}



