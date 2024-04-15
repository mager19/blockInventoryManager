<?php

namespace Plugins40Q\Blockinventory\models;

class SearchBlock
{

    public function search_block_in_content($block_to_find)
    {
        global $wpdb;

        if(!$wpdb) {
            return [];
        }

        $results = [];
        $custom_post_types = get_post_types(["_builtin" => false]);
        $custom_post_types[] = "post";
        $post_types_str = "'" . implode("', '", $custom_post_types) . "'";

        $page_query = $wpdb->prepare(
            "SELECT ID, post_title, post_content, 'page' AS post_type FROM {$wpdb->posts} WHERE post_type = 'page' AND post_content LIKE %s",
            '%' . $wpdb->esc_like($block_to_find) . '%'
        );
        
        $post_query = $wpdb->prepare(
            "SELECT ID, post_title, post_type, post_content FROM {$wpdb->posts} WHERE post_type IN ($post_types_str) AND post_status = 'publish' AND post_content LIKE %s",
            '%' . $wpdb->esc_like($block_to_find) . '%'
        );
        
        // Unir ambas consultas
        $combined_query = "($page_query) UNION ($post_query)";
        
        $combined_results = $wpdb->get_results($combined_query);
        
        foreach ($combined_results as $result) {
            if ($result->post_type === 'page') {
                $post_type = 'page';
            } else {
                $post_type = get_post_type($result->ID);
            }
            
            $permalink = get_permalink($result->ID);
            
            $results[] = [
                'ID' => $result->ID,
                'permalink' => $permalink,
                'title' => $result->post_title,
                'post_type' => $post_type
            ];
        }

        return $results;
    }


    // Function to check if a block is present in the content 
    static function block_is_present($content, $block_to_find)
    {
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            $block_name = $block["blockName"];
            if ($block_name === $block_to_find) {
                return true;
            }
        }
        return false;
    }

}
