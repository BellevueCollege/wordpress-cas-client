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
		global $wp_cas_ldap_use_options, $blog_id;

		$cas_user = authenticate_cas_user();

		$user = get_user_by( 'login', $cas_user );
		// If user already exists
		if ( $user ) {
			update_and_auth_user($cas_user, $user);

			if ( isset( $_GET['redirect_to'] ) ) {
				wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( ) );
				exit( );
			}
			wp_redirect( site_url( '/wp-admin/' ) );
			exit( );

		} else {
			// the CAS user _does_not_have_ a WP account
			if ( function_exists( 'wp_cas_ldap_now_puser' ) && 'yes' === $wp_cas_ldap_use_options['useradd'] ) {
				wp_cas_ldap_now_puser( $cas_user );
			} else {
				exit( __( 'you do not have permission here', 'wpcasldap' ) );
			}
		}
	}

	/**
	 * logout function hook for WordPress.
	 */
	function logout( ) {
		global $cas_configured;
		if ( ! $cas_configured ) {
			exit( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ) );
		}

		phpCAS::logout( array( 'url' => get_site_url() ) );
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

	/*
	 * restrict_access method hook for WordPress.
	 *
	 * Retrict access to site based on 'who_can_view' parameter.
	 */
	public function restrict_access( $wp ) {
		global $wp_cas_ldap_use_options;

		// No restriction on everyone mode
		if (!isset($wp_cas_ldap_use_options['who_can_view']) || $wp_cas_ldap_use_options['who_can_view'] == 'everyone') {
			return $wp;
		}

		// Allow some generic cas (inspired by Wordpress Authorizer plugin)
		if (
				// Always allow access if WordPress is installing.
				// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
				( defined( 'WP_INSTALLING' ) && isset( $_GET['key'] ) ) ||
				// Allow access for requests to /wp-json/oauth1 so oauth clients can authenticate to use the REST API.
				( property_exists( $wp, 'matched_query' ) && stripos( $wp->matched_query, 'rest_oauth1=' ) === 0 ) ||
				// Allow access for non-GET requests to /wp-json/*, since REST API authentication already covers them.
				( property_exists( $wp, 'matched_query' ) && 0 === stripos( $wp->matched_query, 'rest_route=' ) && isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) ||
				// Allow access for GET requests to /wp-json/ (root), since REST API discovery calls rely on this.
				( property_exists( $wp, 'matched_query' ) && 'rest_route=/' === $wp->matched_query )
		)
			return $wp;

		// User is already logged in ?
		if (is_user_logged_in()) {
			// Allow access in 'cas_authenticated_users' mode
			if ( $wp_cas_ldap_use_options['who_can_view'] == 'cas_authenticated_users' ) {
				return $wp;
			}

			// So we are in wordpress_authenticated_users mode

			// Always allow access to admins.
			if ( current_user_can( 'create_users' ) )
				return $wp;

			// Allow access if user is member of the current blog
			if (is_user_member_of_blog( get_current_user_id() ))
				return $wp;
			else
				wp_die( '<p>Access denied.</p>', 'Site Access Restricted' );
		}

		// Auth user via CAS
		$cas_user = authenticate_cas_user();

		// User already exists in Wordpress ?
		$user = get_user_by( 'login', $cas_user );
		if ( $user ) {
			// Update user and allow access
			update_and_auth_user($cas_user, $user);

			// Need redirect user after login to make him directly recognized
			wp_redirect( site_url() );
			exit();
		}
		elseif ( $wp_cas_ldap_use_options['who_can_view'] == 'cas_authenticated_users' ) {
			// Allow user only in 'cas_authenticated_users' mode
			return $wp;
		}
		elseif ('yes' === $wp_cas_ldap_use_options['useradd']) {
			// Wordpress user account could be created
			wp_cas_ldap_now_puser( $cas_user );
			return $wp;
		}

		// Deny access
		self :: deny_access();
	}

	/**
	 * Deny access to user : show them the error message. Render as JSON
         * if this is a REST API call; otherwise, show the error message via
         * wp_die() (rendered html), or redirect to the login URL.
         **/
	function deny_access() {
		$deny_access_message = __( 'Access to this site is restricted.', 'wpcasldap' );
		$current_path = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url();
		if ( property_exists( $wp, 'matched_query' ) && stripos( $wp->matched_query, 'rest_route=' ) === 0 && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			wp_send_json(
				array(
					'code'    => 'rest_cannot_view',
					'message' => $deny_access_message,
					'data'    => array(
						'status' => 401,
					),
				)
			);
		}
		else {
			$page_title = sprintf(
				/* TRANSLATORS: %s: Name of blog */
				__( '%s - Access Restricted', 'wpcasldap' ),
				get_bloginfo( 'name' )
			);
			$error_message = apply_filters( 'the_content', $deny_access_message );
			wp_die( wp_kses( $error_message, $this->allowed_html ), esc_html( $page_title ) );
		}

		// Sanity check: we should never get here.
		wp_die( '<p>Access denied.</p>', 'Site Access Restricted' );
	}
}
