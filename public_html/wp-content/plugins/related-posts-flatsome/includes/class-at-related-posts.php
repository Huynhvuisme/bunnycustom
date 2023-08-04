<?php

namespace AT;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * 
 */
if ( !class_exists('RelatedBlog')) {
    class RelatedBlog {

        public static function init(){
            add_action( 'flatsome_before_comments', array( __CLASS__, 'related_blog' ) );
        }

        public static function related_blog(){
            global $post;
            $post_id = $post->ID;
            $cat_ids = self::get_list_category_id($post_id);
            $ids = array();
            if ($cat_ids):
                $args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'category__in' => $cat_ids,
                    'post__not_in' => array( $post_id ),
                    'posts_per_page'=> get_theme_mod('at_max_related_blog',8),
                    'orderby' => 'rand'
                );
                $posts = get_posts($args);
                if ( !empty( $posts ) ) {
                    foreach($posts as $post){
                        array_push($ids, $post->ID);
                    }
                    wp_reset_query();
                    $ids = implode(',', $ids);
                }
            endif;

            if ( $ids ){
                $title_text = get_theme_mod('at_related_blog_custom_title') ? get_theme_mod('at_related_blog_custom_title') : __( 'Related Posts', 'atweb' );
                ?>
                <div id='at_posts_related'>
                    <h3 class='container-width posts-section-title-related pt-half pb-half uppercase'> <?php echo esc_html($title_text) ?> </h3>
                    <?php
                        echo flatsome_apply_shortcode( 'blog_posts', array(
                            'type'              => get_theme_mod('at_related_blog_type','row'),
                            'depth'             => get_theme_mod( 'blog_posts_depth', 0 ),
                            'depth_hover'       => get_theme_mod( 'blog_posts_depth_hover', 0 ),
                            'text_align'        => get_theme_mod( 'blog_posts_title_align', 'center' ),
                            'style'             => 'normal',
                            'columns'           => get_theme_mod('at_related_blog_pr_row','4'),
                            'columns__md'       => get_theme_mod('at_related_blog_pr_row_tablet','3',),
                            'columns__sm'       => get_theme_mod('at_related_blog_pr_row_mobile','3'),
                            'show_date'         => get_theme_mod( 'blog_badge', 1 ) ? 'true' : 'false',
                            'ids'               => $ids,
                            'excerpt'           => get_theme_mod( 'at_blog_related_show_excerpt', 'true' ) ? 'visible' : 'false',
                            'slider_nav_style'  => "circle",
                            'image_height'      => get_theme_mod('at_related_blog_image_height','56') . '%',
                            'image_size'        => get_theme_mod('at_related_blog_image_size','default')
                        ) );
                    ?>
                </div>
                <?php
            }
        }

        public static function get_list_category_id($post_id){
            $categories = get_the_category($post_id);
            $cat_ids = array();
            if ( $categories ):

                foreach( $categories as $cat_obj ){
                    $cat_ids[] = $cat_obj->term_id;
                }

            endif;
            return $cat_ids;
        }
    }
}