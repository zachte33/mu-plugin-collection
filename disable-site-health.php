<?php
/**
 * Plugin Name: Disable Site Health (MU)
 * Description: Completely removes Site Health from the admin dashboard.
 * Version:     1.0
 * Author:      Zach Elkins
 * Author URI: https://zachwp.com/
 * License:     GPL2
 */

// 1. Remove Tools → Site Health menu
add_action( 'admin_menu', function() {
	remove_submenu_page( 'tools.php', 'site-health.php' );
}, 999 );

// 2. Remove dashboard widget
add_action( 'wp_dashboard_setup', function() {
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
}, 999 );

// 3. Block REST API endpoints
add_filter( 'rest_request_before_callbacks', function( $response, $handler, WP_REST_Request $request ) {
	$route = $request->get_route();
	if ( function_exists( 'str_starts_with' ) ) {
		$is_health = str_starts_with( $route, '/wp-site-health/' );
	} else {
		$is_health = strpos( $route, '/wp-site-health/' ) === 0;
	}
	if ( $is_health ) {
		return new WP_Error( 'forbidden', 'Site Health disabled.', [ 'status' => 403 ] );
	}
	return $response;
}, 10, 3 );

// 4. Disable all tests
add_filter( 'site_status_tests', '__return_empty_array' );

// 5. Remove admin bar link
add_action( 'admin_bar_menu', function( WP_Admin_Bar $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'health-check' );
}, 999 );

// 6. Hide any leftover UI with CSS
add_action( 'admin_head', function() {
	echo '<style>
		#health-check-accordion,.health-check-title,.site-health-status,
		.site-status-all-clear,.health-check-view-more{display:none !important}
	</style>';
} );