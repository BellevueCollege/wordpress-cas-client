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

/**
 * update_network_settings function
 */
function update_network_settings( ) {
	//saving CAS network settings
	if ( isset( $_POST['wpcasldap_server_hostname'] ) ) {
		$cas_version     		= $_POST['wpcasldap_cas_version'];
		$include_path    		= $_POST['wpcasldap_include_path'];
		$server_hostname 		= $_POST['wpcasldap_server_hostname'];
		$server_port     		= $_POST['wpcasldap_server_port'];
		$server_path     		= $_POST['wpcasldap_server_path'];
		$user_add        		= $_POST['wpcasldap_useradd'];
		$user_role       		= $_POST['wpcasldap_userrole'];
		$ldap_host       		= $_POST['wpcasldap_ldaphost'];
		$ldap_port       		= $_POST['wpcasldap_ldapport'];
		$use_ldap        		= $_POST['wpcasldap_useldap'];
		$ldap_base_dn    		= $_POST['wpcasldap_ldapbasedn'];
		$ldap_bind_dn    		= $_POST['wpcasldap_ldapbinddn'];
		$ldap_bind_pwd    		= wp_cas_ldap_encrypt_ldapbindpwd($_POST['wpcasldap_ldapbindpwd']);
		$ldap_map_login_attr		= $_POST['ldap_map_login_attr'];
		$ldap_map_email_attr		= $_POST['ldap_map_email_attr'];
		$ldap_map_alt_email_attr	= $_POST['ldap_map_alt_email_attr'];
		$ldap_map_first_name_attr	= $_POST['ldap_map_first_name_attr'];
		$ldap_map_last_name_attr	= $_POST['ldap_map_last_name_attr'];
		$ldap_map_role_attr		= $_POST['ldap_map_role_attr'];
		$ldap_map_nickname_attr		= $_POST['ldap_map_nickname_attr'];
		$ldap_map_nicename_attr		= $_POST['ldap_map_nicename_attr'];
		$who_can_view			= $_POST['who_can_view'];

		update_site_option( 'wpcasldap_cas_version', $cas_version );
		update_site_option( 'wpcasldap_include_path', $include_path );
		update_site_option( 'wpcasldap_server_hostname', $server_hostname );
		update_site_option( 'wpcasldap_server_port', $server_port );
		update_site_option( 'wpcasldap_server_path', $server_path );
		update_site_option( 'wpcasldap_useradd', $user_add );
		update_site_option( 'wpcasldap_userrole', $user_role );
		update_site_option( 'wpcasldap_ldaphost', $ldap_host );
		update_site_option( 'wpcasldap_ldapport', $ldap_port );
		update_site_option( 'wpcasldap_useldap', $use_ldap );
		update_site_option( 'wpcasldap_ldapbasedn', $ldap_base_dn );
		update_site_option( 'wpcasldap_ldapbinddn', $ldap_bind_dn );
		update_site_option( 'wpcasldap_ldapbindpwd', $ldap_bind_pwd );
		update_site_option( 'ldap_map_login_attr', $ldap_map_login_attr );
		update_site_option( 'ldap_map_email_attr', $ldap_map_email_attr );
		update_site_option( 'ldap_map_alt_email_attr', $ldap_map_alt_email_attr );
		update_site_option( 'ldap_map_first_name_attr', $ldap_map_first_name_attr );
		update_site_option( 'ldap_map_last_name_attr', $ldap_map_last_name_attr );
		update_site_option( 'ldap_map_role_attr', $ldap_map_role_attr );
		update_site_option( 'ldap_map_nickname_attr', $ldap_map_nickname_attr );
		update_site_option( 'ldap_map_nicename_attr', $ldap_map_nicename_attr );
		update_site_option( 'who_can_view', $who_can_view );

	}
}
