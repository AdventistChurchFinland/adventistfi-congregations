<?php
/*
Plugin Name: Adventistfi Congregations
Description: Custom congregations for adventist.fi
Author: Glen Somerville
Version: 1.0.0
Text Domain: adventistfi-congregations
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function adventistfi_congregations_load_plugin_textdomain() {
    load_plugin_textdomain( 'adventistfi-congregations', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

function adventistfi_congregations_register_post_type()
{
	// Congregations
  $labels = array(
      'name'               => _x( 'Congregations', 'post type general name', 'adventistfi-congregations' ),
      'singular_name'      => _x( 'Congregation', 'post type singular name', 'adventistfi-congregations' ),
      'menu_name'          => _x( 'Congregations', 'admin menu', 'adventistfi-congregations' ),
      'name_admin_bar'     => _x( 'Congregation', 'add new on admin bar', 'adventistfi-congregations' ),
      'add_new'            => _x( 'Add New', 'congregations', 'adventistfi-congregations' ),
      'add_new_item'       => __( 'Add New Congregation', 'adventistfi-congregations' ),
      'new_item'           => __( 'New Congregation', 'adventistfi-congregations' ),
      'edit_item'          => __( 'Edit Congregation', 'adventistfi-congregations' ),
      'view_item'          => __( 'View Congregation', 'adventistfi-congregations' ),
      'all_items'          => __( 'All Congregations', 'adventistfi-congregations' ),
      'search_items'       => __( 'Search Congregations', 'adventistfi-congregations' ),
      'parent_item_colon'  => __( 'Parent Congregations:', 'adventistfi-congregations' ),
      'not_found'          => __( 'No congregations found.', 'adventistfi-congregations' ),
      'not_found_in_trash' => __( 'No congregations found in Trash.', 'adventistfi-congregations' ),
  );

  $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'seurakunnat/%adventistfi_congregation_lang%', 'with_front' => false ),
      'capability_type'    => 'post',
      'has_archive'        => 'seurakunnat',
      'hierarchical'       => false,
      'menu_position'      => null,
      'taxonomies'         => array('adventistfi_congregation_lang'),
			'menu_icon' 				 => 'dashicons-admin-multisite',
      'supports'           => array('title','editor','thumbnail'),
  );

	register_post_type( 'adventistfi_congreg', $args );
}

function adventistfi_congregations_custom_taxonomy() {

  $labels = array(
    'name'                       => _x( 'Language Groups', 'Taxonomy General Name', 'adventistfi-congregations' ),
    'singular_name'              => _x( 'Language Group', 'Taxonomy Singular Name', 'adventistfi-congregations' ),
    'menu_name'                  => _x( 'Language Groups', 'Taxonomy Menu Name', 'adventistfi-congregations' ),
    'all_items'                  => __( 'All Language Groups', 'adventistfi-congregations' ),
    'parent_item'                => __( 'Parent Language Group', 'adventistfi-congregations' ),
    'parent_item_colon'          => __( 'Parent Language Group:', 'adventistfi-congregations' ),
    'new_item_name'              => __( 'New Language Group', 'adventistfi-congregations' ),
    'add_new_item'               => __( 'Add New Language Group', 'adventistfi-congregations' ),
    'edit_item'                  => __( 'Edit Language Group', 'adventistfi-congregations' ),
    'update_item'                => __( 'Update Language Group', 'adventistfi-congregations' ),
    'view_item'                  => __( 'View Language Group', 'adventistfi-congregations' ),
    'separate_items_with_commas' => __( 'Separate language groups with commas', 'adventistfi-congregations' ),
    'add_or_remove_items'        => __( 'Add or remove language groups', 'adventistfi-congregations' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'adventistfi-congregations' ),
    'popular_items'              => __( 'Popular Language Groups', 'adventistfi-congregations' ),
    'search_items'               => __( 'Search Language Groups', 'adventistfi-congregations' ),
    'not_found'                  => __( 'Not Found', 'adventistfi-congregations' ),
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
    'rewrite'                    => array( 'slug' => 'seurakunnat', 'with_front' => false ),
  );
  register_taxonomy( 'adventistfi_congregation_lang', 'adventistfi_congreg', $args );

}

function adventistfi_congregation_show_permalinks( $post_link, $id = 0 ){
    $post = get_post($id);
    if ( is_object( $post ) && $post->post_type == 'adventistfi_congreg' ){
        $terms = wp_get_object_terms( $post->ID, 'adventistfi_congregation_lang' );
        if( $terms ){
            return str_replace( '%adventistfi_congregation_lang%' , $terms[0]->slug , $post_link );
        }
    }
    return $post_link;
}

function adventistfi_congregations_install() {
 
    adventistfi_congregations_custom_taxonomy();
    adventistfi_congregations_register_post_type();
 
    // Clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}

add_action( 'plugins_loaded', 'adventistfi_congregations_load_plugin_textdomain' );
add_action( 'init', 'adventistfi_congregations_custom_taxonomy' );
add_action( 'init', 'adventistfi_congregations_register_post_type' );
add_filter( 'post_type_link', 'adventistfi_congregation_show_permalinks' );

register_activation_hook( __FILE__, 'adventistfi_congregations_install' );

