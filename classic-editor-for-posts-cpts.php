<?php
/**
 * Plugin Name: Classic Editor for Posts & CPTs
 * Description: Disables Gutenberg on 'post' and all custom post types. Keeps Gutenberg on 'page'.
 * Author:      Zach Elkins
 * Version:     1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'use_block_editor_for_post_type', function( $use_block_editor, $post_type ) {
    
    // Always allow Gutenberg on pages
    if ( 'page' === $post_type ) {
        return true;
    }
    
    // Force Classic Editor on 'post' and ANY custom post type
    if ( 'post' === $post_type || post_type_exists( $post_type ) && ! in_array( $post_type, [ 'page', 'attachment' ] ) ) {
        return false; // false = use Classic Editor
    }
    
    // Fallback
    return $use_block_editor;
}, 999, 2 );

// Remove "Try Gutenberg" / editor nags
add_action( 'admin_init', function() {
    remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );
});
