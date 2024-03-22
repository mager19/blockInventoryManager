<?php 

namespace Mager19\Blockinventory\utils;

use Mager19\Blockinventory\models\SearchBlock;

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
        $test = new SearchBlock();
        $results = $test->search_block_in_content("core/paragraph");

        ?>
        <div class="wrap">
            <h1>Block Inventory Manager</h1>
            <p>Aquí puedes ver la información de tu plugin.</p>
            
            <form method="POST" action="" >
                <select name="blocks" id="blocksid">
                    <option selected>Select a Block </option>
                    <?php
                    // Obtener todos los bloques disponibles
                    $blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
                    // Filtrar los bloques que comienzan con el prefijo "blocksmg"
                    $filtered_blocks = array_filter(
                        $blocks, function ($block) {
                            return strpos($block->name, 'blocksmg/') === 0;
                        }
                    );
                    // Mostrar opciones en el select
                    foreach ($filtered_blocks as $block) {
                        echo '<option value="' . esc_attr($block->name) . '">' . esc_html($block->title) . '</option>';
                    }
                    ?>
                </select>
                <input type="submit" value="Submit">
            </form>

            <?php 
            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                $selected_block = $_POST['blocks']; ?>

                <h2><?php echo $selected_block; ?></h2>
                <?php 
                $results = $test->search_block_in_content($selected_block);

                if (!empty($results)) {
                    foreach ($results as $result) {
                        echo $result . "<br>";
                    }
                }else {
                    echo "Not was found the block $selected_block in any page";
                
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
            <h1>Block Inventory Extra</h1>
            <p>Extra - Aquí puedes ver la información de tu plugin.</p>
         </div>
        <?php 
    }
} ?>