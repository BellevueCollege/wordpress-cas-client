<?php
	function updateNetworkSettings()
	{
		//saving CAS network settings
		if(isset($_POST['wpcasldap_server_hostname']))
		{
			$cas_version = $_POST['wpcasldap_cas_version'];
			$include_path = $_POST['wpcasldap_include_path'];
			$server_hostname = $_POST['wpcasldap_server_hostname'];
			$server_port = $_POST['wpcasldap_server_port'];
			$server_path = $_POST['wpcasldap_server_path'];
			$useradd = $_POST['wpcasldap_useradd'];
			$userrole = $_POST['wpcasldap_userrole'];
			$ldaphost = $_POST['wpcasldap_ldaphost'];
			$ldapport = $_POST['wpcasldap_ldapport'];
			$useldap = $_POST['wpcasldap_useldap'];
			$ldapbasedn = $_POST['wpcasldap_ldapbasedn'];

			 update_site_option('wpcasldap_cas_version',$cas_version);
			 update_site_option('wpcasldap_include_path',$include_path);
			 update_site_option('wpcasldap_server_hostname',$server_hostname);
			 update_site_option('wpcasldap_server_port',$server_port);
			 update_site_option('wpcasldap_server_path',$server_path);
			 update_site_option('wpcasldap_useradd',$useradd);
			 update_site_option('wpcasldap_userrole',$userrole);
			 update_site_option('wpcasldap_ldaphost',$ldaphost);
			 update_site_option('wpcasldap_ldapport',$ldapport);
			 update_site_option('wpcasldap_useldap',$useldap);
			 update_site_option('wpcasldap_ldapbasedn',$ldapbasedn);
		}
	}
?>