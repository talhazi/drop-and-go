<?php
/*
Plugin Name: BricksExtras
Description: Element Library for Bricks.
Version: 1.6.0
Author: BricksExtras
Author URI: https://bricksextras.com
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'BRICKSEXTRAS_BASE' ) ) {
	define( 'BRICKSEXTRAS_BASE', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'BRICKSEXTRAS_PATH' ) ) {
	define( 'BRICKSEXTRAS_PATH', plugin_dir_path(__FILE__) );
}

if ( ! defined( 'BRICKSEXTRAS_URL' ) ) {
	define( 'BRICKSEXTRAS_URL', plugin_dir_url(__FILE__) );
}


/* user functions */

function bricksextras_user_favorites( $list = 'post' ) {
	return method_exists( '\BricksExtras\Helpers', 'get_favorite_ids_array' ) ? \BricksExtras\Helpers::get_favorite_ids_array( $list ) : [];
}

/**
 * Get the array of user IDs who favorited a post
 *
 * @param int|null $post_id The ID of the post to check, defaults to current post ID
 * @return array Array of user IDs who favorited the post, or empty array if none or if post meta is disabled
 */
function bricksextras_post_favorite( $post_id = null ) {
	if ( $post_id === null ) {
		$post_id = get_the_ID();
	}
	return method_exists( '\BricksExtras\Helpers', 'get_favorite_users' ) ? \BricksExtras\Helpers::get_favorite_users( $post_id ) : [];
}

/**
 * Get the count of users who favorited a post
 *
 * @param int|null $post_id The ID of the post to check, defaults to current post ID
 * @return int Number of users who favorited the post, or 0 if none or if post meta is disabled
 */
function bricksextras_post_favorite_count( $post_id = null ) {
	if ( $post_id === null ) {
		$post_id = get_the_ID();
	}
	return method_exists( '\BricksExtras\Helpers', 'get_favorite_users_count' ) ? \BricksExtras\Helpers::get_favorite_users_count( $post_id ) : 0;
}

require dirname( __FILE__ ) . '/includes/Plugin.php';
new BricksExtras\Plugin();