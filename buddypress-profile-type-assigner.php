<?php
/**
 * BuddyPress Profile Type Assigner
 *
 * @package           BuddyPress-Profile-Type-Assigner
 * @author            WordPress VIP
 * @copyright         2025-onwards Shared and distributed between contributors.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       BuddyPress Profile Type Assigner
 * Description:       Automatically assigns BuddyPress profile types to new users based on their email domain.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Author:            WordPress VIP
 * Text Domain:       buddypress-profile-type-assigner
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Automattic\BuddyPressProfileTypeAssigner;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
You must define a BUDDYPRESS_PROFILE_TYPE_DOMAIN_MAPPING constant like:
define(
	'BUDDYPRESS_PROFILE_TYPE_DOMAIN_MAPPING',
	array(
		'@example.com'  => 'profile-type-1',
		'@example.org'  => 'profile-type-2',
		'@example.net'  => 'profile-type-3',
		'@example.info' => 'profile-type-4',
	)
);
*/

// Hook into user activation.
add_action( 'bp_core_activated_user', __NAMESPACE__ . '\\assign_profile_type', 1000 );

// Add a hook to run after activation is complete - without this, the pending user becomes a Subscriber again.
add_action( 'bp_after_activation', __NAMESPACE__ . '\\assign_profile_type', 1000 );

/**
 * Assigns profile type based on email domain.
 *
 * @param int $user_id The user ID.
 */
function assign_profile_type( $user_id ) {
	
	// Get user data.
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}

	// Get user's email.
	$email = $user->user_email;
	if ( empty( $email ) ) {
		// error_log( 'buddypress_profile_type_assigner: No email found for user: ' . $user_id );
		return;
	}

	// Get available member types.
	$member_types = bp_get_member_types( array(), 'names' );
	if ( empty( $member_types ) ) {
		// error_log( 'buddypress_profile_type_assigner: No member types available' );
		return;
	}

	// Find matching domain.
	$profile_type = null;
	foreach ( BUDDYPRESS_PROFILE_TYPE_DOMAIN_MAPPING as $domain => $type ) {
		if ( strpos( $email, $domain ) !== false ) {
			$profile_type = $type;
			break;
		}
	}

	// Not a match to a known domain.
	if ( ! $profile_type ) {
		return;
	}

	// Verify the profile type exists.
	if ( ! in_array( $profile_type, $member_types, true ) ) {
		error_log( 'buddypress_profile_type_assigner: Profile type not found: ' . $profile_type );
		return;
	}

	// Get current member types
	$current_types = bp_get_member_type( $user_id, false );
	if ( is_array( $current_types ) && in_array( $profile_type, $current_types, true ) ) {
		// error_log( 'buddypress_profile_type_assigner: User already has profile type: ' . $profile_type );
		// Don't return here - we still want to set the WP role.
	} else {
		// Set the member type.
		$result = bp_set_member_type( $user_id, $profile_type, false );
		if ( is_wp_error( $result ) ) {
			error_log( 'buddypress_profile_type_assigner: Error setting member type: ' . $result->get_error_message() );
			return;
		}
	}

	// Get the member type post ID to check role configuration.
	$member_type_id = bp_member_type_post_by_type( $profile_type );
	if ( ! $member_type_id ) {
		error_log( 'buddypress_profile_type_assigner: Could not find member type post for: ' . $profile_type );
		return;
	}

	// Get the configured WordPress role for this member type.
	$member_type_roles = get_post_meta( $member_type_id, '_bp_member_type_wp_roles', true );
	// error_log( 'buddypress_profile_type_assigner: Member type roles meta: ' . print_r( $member_type_roles, true ) );
	
	if ( empty( $member_type_roles ) || ! is_array( $member_type_roles ) ) {
		error_log( 'buddypress_profile_type_assigner: No WordPress roles configured for member type: ' . $profile_type );
		return;
	}

	// Set the WordPress role if configured.
	if ( isset( $member_type_roles[0] ) && 'none' !== $member_type_roles[0] ) {
		$user      = new \WP_User( $user_id );
		$old_roles = $user->roles;
		$new_role  = $member_type_roles[0];
		
		// Check if user already has the correct role.
		if ( in_array( $new_role, $old_roles, true ) ) {
			// error_log( 'buddypress_profile_type_assigner: User ' . $user_id . ' already has role ' . $new_role );
			return;
		}
		
		// error_log( 'buddypress_profile_type_assigner: Attempting to set role for user ' . $user_id . ' from [' . implode(',', $old_roles) . '] to ' . $new_role );
		
		// Remove existing roles.
		foreach ( $old_roles as $role ) {
			$user->remove_role( $role );
		}
		
		// Add the new role.
		$user->add_role( $new_role );
		
		// Verify the role was set.
		$user          = new \WP_User( $user_id ); // Refresh user object.
		$current_roles = $user->roles;
		// error_log( 'buddypress_profile_type_assigner: Current roles after update: ' . print_r( $current_roles, true ) );
		
		if ( ! in_array( $new_role, $current_roles, true ) ) {
			error_log( 'buddypress_profile_type_assigner: Failed to set role ' . $new_role . ' for user ' . $user_id );
		} else {
			// error_log( 'buddypress_profile_type_assigner: Successfully set role ' . $new_role . ' for user ' . $user_id );
		}
	}

	// Verify the type was set.
	$new_types = bp_get_member_type( $user_id, false );
	if ( ! is_array( $new_types ) || ! in_array( $profile_type, $new_types, true ) ) {
		error_log( 'buddypress_profile_type_assigner: Failed to verify member type was set' );
		return;
	}

	// error_log( 'buddypress_profile_type_assigner: Successfully set profile type ' . $profile_type . ' for user ' . $user_id );
}
