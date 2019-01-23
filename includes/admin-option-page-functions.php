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

/**
 * wp_cas_ldap_register_settings function
 */
function wp_cas_ldap_register_settings( ) {
	global $wp_cas_ldap_options;

	$options = array(
		'email_suffix',
		'cas_version',
		'include_path',
		'server_hostname',
		'server_port',
		'server_path',
		'useradd',
		'userrole',
		'ldaphost',
		'ldapport',
		'ldapbasedn',
		'ldapbinddn',
		'ldapbindpwd',
		'useldap',
		'ldap_map_login_attr',
		'ldap_map_email_attr',
		'ldap_map_alt_email_attr',
		'ldap_map_first_name_attr',
		'ldap_map_last_name_attr',
		'ldap_map_role_attr',
		'ldap_map_nickname_attr',
		'ldap_map_nicename_attr',
		'who_can_view',
	);

	foreach ( $options as $o ) {
		if ( ! isset( $wp_cas_ldap_options[ $o ] ) ) {
			switch ( $o ) {
				case 'cas_version':
					$cleaner = 'wp_cas_ldap_one_or_two';
					break;
				case 'useradd':
				case 'useldap':
					$cleaner = 'wp_cas_ldap_yes_or_no';
					break;
				case 'email_suffix':
					$cleaner = 'wp_cas_ldap_strip_at';
					break;
				case 'userrole':
					$cleaner = 'wp_cas_ldap_fix_user_role';
					break;
				case 'server_port':
					$cleaner = 'intval';
					break;
				case 'ldapbindpwd':
					$cleaner = 'wp_cas_ldap_encrypt_ldapbindpwd';
					break;
				case 'ldap_map_login_attr':
				case 'ldap_map_email_attr':
				case 'ldap_map_alt_email_attr':
				case 'ldap_map_first_name_attr':
				case 'ldap_map_last_name_attr':
				case 'ldap_map_role_attr':
				case 'ldap_map_nickname_attr':
				case 'ldap_map_nicename_attr':
					$cleaner = 'wp_cas_ldap_fix_attr_name';
					break;
				case 'who_can_view':
					$cleaner = 'wp_cas_ldap_fix_who_can_view';
					break;
				default:
					$cleaner = 'wp_cas_ldap_dummy';
			}
			register_setting('wpcasldap', 'wpcasldap_' . $o, $cleaner);
		}
	}
}

/**
 * wp_cas_ldap_strip_at function
 *
 * @param string $in domain suffix in email address.
 * @return string domain suffix without '@' symbol.
 */
function wp_cas_ldap_strip_at( $in ) {
	return str_replace( '@', '', $in );
}

/**
 * wp_cas_ldap_yes_or_no function
 *
 * @param string $in value is 'yes' or anything else.
 * @return string value will be 'yes or 'no'.
 */
function wp_cas_ldap_yes_or_no( $in ) {
	return ( 'yes' === strtolower( $in ) ) ? 'yes' : 'no';
}

/**
 * wp_cas_ldap_one_or_two function
 *
 * @param string $in value is '1.0' or anything else.
 * @return string value will be '1.0' or '2.0'.
 */
function wp_cas_ldap_one_or_two( $in ) {
	return ( '1.0' === $in ) ? '1.0' : '2.0';
}

/**
 * wp_cas_ldap_fix_user_role function
 *
 * @param string $in value is 'subscriber', 'contributor', 'author', 'editor',
 *                  'administrator', or anything else.
 * @return string value will be 'subscriber', 'contributor', 'author',
 *                'editor', or 'administrator'.
 */
