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
			$user_data = $new_user->get_user_data( );
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
 * @param string $uid ldap sAMAccountName value to match
 * @return false|WP_CAS_LDAP_User returns WP_CAS_LDAP_User object as long as user is
 *                             found on the ldap server, otherwise false.
 */
function get_ldap_user( $uid ) {
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
			// Get LDAP service sccount username
			$ldap_user = $GLOBALS['ldapUser'];
			// Get service account associated password
			$ldap_pass = $GLOBALS['ldapPassword'];
			$bind = ldap_bind( $ds, $ldap_user, $ldap_pass );

			//Check to make sure we're bound.
			if ( ! $bind ) {
				error_log( 'LDAP Bind failed with Service Account: ' . $ldap_user );
			} else {
				$search = ldap_search(
					$ds,
					$wp_cas_ldap_use_options['ldapbasedn'],
					'sAMAccountName=' . $uid,
					array(
						'uid',
						'mail',
						'givenname',
						'sn',
						'rolename',
						'cn',
						'EmployeeID',
						'sAMAccountName',
					),0
				);
                if($search)
                {
                    $info = ldap_get_entries( $ds, $search );
                    return new WP_CAS_LDAP_User( $info );
                }
			}
		}
		ldap_close( $ds );
	}
	return false;
}
