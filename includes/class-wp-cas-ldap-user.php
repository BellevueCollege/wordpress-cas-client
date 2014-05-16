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
	private $data = null;

	/**
	 * __construct method for WP_CAS_LDAP_User class
	 *
	 * @param array $member_array information about the ldap user.
	 */
	function __construct( $member_array ) {
		$this->data = $member_array;
	}

	/**
	 * get_user_name method for WP_CAS_LDAP_User class
	 *
	 * @return string|false returns 'cn' value fromprivate $data array.
	 */
	function get_user_name( ) {
		if ( isset( $this->data[0]['cn'][0] ) ) {
			return $this->data[0]['cn'][0];
		} else {
			return false;
		}
	}

	/**
	 * get_user_data method for WP_CAS_LDAP_User class
	 *
	 * @return false|array
	 */
	function get_user_data( ) {
		global $wp_cas_ldap_use_options;
		if ( isset( $this->data[0]['uid'][0] ) || isset( $this->data[0]['employeeid'][0] ) ) {
			$user_role = '';
			$user_nice_name = sanitize_title_with_dashes( $this->data[0]['samaccountname'][0] );
			if ( isset( $this->data[0]['employeeid'][0] ) ) {
				$user_role = $GLOBALS['defaultEmployeeUserrole'];
			} else {
				$user_role = $GLOBALS['defaultStudentUserrole'];
			}
			return array(
				'user_login'    => $this->data[0]['samaccountname'][0],
				'user_pass'     => generate_password( 32, 64 ),
				'user_email'    => $this->data[0]['mail'][0],
				'first_name'    => $this->data[0]['givenname'][0],
				'last_name'     => $this->data[0]['sn'][0],
				'role'          => $user_role,
				'nickname'      => $this->data[0]['cn'][0],
				'user_nicename' => $user_nice_name,
			);
		} else {
			return false;
		}
	}
}
