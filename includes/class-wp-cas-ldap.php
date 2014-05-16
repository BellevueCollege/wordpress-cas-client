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

require_once constant( 'CAS_CLIENT_ROOT' ) . '/includes/class-wp-cas-ldap-user.php';
require_once constant( 'CAS_CLIENT_ROOT' ) . '/includes/generate-password.php';
require_once constant( 'CAS_CLIENT_ROOT' ) . '/includes/wordpress-cas-client-functions.php';

/**
 * WP_CAS_LDAP class hook for WordPress.
 */
class WP_CAS_LDAP {

	/**
	 * authenticate function hook for WordPress.
	 *
	 * We call phpCAS to authenticate the user at the appropriate time
	 * (the script dies there if login was unsuccessful)
	 * If the user is not provisioned and wpcasldap_useradd is set to 'yes'
	 * wp_cas_ldap_now_puser() is called.
	 */
	function authenticate( ) {
		global $wp_cas_ldap_use_options, $cas_configured, $blog_id;

		if ( ! $cas_configured ) {
			exit( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ) );
		}

		if ( phpCAS::isAuthenticated( ) ) {
			// CAS was successful

			$user = get_user_by( 'login', phpCAS::getUser( ) );
			// If user already exists
			if ( $user ) {
					// Update user information from ldap
					if ( 'yes' === $wp_cas_ldap_use_options['useldap'] && function_exists( 'ldap_connect' ) ) {

						$existing_user = get_ldap_user( phpCAS::getUser( ) );
						if ( $existing_user ) {
							$user_data = $existing_user->get_user_data( );
							$user_data['ID'] = $user->ID;

							//Remove role from userdata
							unset( $user_data['role'] );

							$user_id = wp_update_user( $user_data );

							if ( is_wp_error( $user_id ) ) {
								$error_string = $user_id->get_error_message( );
								error_log( 'Update user failed: ' . $error_string );
								echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
							}
						}
					}

				$user_exists = is_user_member_of_blog( $user->ID, $blog_id );
				if ( ! $user_exists ) {
					if ( function_exists( 'add_user_to_blog' ) ) {
						add_user_to_blog( $blog_id, $user->ID, $wp_cas_ldap_use_options['userrole'] );
					}
				}

				// the CAS user has a WP account
				wp_set_auth_cookie($user->ID);

				if ( isset( $_GET['redirect_to'] ) ) {
					wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( ) );
					exit( );
				}
				wp_redirect( site_url( '/wp-admin/' ) );
				exit( );

			} else {
				// the CAS user _does_not_have_ a WP account
				if ( function_exists( 'wp_cas_ldap_now_puser' ) && 'yes' === $wp_cas_ldap_use_options['useradd'] ) {
					wp_cas_ldap_now_puser( phpCAS::getUser( ) );
				} else {
					exit( __( 'you do not have permission here', 'wpcasldap' ) );
				}
			}
		} else {
			// Authenticate the user
			phpCAS::forceAuthentication( );
			exit( );
		}
	}

	/**
	 * logout function hook for WordPress.
	 */
	function logout( ) {
		global $cas_configured;
		global $get_options_func;
		if ( ! $cas_configured ) {
			exit( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ) );
		}

		phpCAS::logout( array( 'url' => $get_options_func( 'siteurl' ) ) );
		exit( );
	}

	/**
	 * show_password_fields method hook for WordPress.
	 *
	 * We overide the show_password_fields functionality in WordPress to hide the
	 * password field on user profile pages.
	 *
	 * @param bool $show_password_fields required param by WordPress
	 * @returns false
	 */
	function show_password_fields( $show_password_fields ) {
		if ( 'user-new.php' !== basename( $_SERVER['PHP_SELF'] ) ) {
			return false;
		}

		$random_password = generate_password( 32, 64 );
		echo '<input id="wpcasldap_pass1" type="hidden" name="pass1" value="' . $random_password . '" />';
		echo '<input id="wpcasldap_pass2" type="hidden" name="pass2" value="' . $random_password . '" />';
		return false;
	}

	/**
	* disable_function method hook for WordPress.
	*
	* Disable reset, lost, and retrieve password features in WordPress.
	*/
	function disable_function( ) {
		exit( __( 'Sorry, this feature is disabled.', 'wpcasldap' ) );
	}
}
