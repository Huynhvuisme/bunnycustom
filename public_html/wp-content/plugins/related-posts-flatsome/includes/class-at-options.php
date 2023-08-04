<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('init', function(){

    if (class_exists('Flatsome_Option')):

        Flatsome_Option::add_section( 'at_blog_related', array(
            'title' => __( 'Blog Related Posts', 'atweb' ),
            'panel' => 'blog',
        ) );
        
        Flatsome_Option::add_field( 'option', array(
            'type'            => 'text',
            'settings'        => 'at_related_blog_custom_title',
            'label'           => __( 'Custom title', 'atweb' ),
            'description' => __( "Default 'Related Posts' ", 'atweb' ),
            'section'         => 'at_blog_related',
        ) );

        Flatsome_Option::add_field( 'option', array(
            'type'            => 'select',
            'settings'        => 'at_related_blog_type',
            'label'           => __( 'Related Posts Style', 'atweb' ),
            'section'         => 'at_blog_related',
            'default'         => 'row',
            'choices'         => array(
                'row'   => __( 'Grid', 'atweb' ),
                'slider' => __( 'Slider', 'atweb' ),
            ),
        ) );
        
        Flatsome_Option::add_field( 'option', array(
            'type'     => 'slider',
            'settings' => 'at_related_blog_pr_row',
            'label'    => __( 'Post per row - Desktop', 'atweb' ),
            'section'  => 'at_blog_related',
            'default'  => 4,
            'choices'  => array(
                'min'  => 1,
                'max'  => 6,
                'step' => 1,
            ),
        ) );
        
        Flatsome_Option::add_field( 'option', array(
            'type'     => 'slider',
            'settings' => 'at_related_blog_pr_row_tablet',
            'label'    => __( 'Post per row - Tablet', 'atweb' ),
            'section'  => 'at_blog_related',
            'default'  => 3,
            'choices'  => array(
                'min'  => 1,
                'max'  => 4,
                'step' => 1,
            ),
        ) );
        
        Flatsome_Option::add_field( 'option', array(
            'type'     => 'slider',
            'settings' => 'at_related_blog_pr_row_mobile',
            'label'    => __( 'Post per row - Mobile', 'atweb' ),
            'section'  => 'at_blog_related',
            'default'  => 2,
            'choices'  => array(
                'min'  => 1,
                'max'  => 3,
                'step' => 1,
            ),
        ) );
        
        Flatsome_Option::add_field( 'option',  array(
            'type'        => 'checkbox',
            'settings'     => 'at_blog_related_show_excerpt',
            'label'       => __( 'Show Excerpts', 'atweb' ),
            'section'     => 'at_blog_related',
            'default'     => 1,
        ));
        
        Flatsome_Option::add_field( 'option', array(
            'type'     => 'text',
            'settings' => 'at_max_related_blog',
            'label'    => __( 'Max number of Related Posts', 'atweb' ),
            'section'  => 'at_blog_related',
            'default'  => '6',
        ) );

        Flatsome_Option::add_field( 'option', array(
            'type'            => 'select',
            'settings'        => 'at_related_blog_image_size',
            'label'           => __( 'Image size', 'atweb' ),
            'section'         => 'at_blog_related',
            'default'         => 'default',
            'choices'         => array(
                'default'   => __( 'Default', 'atweb' ),
                'large'     => __( 'Large', 'atweb' ),
                'medium'    => __( 'Medium', 'atweb' ),
                'thumbnail' => __( 'Thumbnail', 'atweb' ),
                'original' => __( 'Original', 'atweb' )
            ),
        ) );

        Flatsome_Option::add_field( 'option', array(
            'type'     => 'slider',
            'settings' => 'at_related_blog_image_height',
            'label'    => __( 'Image height', 'atweb' ),
            'section'  => 'at_blog_related',
            'default'  => 56,
            'choices'  => array(
                'min'  => 0,
                'max'  => 100,
                'step' => 1,
            ),
        ) );
    
    endif;

});
