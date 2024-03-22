<?php

namespace Mager19\Blockinventory\models;

class SearchBlock
{

    public function search_block_in_content($block_to_find)
    {
        $results = [];
        $pages = get_pages();
        // // Obtener todos los custom post types
        $custom_post_types = get_post_types(["_builtin" => false]);
        // Agregar el tipo de publicación 'post' a la lista de custom post types
        $custom_post_types[] = "post";
        // Iterar sobre todas las páginas y publicaciones
        foreach ($pages as $page) {
            $content = $page->post_content;
            if ($this->block_is_present($content, $block_to_find)) {
                $results[] = "El bloque $block_to_find está presente en la página: " . $page->post_title;
            }
        }
        foreach ($custom_post_types as $post_type) {
            $posts = get_posts(
                [
                "post_type" => $post_type,
                "posts_per_page" => -1,
                ]
            );
            foreach ($posts as $post) {
                $content = $post->post_content;
                if ($this->block_is_present($content, $block_to_find)) {
                    $results[] = "El bloque $block_to_find está presente en el $post_type: " . $post->post_title;
                }
            }
        }
        return $results;
    }

    // Función para verificar si un bloque está presente en el contenido
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
