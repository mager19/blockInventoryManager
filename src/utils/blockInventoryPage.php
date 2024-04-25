<?php 

namespace Agency40Q\Blockinventory\utils;


class BlockInventoryPage
{   
    protected $icon_url;
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        $this->icon_url = plugin_dir_url( __FILE__ ) . '../images/40q-wp-icon.svg';
    }

    public function add_plugin_page()
    {   
        $menu_slug = 'blockinventory-options';
        add_menu_page('Blockinventory', 'Block Inventory', 'read', $menu_slug,  '', $this->icon_url);

        $subpage = new BlockInventorySubPage();
    }
}
?>