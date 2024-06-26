<?php 

namespace Agency40Q\Blockinventory\utils;

use Agency40Q\Blockinventory\models\SearchBlock;
use Agency40Q\Blockinventory\utils\BlockInventorySettings;
use Agency40Q\Blockinventory\utils\BlockInventoryListTable;

class BlockInventorySubPageUtil
{   
    protected $block_prefix;

    public function __construct()
    {           
        $this->addPages();
        $this->BlockInventoryRegisterOptions();
        $this->updateOptions();
    }

    public function addPages(){
        $menu_slug = 'blockinventory-options';

        // Page Block Inventory
        add_submenu_page($menu_slug, 'Blockinventory', 'Pages', 'read', $menu_slug, array($this, 'render_block_inventory_page'));

        //Page Search Blocks
        add_submenu_page($menu_slug, 'Extra Options', 'Search Blocks', 'read', 'extraOptions', array($this, 'extraOptionsCallback'));

        // Page settings
        add_submenu_page( $menu_slug, 'Block Inventory Settings', 'Settings', 'manage_options', 'blockinventory_settings', array($this,'blockInventory_admin_options') ); 
    }

    private function updateOptions(){
        // Delete transient when the settings are updated
        add_action('update_option_40qtransient_expiration', function($old_value, $value) {
            if ($old_value !== $value) {
                delete_transient('blockInventory');
            }
        }, 10, 2);

        add_action('update_option_40qblock_prefix', function($old_value, $value) {
            if ($old_value !== $value) {
                delete_transient('blockInventory');
            }
        }, 10, 2);
    }

    private function BlockInventoryRegisterOptions(){
        $blockInventorySettings = new BlockInventorySettings();
        $blockInventorySettings::registerSetting('40qblock_prefix');
        $blockInventorySettings::registerSetting('40qtransient_expiration');
    }