function wp_cas_ldap_fix_user_role( $in ) {
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
 * wp_cas_ldap_fix_attr_name function
 *
 * @param string $in value is an LDAP attribute name
 * @return string value will be a valid LDAP attribute name
 */
function wp_cas_ldap_fix_attr_name( $in ) {
	return preg_replace('/[^a-zA-Z0-9]/', '', $in);
}

/**
 * wp_cas_ldap_fix_who_can_view function
 *
 * @param string $in value is the who_can_view parameter value
 * @return string value will be a 'cas_authenticated_users',
 *                'wordpress_authenticated_users' or 'everyone'.
 */
function wp_cas_ldap_fix_who_can_view( $in ) {
	if ($in == 'cas_authenticated_users' || $in == 'wordpress_authenticated_users')
		return $in;
	return 'everyone';
}

/**
 * wp_cas_ldap_encrypt_ldapbindpwd function
 *
 * @param string $in value is the LDAP bind plain-password
 * @return string value will be the LDAP bind encrypted password
 */
function wp_cas_ldap_encrypt_ldapbindpwd( $in ) {
	if (strlen($in) > 0)
		return wp_cas_ldapbindpwd :: encrypt($in);
	return $in;
}

/**
 * wp_cas_ldap_dummy function
 *
 * @param string $in domain suffix in email address.
 * @return string domain suffix without @ symbol.
 */
function wp_cas_ldap_dummy( $in ) {
	return $in;
}

/**
 * wp_cas_ldapbindpwd class to encrypt/decrypt LDAP Bind password
 *
 * This mecanism provided from Authorizer Wordpress plugin.
 * Author: Paul Ryan <prar@hawaii.edu>
 * Plugin URI: https://github.com/uhm-coe/authorizer
 * License: GPL2
 * Version: 2.8.6
 */
class wp_cas_ldapbindpwd {
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

}

/**
 * cas_client_settings function hook for WordPress.
 */
function cas_client_settings( ) {
	add_submenu_page(
		'settings.php',
		'CAS Client',
		'CAS Client',
		'manage_network',
		'casclient',
		'wp_cas_ldap_options_page'
	);
}

/**
 * wp_cas_ldap_options_page_add function hook for WordPress.
 */
function wp_cas_ldap_options_page_add( ) {
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
 * wp_cas_ldap_get_options function hook for WordPress.
 *
 * @return array contains plugin configuration options from database.
 */
function wp_cas_ldap_get_options( ) {
	global $wp_cas_ldap_options;
	global $get_options_func;

	$out = array (
		'email_suffix'    		=> $get_options_func( 'wpcasldap_email_suffix' ),
		'cas_version'     		=> $get_options_func( 'wpcasldap_cas_version' ),
		'include_path'    		=> $get_options_func( 'wpcasldap_include_path' ),
		'server_hostname' 		=> $get_options_func( 'wpcasldap_server_hostname' ),
		'server_port'     		=> $get_options_func( 'wpcasldap_server_port' ),
		'server_path'     		=> $get_options_func( 'wpcasldap_server_path' ),
		'useradd'         		=> $get_options_func( 'wpcasldap_useradd' ),
		'userrole'        		=> $get_options_func( 'wpcasldap_userrole' ),
		'ldaphost'        		=> $get_options_func( 'wpcasldap_ldaphost' ),
		'ldapport'        		=> $get_options_func( 'wpcasldap_ldapport' ),
		'useldap'         		=> $get_options_func( 'wpcasldap_useldap' ),
		'ldapbasedn'      		=> $get_options_func( 'wpcasldap_ldapbasedn' ),
		'ldapbinddn'      		=> $get_options_func( 'wpcasldap_ldapbinddn' ),
		'ldapbindpwd'      		=> $get_options_func( 'wpcasldap_ldapbindpwd' ),
		'ldap_map_login_attr'		=> $get_options_func( 'wpcasldap_ldap_map_login_attr', 'samaccountname'),
		'ldap_map_email_attr'		=> $get_options_func( 'wpcasldap_ldap_map_email_attr', 'mail'),
		'ldap_map_alt_email_attr'	=> $get_options_func( 'wpcasldap_ldap_map_alt_email_attr'),
		'ldap_map_first_name_attr'	=> $get_options_func( 'wpcasldap_ldap_map_first_name_attr', 'givenname'),
		'ldap_map_last_name_attr'	=> $get_options_func( 'wpcasldap_ldap_map_last_name_attr', 'sn'),
		'ldap_map_role_attr'		=> $get_options_func( 'wpcasldap_ldap_map_role_attr'),
		'ldap_map_nickname_attr'	=> $get_options_func( 'wpcasldap_ldap_map_nickname_attr', 'cn'),
		'ldap_map_nicename_attr'	=> $get_options_func( 'wpcasldap_ldap_map_nicename_attr'),
		'who_can_view'			=> $get_options_func( 'wpcasldap_who_can_view', 'everyone'),
	);

	if ( is_array( $wp_cas_ldap_options ) && 0 < count( $wp_cas_ldap_options ) ) {
		foreach ( $wp_cas_ldap_options as $key => $val ) {
			$out[ $key ] = $val;
		}
	}
	return $out;
}
