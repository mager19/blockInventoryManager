<?php 

namespace Agency40Q\Blockinventory\utils;

use Agency40Q\Blockinventory\models\SearchBlock;
use Agency40Q\Blockinventory\models\ShowResults;

class BlockInventorySubPage
{   
    protected $block_prefix;

    public function __construct()
    {   
        $menu_slug = 'blockinventory-options';
        add_submenu_page($menu_slug, 'Blockinventory', 'Pages', 'read', $menu_slug, array($this, 'render_block_inventory_page'));

        add_submenu_page($menu_slug, 'Extra Options', 'Search Blocks', 'read', 'extraOptions', array($this, 'extraOptionsCallback'));

        add_submenu_page( $menu_slug, 'Block Inventory Settings', 'Settings', 'manage_options', 'blockinventory_settings', array($this,'blockInventory_admin_options') ); 
        
        add_action( 'admin_init', array($this,'blockInventorySettings') );

        $this->block_prefix = get_option('block_prefix');

        add_action('update_option_transient_expiration', function($old_value, $value) {
            if ($old_value !== $value) {
                delete_transient('blockInventory');
            }
        }, 10, 2);
    }

    public function blockInventorySettings() {
        //register our settings
        register_setting( 'blockinventory-plugin-settings-group', 'block_prefix' );
        register_setting( 'blockinventory-plugin-settings-group', 'transient_expiration' );
    }

    public function blockInventory_admin_options(){
        ?>
            <h1>Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'blockinventory-plugin-settings-group' ); ?>
                <?php ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Block prefix:</th>
                            <td><input type="text" name="block_prefix" value="<?php echo get_option( 'block_prefix' ); ?>"/></td>
                        
                    </tr>
                    <tr valign="top">
                    <span><i>if no value is assigned, the default value "core/" will be used</i></span>

                    <tr valign="top">
                        <th scope="row">Transient Expiration:</th>
                        <td>
                            <select name="transient_expiration"> 
                                <option selected="selected" value="<?php echo MINUTE_IN_SECONDS; ?>" <?php selected( get_option( 'transient_expiration' ), MINUTE_IN_SECONDS ); ?>>1 Minute</option>
                                <option value="<?php echo MINUTE_IN_SECONDS*5; ?>" <?php selected( get_option( 'transient_expiration' ), MINUTE_IN_SECONDS*5 ); ?>>5 Minutes</option>
                                <option value="<?php echo HOUR_IN_SECONDS; ?>" <?php selected( get_option( 'transient_expiration' ), HOUR_IN_SECONDS ); ?>>1 Hour</option>
                                <option value="<?php echo HOUR_IN_SECONDS*6; ?>" <?php selected( get_option( 'transient_expiration' ), HOUR_IN_SECONDS*6 ); ?>>6 Hours</option>
                                <option value="<?php echo DAY_IN_SECONDS; ?>" <?php selected( get_option( 'transient_expiration' ), DAY_IN_SECONDS ); ?>>1 Day</option>
                            </select>
                        </td>
                    </tr>
                    
                </table>
            <?php submit_button(); ?>
            </form>        
        <?php 
    } 

    public function render_block_inventory_page()
    {   
        $ui = new ShowResults();

        ?>
        <div class="wrap">
            <h1>Block Inventory Pages</h1>
            <p>The following table shows the pages, posts and cpts in which the prefix defined in settings has been used, if you have not done so it will show the default native blocks (core/blocks) and the other blocks on the site.</p>
            <?php 
                global $wpdb;

                $ui = new ShowResults();

                $filtered_results = get_transient('blockInventory');
                
                if ($filtered_results === false) {
                    $custom_post_types = get_post_types(["_builtin" => false]);
                    $custom_post_types[] = "post";
                    $custom_post_types[] = "page";
                    $post_types_str = "'" . implode("', '", $custom_post_types) . "'";

                    // query SQL 
                    $query = "
                        SELECT ID, post_title, post_content, post_type
                        FROM {$wpdb->posts}
                        WHERE post_type IN ($post_types_str)
                    ";
                    
                    $results = $wpdb->get_results($query);
                    
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
                                'post_type' => $result->post_type
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
                                'blocks' => implode(', ', $filtered_blocks)
                            );
                        }
                    }
                    
                    if(get_option('transient_expiration') !== false){
                        $transient_expiration = get_option('transient_expiration');
                    }else{
                        $transient_expiration =  MINUTE_IN_SECONDS;
                    }

                    //Save the transient
                    set_transient('blockInventory', $filtered_results, $transient_expiration);
                }                
                
                $showResults = $ui->createTable($filtered_results, 'blocks');

                echo $showResults;
            ?>            
        </div>        
        <?php        
    }

    public function extraOptionsCallback()
    {
        $search = new SearchBlock();
        $ui = new ShowResults();

        ?>
        <div class="wrap">

            <h1>Block Inventory</h1>
            <p>Select a block and get the information of all the pages/cpt where it was used.</p>

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
                    <option selected disabled>Select a Block </option>
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

                $selected_block = $_POST['blocks']; ?>

                <h2><?php echo $selected_block; ?></h2>
                <?php 
                $results = $search->search_block_in_content($selected_block);              

                if (!empty($results)) {
                    $showResults = $ui->createTable($results);
                
                    echo $showResults;
                }else {
                    echo "Not was found the block $selected_block in any page or cpt";                
                }
            }
            ?>            
        </div>
        <?php 
    }
} ?>