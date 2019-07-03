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

require_once constant( 'CAS_CLIENT_ROOT' ) . '/includes/wp-cas-ldap-options-page.php';

class wp_cas_ldap_settings {

	private static $options = array(
		'cas_version' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_cas_version' ),
		),
		'include_path' => array (),
		'server_hostname' => array (),
		'server_port' => array (
			'type' => 'integer',
			'sanitize_callback' => 'intval',
			'default' => 443,
		),
		'server_path' => array (),
		'disable_cas_logout' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_yes_or_no' ),
		),
		'cas_redirect_using_js' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_yes_or_no' ),
		),
		'useradd' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_yes_or_no' ),
		),
		'email_suffix' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_strip_at' ),
		),
		'userrole' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_user_role' ),
		),
		'ldaphost' => array (),
		'ldapport' => array (
			'type' => 'integer',
			'sanitize_callback' => 'intval',
			'default' => 389,
		),
		'ldapbasedn' => array (),
		'ldapbinddn' => array (),
		'ldapbindpwd' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_encrypt_ldapbindpwd' ),
		),
		'useldap' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_yes_or_no' ),
		),
		'ldap_map_login_attr' => array (
			'default' => 'samaccountname',
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_email_attr' => array (
			'default' => 'mail',
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_alt_email_attr' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_first_name_attr' => array (
			'default' => 'givenname',
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_last_name_attr' => array (
			'default' => 'sn',
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_role_attr' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_nickname_attr' => array (
			'default' => 'cn',
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_nicename_attr' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'ldap_map_affiliations_attr' => array (
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_ldap_attr_name'),
		),
		'who_can_view' => array (
			'default' => 'everyone',
			'sanitize_callback' => array( 'wp_cas_ldap_settings', 'sanitize_who_can_view'),
		),
		'access_denied_redirect_url' => array (),
	);

	/**
	 * get_options function hook for WordPress.
	 *
	 * @return array contains plugin configuration options from database.
	 */
	public static function get_options( ) {
		global $wp_cas_ldap_options;

		$out = array ();
		foreach (self :: $options as $opt => $opt_args) {
			if (is_array($wp_cas_ldap_options) && array_key_exists($opt, $wp_cas_ldap_options)) {
				$out[ $opt ] = $wp_cas_ldap_options[ $opt ];
			}
			elseif ( self :: is_enabled_for_network( ) ) {
				$out[ $opt ] = get_site_option (
					"wpcasldap_$opt",
					(isset($opt_args['default'])?$opt_args['default']:false)
				);
			}
			else {
				$out[ $opt ] = get_option (
					"wpcasldap_$opt",
					(isset($opt_args['default'])?$opt_args['default']:false)
				);
			}
		}

		return $out;
	}

	/**
	 * get_option_sanitizer method
	 *
	 * @param string $option the setting option name.
	 * @return string the sanitize_callback to use for this setting option
	 **/
	public static function get_option_sanitizer($option) {
		if (isset(self :: $options[$option]) && isset(self :: $options[$option]['sanitize_callback'])) {
			if (is_callable(self :: $options[$option]['sanitize_callback']))
				return self :: $options[$option]['sanitize_callback'];
		}
		return array('wp_cas_ldap_settings', 'wp_cas_ldap_dummy');
	}


	/**
	 * Options settings sanitizers
	 **/

	/**
	 * sanitize_strip_at method
	 *
	 * @param string $in domain suffix in email address.
	 * @return string domain suffix without '@' symbol.
	 */
	function sanitize_strip_at( $in ) {
		return str_replace( '@', '', $in );
	}

	/**
	 * sanitize_yes_or_no method
	 *
	 * @param string $in value is 'yes' or anything else.
	 * @return string value will be 'yes or 'no'.
	 */
	function sanitize_yes_or_no( $in ) {
		return ( 'yes' === strtolower( $in ) ) ? 'yes' : 'no';
	}

	/**
	 * sanitize_one_or_two method
	 *
	 * @param string $in value is '1.0' or anything else.
	 * @return string value will be '1.0' or '2.0'.
	 */
	function sanitize_cas_version( $in ) {
		return ( '1.0' === $in ) ? '1.0' : '2.0';
	}

	/**
	 * sanitize_user_role method
	 *
	 * @param string $in value is 'subscriber', 'contributor', 'author', 'editor',
	 *                  'administrator', or anything else.
	 * @return string value will be 'subscriber', 'contributor', 'author',
	 *                'editor', or 'administrator'.
	 */
	function sanitize_user_role( $in ) {
		$roles = array(
			'subscriber',
			'contributor',
			'author',
			'editor',
			'administrator',
		);
		if ( in_array( $in, $roles ) ) {
			return $in;
		} else {
			return 'subscriber';
		}
	}

	/**
	 * sanitize_attr_name method
	 *
	 * @param string $in value is an LDAP attribute name
	 * @return string value will be a valid LDAP attribute name
	 */
	function sanitize_ldap_attr_name( $in ) {
		return preg_replace('/[^a-zA-Z0-9]/', '', $in);
	}

	/**
	 * sanitize_who_can_view method
	 *
	 * @param string $in value is the who_can_view parameter value
	 * @return string value will be a 'cas_authenticated_users',
	 *                'wordpress_authenticated_users' or 'everyone'.
	 */
	function sanitize_who_can_view( $in ) {
		if ($in == 'cas_authenticated_users' || $in == 'wordpress_authenticated_users')
			return $in;
		return 'everyone';
	}

	/**
	 * sanitize_dummy function
	 *
	 * @param string $in input value
	 * @return string unchange input value
	 */
	function wp_cas_ldap_dummy( $in ) {
		return $in;
	}

	/**
	 * sanitize_encrypt_ldapbindpwd method
	 *
	 * @param string $in value is the LDAP bind plain-password
	 * @return string value will be the LDAP bind encrypted password
	 */
	function sanitize_encrypt_ldapbindpwd( $in ) {
		if (strlen($in) > 0)
			return self :: encrypt($in);
		return $in;
	}
	
	/**
	 * Methods to encrypt/decrypt LDAP Bind password
	 *
	 * This mecanism provided from Authorizer Wordpress plugin.
	 * Author: Paul Ryan <prar@hawaii.edu>
	 * Plugin URI: https://github.com/uhm-coe/authorizer
	 * License: GPL2
	 * Version: 2.8.6
	 */

	/**
	 * Encryption key (not secret!).
	 *
	 * @var string
	 */
	private static $key = 'ka1Ieku&vaeng5pais#o9Air';

	/**
	 * Encryption salt (not secret!).
	 *
	 * @var string
	 */
	private static $iv = 'Eob1Sie8aK5zai9Iech/eyu6';

	/**
	 * Basic encryption using a public (not secret!) key. Used for general
	 * database obfuscation of passwords.
	 *
	 * @param  string $text    String to encrypt.
	 * @param  string $library Encryption library to use (openssl).
	 * @return string	  Encrypted string.
	 */
	public static function encrypt( $text, $library = 'openssl' ) {
		$result = '';

		// Use openssl library (better) if it is enabled.
		if ( function_exists( 'openssl_encrypt' ) && 'openssl' === $library ) {
			$result = base64_encode(
				openssl_encrypt(
					$text,
					'AES-256-CBC',
					hash( 'sha256', self::$key ),
					0,
					substr( hash( 'sha256', self::$iv ), 0, 16 )
				)
			);
		} elseif ( function_exists( 'mcrypt_encrypt' ) ) { // Use mcrypt library (deprecated in PHP 7.1) if php5-mcrypt extension is enabled.
			$result = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, self::$key, $text, MCRYPT_MODE_ECB, 'abcdefghijklmnopqrstuvwxyz012345' ) );
		} else { // Fall back to basic obfuscation.
			$length = strlen( $text );
			for ( $i = 0; $i < $length; $i++ ) {
				$char    = substr( $text, $i, 1 );
				$keychar = substr( self::$key, ( $i % strlen( self::$key ) ) - 1, 1 );
				$char    = chr( ord( $char ) + ord( $keychar ) );
				$result .= $char;
			}
			$result = base64_encode( $result );
		}

		return $result;
	}


	/**
	 * Basic decryption using a public (not secret!) key. Used for general
	 * database obfuscation of passwords.
	 *
	 * @param  string $secret  String to encrypt.
	 * @param  string $library Encryption lib to use (openssl).
	 * @return string	  Decrypted string
	 */
	public static function decrypt( $secret, $library = 'openssl' ) {
		$result = '';

		// Use openssl library (better) if it is enabled.
		if ( function_exists( 'openssl_decrypt' ) && 'openssl' === $library ) {
			$result = openssl_decrypt(
				base64_decode( $secret ),
				'AES-256-CBC',
				hash( 'sha256', self::$key ),
				0,
				substr( hash( 'sha256', self::$iv ), 0, 16 )
			);
		} elseif ( function_exists( 'mcrypt_decrypt' ) ) { // Use mcrypt library (deprecated in PHP 7.1) if php5-mcrypt extension is enabled.
			$secret = base64_decode( $secret );
			$result = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, self::$key, $secret, MCRYPT_MODE_ECB, 'abcdefghijklmnopqrstuvwxyz012345' ), "\0$result" );
		} else { // Fall back to basic obfuscation.
			$secret = base64_decode( $secret );
			$length = strlen( $secret );
			for ( $i = 0; $i < $length; $i++ ) {
				$char    = substr( $secret, $i, 1 );
				$keychar = substr( self::$key, ( $i % strlen( self::$key ) ) - 1, 1 );
				$char    = chr( ord( $char ) - ord( $keychar ) );
				$result .= $char;
			}
		}

		return $result;
	}

	/**
	 * Detect if plugin is enabled for the network or a site
	 **/
	public static function is_enabled_for_network($plugin = 'wordpress-cas-client') {
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		return is_plugin_active_for_network('wordpress-cas-client/wordpress-cas-client.php');
	}

	/**
	 * update_network_settings method
	 *
	 * Save network settings
	 */
	public static function update_network_settings( ) {
		// Stop silently if current user doesn't have permissions.
		if ( ! current_user_can( 'manage_network_options' ) )
			return false;

		// Check if admin form is posted
		if ( isset( $_POST['wpcasldap_server_hostname'] ) ) {
			foreach (self :: $options as $opt => $opt_args) {
				$sanitizer = self :: get_option_sanitizer($opt);
				$value = call_user_func($sanitizer, (isset($_POST["wpcasldap_$opt"])?$_POST["wpcasldap_$opt"]:""));
				update_site_option( 'wpcasldap_'.$opt, $value);
			}
		}
	}

	/**
	 * Wordpress hook methods
	 **/

	/**
	 * register_settings method
	 *
	 * Use as admin_init hook for WordPress.
	 */
	public static function register_settings( ) {
		global $wp_cas_ldap_options;

		foreach ( self :: $options as $option => $option_args ) {
			if ( ! isset( $wp_cas_ldap_options[ $option ] ) ) {
				register_setting('wpcasldap', 'wpcasldap_' . $option, $option_args);
			}
		}
	}

	/**
	 * add_cas_client_admin_menu method
	 *
	 * Use as admin_menu hook for WordPress.
	 */
	public static function add_cas_client_admin_menu( ) {
		if ( function_exists( 'add_management_page' ) ) {
			add_submenu_page(
			  'options-general.php',
				'CAS Client',
				'CAS Client',
				CAPABILITY,
				'wpcasldap',
				'wp_cas_ldap_options_page'
			);
		} else {
			add_options_page(
				'CAS Client',
				'CAS Client',
				CAPABILITY,
				basename(__FILE__),
				'wp_cas_ldap_options_page'
			);
		}
	}

	/**
	 * add_cas_client_network_admin_menu method
	 *
	 * Use as network_admin_menu hook for WordPress.
	 */
	public static function add_cas_client_network_admin_menu( ) {
		add_submenu_page(
			'settings.php',
			'CAS Client',
			'CAS Client',
			'manage_network',
			'casclient',
			'wp_cas_ldap_options_page'
		);

		// Handle admin form POST request
		self :: update_network_settings();
	}
}
