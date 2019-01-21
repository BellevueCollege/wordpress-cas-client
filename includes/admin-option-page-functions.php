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
		'useldap',
		'ldap_map_login_attr',
		'ldap_map_email_attr',
		'ldap_map_alt_email_attr',
		'ldap_map_first_name_attr',
		'ldap_map_last_name_attr',
		'ldap_map_role_attr',
		'ldap_map_nickname_attr',
		'ldap_map_nicename_attr',
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
 * wp_cas_ldap_dummy function
 *
 * @param string $in domain suffix in email address.
 * @return string domain suffix without @ symbol.
 */
function wp_cas_ldap_dummy( $in ) {
	return $in;
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
		'ldap_map_login_attr'		=> $get_options_func( 'wpcasldap_ldap_map_login_attr', 'samaccountname'),
		'ldap_map_email_attr'		=> $get_options_func( 'wpcasldap_ldap_map_email_attr', 'mail'),
		'ldap_map_alt_email_attr'	=> $get_options_func( 'wpcasldap_ldap_map_alt_email_attr'),
		'ldap_map_first_name_attr'	=> $get_options_func( 'wpcasldap_ldap_map_first_name_attr', 'givenname'),
		'ldap_map_last_name_attr'	=> $get_options_func( 'wpcasldap_ldap_map_last_name_attr', 'sn'),
		'ldap_map_role_attr'		=> $get_options_func( 'wpcasldap_ldap_map_role_attr'),
		'ldap_map_nickname_attr'	=> $get_options_func( 'wpcasldap_ldap_map_nickname_attr', 'cn'),
		'ldap_map_nicename_attr'	=> $get_options_func( 'wpcasldap_ldap_map_nicename_attr'),
	);

	if ( is_array( $wp_cas_ldap_options ) && 0 < count( $wp_cas_ldap_options ) ) {
		foreach ( $wp_cas_ldap_options as $key => $val ) {
			$out[ $key ] = $val;
		}
	}
	return $out;
}
