<?php
/**
 * Created by PhpStorm.
 * User: Barry Hylton
 * Date: 3/17/2019
 * Time: 6:45 PM
 */

namespace BLHylton\InfoResStoreLocator\WordPress;


class WPAPI_Posts
{
    public function init()
    {
        add_action('rest_api_init', array($this, 'registerRoute'));
    }

    public function registerRoute()
    {
        register_rest_route('blhirsl/v1', 'posts', [
            'methods' => 'GET',
            'callback' => array($this, 'getPosts')
        ]);
    }

    public function getPosts()
    {
        $args = [
            'post_type' => get_option('blhirsl-post-type-selector'),
            'post_status' => ['publish'],
            'meta_key' => 'blhirsl-product-id-meta-value',
            'orderby' => 'title',
            'order' => 'ASC',
            'posts_per_page' => -1,
            'nopaging' => true,
            'meta_query' => [
                [
                    'key' => 'blhirsl-product-id-meta-value',
                    'compare' => 'EXIST'
                ],
                [
                    'key' => 'blhirsl-product-id-meta-value',
                    'value' => '',
                    'compare' => '!='
                ]
            ]

        ];

        $query = new \WP_Query($args);
        $posts = [];

        foreach ($query->posts as $post) {
            $metaValue = get_post_meta($post->ID, 'blhirsl-product-id-meta-value', true);
            $posts[$metaValue] = $post->post_title;
        }
        return (object)$posts;
    }
}