<?php 

namespace Commvault\Blockinventory\utils;

use Commvault\Blockinventory\models\SearchBlock;
use Commvault\Blockinventory\models\ShowResults;

class BlockInventorySubPage
{

    public function __construct()
    {   
        $menu_slug = 'blockinventory-options';
        add_submenu_page($menu_slug, 'Options Blockinventory', 'Options BlockInventory', 'read', $menu_slug, array($this, 'render_block_inventory_page'));

        add_submenu_page($menu_slug, 'Extra Options', 'Extra Options', 'read', 'extraOptions', array($this, 'extraOptionsCallback'));
    }

    public function render_block_inventory_page()
    {   
        $search = new SearchBlock();
        $ui = new ShowResults();

        ?>
        <div class="wrap">
            <h1>Commvault Block Inventory</h1>
            <p>Select a block and get the information of all the pages/cpt where it was used.</p>

            <?php 
                // Obtener todos los bloques disponibles
                $blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
                // Filtrar los bloques que comienzan con el prefijo "blocksmg"
                $filtered_blocks = array_filter(
                    $blocks, function ($block) {
                        return strpos($block->name, 'commvault-v2/') === 0;
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

    public function extraOptionsCallback()
    {
        ?>
        <div class="wrap">
            <h1>Block Inventory Pages - Cpts</h1>

            <?php 
                global $wpdb;

                $ui = new ShowResults();

                $custom_post_types = get_post_types(["_builtin" => false]);
                $custom_post_types[] = "post";
                $custom_post_types[] = "page";
                $post_types_str = "'" . implode("', '", $custom_post_types) . "'";

                // Consulta SQL para obtener las páginas y los bloques utilizados
                $query = "
                    SELECT ID, post_title, post_content, post_type
                    FROM {$wpdb->posts}
                    WHERE post_type IN ($post_types_str)
                ";
                
                $results = $wpdb->get_results($query);
                
                $pages_with_blocks = array();
                
                foreach ($results as $result) {
                    $blocks = parse_blocks($result->post_content);
                    
                    $contains_commvault_v2 = false;
                    foreach ($blocks as $block) {
                        if (!empty($block['blockName']) && strpos($block['blockName'], 'commvault-v2/') !== false) {
                            $contains_commvault_v2 = true;
                            break;
                        }
                    }
                    
                    //  If the page contains blocks with "commvault-v2/" in its name, store it together with the blocks used.
                    if ($contains_commvault_v2) {
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
                        return strpos($block, 'commvault-v2/') !== false;
                    });
                    // Only add to the results if there are blocks containing "commvault-v2/" in their name.
                    if (!empty($filtered_blocks)) {
                        $filtered_results[] = array(
                            'ID' => $page_id,
                            'title' => $page_data['title'],
                            'post_type' => $page_data['post_type'],
                            'blocks' => implode(', ', $filtered_blocks)
                        );
                    }
                }
                
                $showResults = $ui->createTable($filtered_results, 'blocks');

                echo $showResults;
            ?>
        </div>
        <?php 
    }
} ?>