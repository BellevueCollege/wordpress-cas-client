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
 * WP_CAS_LDAP_User class
 */
class WP_CAS_LDAP_User {
	private $dn = null;
	private $attributes = array();

	/**
	 * __construct method for WP_CAS_LDAP_User class
	 *
	 * @param array $entry informations about the ldap user.
	 */
	function __construct( $dn, $attributes ) {
		$this -> dn = $dn;
		if (is_array($attributes)) {
			foreach ($attributes as $attr => $values) {
				$this -> attributes[strtolower($attr)] = $values;
			}
		}
	}

	/**
	 * get_user_name method for WP_CAS_LDAP_User class
	 *
	 * @return string|false returns the user name from private $attributes array.
	 */
	function get_user_name( ) {
		global $wp_cas_ldap_use_options;
		return $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_nicename_attr'], $wp_cas_ldap_use_options['ldap_map_nickname_attr']);
	}

	/**
	 * get_user_data method for WP_CAS_LDAP_User class
	 *
	 * @return false|array
	 */
	function get_user_data( ) {
		global $wp_cas_ldap_use_options;
		if ( $wp_cas_ldap_use_options['ldap_map_login_attr'] && $this->get_user_attr($wp_cas_ldap_use_options['ldap_map_login_attr']) ) {
			return array(
				'user_login'    => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_login_attr']),
				'user_pass'     => generate_password( 32, 64 ),
				'user_email'    => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_email_attr'], $wp_cas_ldap_use_options['ldap_map_alt_email_attr']),
				'first_name'    => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_first_name_attr']),
				'last_name'     => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_last_name_attr']),
				'role'          => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_role_attr'], null, $wp_cas_ldap_use_options['userrole']),
				'nickname'      => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_nickname_attr']),
				'user_nicename' => $this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_nicename_attr'], null, sanitize_title_with_dashes($this -> get_user_attr($wp_cas_ldap_use_options['ldap_map_login_attr']))),
			);
		} else {
			return false;
		}
	}

	/**
	 * get_user_attr method for WP_CAS_LDAP_User class
	 *
	 * @return string|null
	 */
	function get_user_attr($attr, $alt_attr=null, $default_value=null) {
		$attr = ($attr?strtolower($attr):null);
		$alt_attr = ($alt_attr?strtolower($alt_attr):null);
		if($attr && isset($this->attributes[$attr]) && !empty($this->attributes[$attr])) {
			return $this->attributes[$attr][0];
		}
		elseif($alt_attr && isset($this->attributes[$alt_attr]) && !empty($this->attributes[$alt_attr])) {
			return $this->attributes[$alt_attr][0];
		}
		else {
			return $default_value;
		}
	}
}