    public function blockInventory_admin_options(){ ?>
            <h1><?php echo esc_html__( 'Settings', 'Blockinventory' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'blockinventory-plugin-settings-group' ); ?>
                <?php do_settings_sections( 'blockinventory-plugin-settings-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__( 'Block prefix:', 'Blockinventory' ); ?></th>
                        <td><input type="text" name="40qblock_prefix" value="<?php echo esc_attr( get_option( '40qblock_prefix' ) ); ?>"/></td>             
                    </tr>
                    <tr valign="top">
                    <span><i><?php echo esc_html__( 'if no value is assigned, the default value "core/" will be used', 'Blockinventory' ); ?></i></span>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__( 'Transient Expiration:', 'Blockinventory' ); ?></th>
                        <td>
                        <select name="40qtransient_expiration"> 
                            <option selected="selected" value="<?php echo esc_attr(MINUTE_IN_SECONDS); ?>" <?php selected( get_option( '40qtransient_expiration' ), MINUTE_IN_SECONDS ); ?>>1 Minute</option>
                            <option value="<?php echo esc_attr(MINUTE_IN_SECONDS*5); ?>" <?php selected( get_option( '40qtransient_expiration' ), MINUTE_IN_SECONDS*5 ); ?>>5 Minutes</option>
                            <option value="<?php echo esc_attr(HOUR_IN_SECONDS); ?>" <?php selected( get_option( '40qtransient_expiration' ), HOUR_IN_SECONDS ); ?>>1 Hour</option>
                            <option value="<?php echo esc_attr(HOUR_IN_SECONDS*6); ?>" <?php selected( get_option( '40qtransient_expiration' ), HOUR_IN_SECONDS*6 ); ?>>6 Hours</option>
                            <option value="<?php echo esc_attr(DAY_IN_SECONDS); ?>" <?php selected( get_option( '40qtransient_expiration' ), DAY_IN_SECONDS ); ?>>1 Day</option>
                        </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>       
        <?php 
    } 

    public function render_block_inventory_page() {  ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Block Inventory Pages', 'Blockinventory' ); ?> </h1>
            <p><?php echo esc_html__( 'The following table shows the pages, posts and cpts in which the prefix defined in settings has been used, if you have not done so it will show the default native blocks (core/blocks) and the other blocks on the site.', 'Blockinventory' );  ?>  </p>
            <?php 
                global $wpdb;

                $filtered_results = get_transient('blockInventory');
                
                if ($filtered_results === false) {
                    $this->block_prefix = get_option('40qblock_prefix');
                    $custom_post_types = get_post_types(["_builtin" => false]);
                    $custom_post_types[] = "post";
                    $custom_post_types[] = "page";
                    $post_types_str = "'" . implode("', '", $custom_post_types) . "'";

                    // query SQL 
                    $query = "
                        SELECT ID, post_title, post_content, post_type, post_status, post_author
                        FROM {$wpdb->posts}
                        WHERE post_type IN ($post_types_str)
                    ";
                    
                    $prepared_query = $wpdb->prepare($query);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $results = $wpdb->get_results($prepared_query);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    
                    $pages_with_blocks = array();
                    
                    foreach ($results as $result) {
                        $blocks = parse_blocks($result->post_content);
                        
                        $contains_PluginsAgency40Q_v2 = false;
                        foreach ($blocks as $block) {
                            if (!empty($block['blockName']) && strpos($block['blockName'], $this->block_prefix) !== false) {
                                $contains_PluginsAgency40Q_v2 = true;
                                break;
                            }
                        }
                        
                        //  If the page contains blocks with "PluginsAgency40Q-v2/" in its name, store it together with the blocks used.
                        if ($contains_PluginsAgency40Q_v2) {
                            $page_blocks = array();
                            foreach ($blocks as $block) {
                                if (!empty($block['blockName'])) {
                                    $page_blocks[] = $block['blockName'];
                                }
                            }
                            $pages_with_blocks[$result->ID] = array(
                                'title' => $result->post_title,
                                'blocks' => $page_blocks,
                                'post_type' => $result->post_type,
                                'post_status' => $result->post_status ?? '',
                                'post_author' => get_the_author_meta('display_name', $result->post_author)
                            );
                        }
                    }
                    
                    $filtered_results = array();

                    foreach ($pages_with_blocks as $page_id => $page_data) {
                        $filtered_blocks = array_filter($page_data['blocks'], function($block) {
                            return strpos($block, $this->block_prefix) !== false;
                        });
                        // Only add to the results if there are blocks containing "PluginsAgency40Q-v2/" in their name.
                        if (!empty($filtered_blocks)) {
                            $filtered_results[] = array(
                                'ID' => $page_id,
                                'title' => $page_data['title'],
                                'post_type' => $page_data['post_type'],
                                'post_status' => $page_data['post_status'],
                                'post_author' => $page_data['post_author'],
                                'blocks' => implode(', ', $filtered_blocks)
                            );
                        }
                    }
                    
                    if(get_option('40qtransient_expiration') !== false){
                        $transient_expiration = get_option('40qtransient_expiration');
                    }else{
                        $transient_expiration =  MINUTE_IN_SECONDS;
                    }

                    //Save the transient
                    set_transient('blockInventory', $filtered_results, $transient_expiration);
                }            
                
                $this->tableList($filtered_results);
                ?>                
        </div>        
        <?php        
    }

    public function extraOptionsCallback()
    {
        $search = new SearchBlock();
        $this->block_prefix = get_option('40qblock_prefix');
        ?>
        <div class="wrap">

            <h1><?php echo esc_html__( 'Block Inventory', 'Blockinventory' ); ?></h1>
            <p><?php echo esc_html__( 'Select a block and get the information of all the pages/cpt where it was used.', 'Blockinventory' ); ?></p>

            <?php 
                $blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

                $filtered_blocks = array_filter(
                    $blocks, function ($block) {
                        return strpos($block->name, $this->block_prefix) === 0;
                    }
                );
            ?>
            
            <form method="POST" action="" >
                <select name="blocks" id="blocksid">
                    <option selected disabled><?php echo esc_html__( 'Select a Block', 'Blockinventory' ); ?> </option>
                    <?php
                    foreach ($filtered_blocks as $block) {
                        echo '<option value="' . esc_attr($block->name) . '">' . esc_html($block->title) . '</option>';
                    }
                    ?>
                </select>
                <input type="submit" value="Submit" class="button button-primary">
            </form>

            <?php 
            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                $selected_block = isset($_POST['blocks']) ? $_POST['blocks'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                ?>

                <h2><?php echo esc_html($selected_block); ?></h2>
                <?php 
                $results = $search->search_block_in_content($selected_block);              

                if (!empty($results)) {
                    
                    $pages_table = new BlockInventoryListTable('blocks');
                    $pages_table->prepare_items($results);
                    ?>
    
                    <form id="pages" method="get">
                        <input type="hidden" name="page" value="<?php echo esc_html( $_REQUEST['page'] ); // phpcs:ignore WordPress.Security. NonceVerification.Missing ?>" /> 
                        <?php 
                        $pages_table->display(); 
                        ?>
                    </form>
                    
                    <?php 
                }else {   
                    echo esc_html__( "Not was found the selected block in any page or cpt", 'Blockinventory' );            
                }
            }
            ?>            
        </div>
        <?php 
    }

    private function tableList($filtered_results) { 
        $pages_table = new BlockInventoryListTable();
        $pages_table->prepare_items($filtered_results);        
    ?>    
        <form id="pages-filter" method="get">
            <input type="hidden" name="page" value="<?php echo esc_html( $_REQUEST['page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
            <?php 
            $pages_table->search_box('Search', 'search');
            $pages_table->display(); 
            ?>
        </form>
    <?php 
    }
} 
?>