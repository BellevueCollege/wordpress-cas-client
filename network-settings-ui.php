<?php
/**
 * update_network_settings function
 */
function update_network_settings( ) {
	//saving CAS network settings
	if ( isset( $_POST['wpcasldap_server_hostname'] ) ) {
		$cas_version     = $_POST['wpcasldap_cas_version'];
		$include_path    = $_POST['wpcasldap_include_path'];
		$server_hostname = $_POST['wpcasldap_server_hostname'];
		$server_port     = $_POST['wpcasldap_server_port'];
		$server_path     = $_POST['wpcasldap_server_path'];
		$user_add        = $_POST['wpcasldap_useradd'];
		$user_role       = $_POST['wpcasldap_userrole'];
		$ldap_host       = $_POST['wpcasldap_ldaphost'];
		$ldap_port       = $_POST['wpcasldap_ldapport'];
		$use_ldap        = $_POST['wpcasldap_useldap'];
		$ldap_base_dn    = $_POST['wpcasldap_ldapbasedn'];

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
	}
}
