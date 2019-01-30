<?php
/*
 * Copyright (C) 2014 Bellevue College
 * Copyright (C) 2009 Ioannis C. Yessios
 *
 * This file is part of the WordPress CAS Client
 *
 * The WordPress CAS Client is free software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Bellevue College
 * Address: 3000 Landerholm Circle SE
 *          Room N215F
 *          Bellevue WA 98007-6484
 * Phone:   +1 425.564.4201
 */

require_once constant( 'CAS_CLIENT_ROOT' ) . '/includes/generate-password.php';

/**
 * authenticate_cas_user function
 *
 * Authenticate user via CAS
 *
 * @retval string|void The login of the authenticated CAS user
 **/
function authenticate_cas_user() {
	global $wp_cas_ldap_use_options, $cas_configured, $blog_id;

	if ( ! $cas_configured ) {
		$message = __( 'WordPress CAS Client plugin not configured.', 'wpcasldap' );
		wp_die( $message, $message);
	}

	if ( phpCAS::isAuthenticated() ) {
		// CAS was successful
		return phpCAS::getUser();
	} else {
		// Authenticate the user
		phpCAS::forceAuthentication();
		exit();
	}
}

/**
 * Update and authenticated user
 *
 * @retval void
 */
function update_and_auth_user($cas_user, $wordpress_user) {
	global $wp_cas_ldap_use_options;

	// Update user information from ldap
	if ( 'yes' === $wp_cas_ldap_use_options['useldap'] && function_exists( 'ldap_connect' ) ) {
		$existing_user = get_ldap_user( $cas_user );
		if ( $existing_user ) {
			$user_data = $existing_user->get_user_data( );
			$user_data['ID'] = $wordpress_user->ID;

			// Remove role from userdata
			unset( $user_data['role'] );

			$user_id = wp_update_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				$error_string = $user_id->get_error_message( );
				error_log( 'Update user failed: ' . $error_string );
				echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
			}
		}
	}

	$user_exists = is_user_member_of_blog( $wordpress_user->ID, $blog_id );
	if ( ! $user_exists ) {
		if ( function_exists( 'add_user_to_blog' ) ) {
			add_user_to_blog( $blog_id, $wordpress_user->ID, $wp_cas_ldap_use_options['userrole'] );
		}
	}

	// the CAS user has a WP account
	wp_set_auth_cookie($wordpress_user->ID);
}

/**
 * wp_cas_ldap_now_puser function
 *
 * @param string $new_user_id the username of a user
 */
function wp_cas_ldap_now_puser( $new_user_id ) {
	global $wp_cas_ldap_use_options;
	$user_data = '';
	if ( 'yes' === $wp_cas_ldap_use_options['useldap'] && function_exists( 'ldap_connect' ) ) {
		$new_user = get_ldap_user( $new_user_id );

		if ( $new_user ) {
			$user_data = $new_user->get_user_data();
		} else {
			error_log( 'User not found on LDAP Server: ' . $new_user_id );
		}
	} else {
		$user_data = array(
			'user_login' => $new_user_id,
			'user_pass'  => generate_password( 32, 64 ),
			'user_email' => $new_user_id . '@' . $wp_cas_ldap_use_options['email_suffix'],
			'role'       => $wp_cas_ldap_use_options['userrole'],
		);
	}

	if ( ! function_exists( 'wp_insert_user' ) ) {
		include_once ( ABSPATH . WPINC . '/registration.php' );
	}

	if ( $user_data ) {
		$user_id = wp_insert_user( $user_data );
		if ( is_wp_error( $user_id ) ) {
			$error_string = $user_id->get_error_message( );
			error_log( 'Inserting a user in wp failed: ' . $error_string );
			echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
			return;
		} else {
			wp_set_auth_cookie( $user_id );

			if ( isset( $_GET['redirect_to'] ) ) {
				wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( ) );
				exit( );
			}

			wp_redirect( site_url( '/wp-admin/' ) );
			exit( );
		}
	}
}

/**
 * get_ldap_user function
 *
 * @param string $login User login
 * @return false|WP_CAS_LDAP_User returns WP_CAS_LDAP_User object as long as user is
 *                             found on the ldap server, otherwise false.
 */
function get_ldap_user( $login ) {
	global $wp_cas_ldap_use_options;
	$ds = ldap_connect( $wp_cas_ldap_use_options['ldaphost'], $wp_cas_ldap_use_options['ldapport'] );
	//Can't connect to LDAP.
	if ( ! $ds ) {
		error_log('Error in contacting the LDAP server: ' . $wp_cas_ldap_use_options['ldaphost'] . ':' . $wp_cas_ldap_use_options['ldapport']);
	} else {
		// Make sure the protocol is set to version 3
		if ( ! ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 ) ) {
			error_log( 'Failed to set LDAP protocol to version 3.' );
		} else {
			// Do not allow referrals, per MS recommendation
			if ( ! ldap_set_option( $ds, LDAP_OPT_REFERRALS, 0 ) ) {
				error_log( 'Failed to set LDAP Referrals to False.' );
			} else {
				// Get LDAP service account DN/password
				$ldap_bind_dn = $wp_cas_ldap_use_options['ldapbinddn'];
				$ldap_bind_pwd = $wp_cas_ldap_use_options['ldapbindpwd'];
				if (strlen($ldap_bind_pwd) > 0)
					$ldap_bind_pwd = wp_cas_ldap_settings :: decrypt($ldap_bind_pwd);

				$bind = ldap_bind( $ds, $ldap_bind_dn, $ldap_bind_pwd );

				//Check to make sure we're bound.
				if ( ! $bind ) {
					error_log( 'LDAP Bind failed with Service Account: ' . $ldap_bind_pwd );
				} else {
					$search = ldap_search(
						$ds,
						$wp_cas_ldap_use_options['ldapbasedn'],
						$wp_cas_ldap_use_options['ldap_map_login_attr'] . '=' . $login,
						array(
							$wp_cas_ldap_use_options['ldap_map_login_attr'],
							$wp_cas_ldap_use_options['ldap_map_email_attr'],
							$wp_cas_ldap_use_options['ldap_map_alt_email_attr'],
							$wp_cas_ldap_use_options['ldap_map_first_name_attr'],
							$wp_cas_ldap_use_options['ldap_map_last_name_attr'],
							$wp_cas_ldap_use_options['ldap_map_role_attr'],
							$wp_cas_ldap_use_options['ldap_map_nickname_attr'],
							$wp_cas_ldap_use_options['ldap_map_nicename_attr'],
						),
						0
					);
					if($search) {
						$count = ldap_count_entries( $ds, $search);
						if ($count == 1) {
							$entry = ldap_first_entry( $ds, $search );
							return new WP_CAS_LDAP_User(
								ldap_get_dn( $ds, $entry),
								ldap_get_attributes( $ds, $entry)
							);
						}
						else {
							error_log("Duplicated users found in LDAP for login '$login'.");
						}
					}
					else {
						error_log("User not found in LDAP for login '$login'.");
					}
				}
			}
		}
		ldap_close( $ds );
	}
	return false;
}
