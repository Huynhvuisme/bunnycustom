<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */

update_option( 'flatsome_wup_purchase_code', 'B5E0B5F8DD8689E6ACA49DD6E6E1A930' );
update_option( 'flatsome_wup_supported_until', '01.01.2050' );
update_option( 'flatsome_wup_buyer', 'Licensed' );
update_option( 'flatsome_wup_sold_at', time() );
delete_option( 'flatsome_wup_errors');
delete_option( 'flatsome_wupdates');
//register style, script file
function insert_styles(){
    wp_register_style('main-style',get_template_directory_uri().'/assets/css/custom.css', array(), '1.1.4');
    wp_enqueue_style('main-style');
    wp_register_script('custom-js',get_template_directory_uri().'/assets/js/custom.js',array('jquery'),'1.1.3');
    wp_enqueue_script('custom-js');
}
add_action('wp_enqueue_scripts','insert_styles');
add_action( 'the_post',  'wdc_move_price_order', 99  );
function wdc_move_price_order(){
    if(is_product()){
        global $product;
        if($product->is_type( 'variable' )):
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
            add_action( 'woocommerce_single_variation', 'woocommerce_template_single_price', 15 );
        endif;
    }
} 
// bỏ ajax khi bấm vào biến thể
add_filter('woocommerce_ajax_variation_threshold', 'custom_variation_threshold', 10, 2);
function custom_variation_threshold($threshold, $product) {
$max_variations =2000;
return $max_variations;
}
