<?php
/**
 * @link              https://atweb.vn
 * @since             1.0.0
 * @package           FlatsomeRelatedPosts
 *
 * @wordpress-plugin
 * Plugin Name:       Related Posts Flatsome
 * Plugin URI:        https://atweb.vn/
 * Description:       Display related posts in single post page
 * Version:           1.0.1
 * Author:            Minh Tiến
 * Author URI:        https://www.facebook.com/minhtien94.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       atweb
 * Domain Path:       /languages
 * Tags:              Related Posts, Related Posts Flatsome, Flatsome
 * Donate link:       https://paypal.me/tienweb
 * 
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if ( get_template() !== 'flatsome' ){
    /**
     * Display error
     */
    add_action( 'admin_notices', function(){

        // class user notices default in wp : notice notice-warning, error , notice notice-error
        printf( 
        '<div class="error"><p>%s</p></div>', 
        apply_filters( 'at_text_error_theme', __( 'Related Posts Flatsome plugin only works on flatsome theme', 'atweb' ) ) 
        );
  
    });
    return;
}

add_action( 'init', function(){
    load_plugin_textdomain( 'atweb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
} );

require plugin_dir_path( __FILE__ ) . 'includes/class-at-options.php';

require plugin_dir_path( __FILE__ ) . 'includes/class-at-related-posts.php';

use AT\RelatedBlog; 

if (class_exists('AT\RelatedBlog')) {
    add_action('after_setup_theme', function(){
        RelatedBlog::init();
    });
}
