<?php
if (file_exists( dirname(__FILE__).'/cas-password-encryption.php' ) ) 
	include_once( dirname(__FILE__).'/cas-password-encryption.php' ); 

	function updateNetworkSettings()
	{
		//saving CAS network settings
		if(isset($_POST['wpcasldap_include_path']))
		{
			$cas_version = $_POST['wpcasldap_cas_version'];
			$include_path = $_POST['wpcasldap_include_path'];
			$casserver = $_POST['wpcasldap_casserver'];
			error_log("cas server url :".$casserver);
			//$server_hostname = $_POST['wpcasldap_server_hostname'];
			//$server_port = $_POST['wpcasldap_server_port'];
			//$server_path = $_POST['wpcasldap_server_path'];
			$useradd = $_POST['wpcasldap_useradd'];
			$userrole = $_POST['wpcasldap_userrole'];
			//$ldaphost = $_POST['wpcasldap_ldaphost'];
			//$ldapport = $_POST['wpcasldap_ldapport'];
			$ldapuri = $_POST['wpcasldap_ldapuri'];
			$useldap = $_POST['wpcasldap_useldap'];
			$ldapbasedn = $_POST['wpcasldap_ldapbasedn'];	
			$ldapuser = $_POST['wpcasldap_ldapuser'];	
			$ldappassword = $_POST['wpcasldap_ldappassword'];
			//Encrypt password
			$ldappassword = wpcasclient_encrypt($ldappassword,$GLOBALS['ciphers']);





			$casorldap_attribute = $_POST['wpcasldap_casorldap_attribute'];	

			//CAS Attributes
			$casatt_name = $_POST['wpcasldap_casatt_name'];	
			$casatt_operator = $_POST['wpcasldap_casatt_operator'];	
			$casatt_user_value_to_compare = $_POST['wpcasldap_casatt_user_value_to_compare'];	
			$casatt_wp_role = $_POST['wpcasldap_casatt_wp_role'];	
			$casatt_wp_site = $_POST['wpcasldap_casatt_wp_site'];	

			
			//LDAP Attributes
			$ldap_query = $_POST['wpcasldap_ldap_query'];
			$ldap_operator = $_POST['wpcasldap_ldap_operator'];
			$ldap_user_value_to_compare = $_POST['wpcasldap_ldap_user_value_to_compare'];
			$ldap_wp_role = $_POST['wpcasldap_ldap_wp_role'];
			$ldap_wp_site = $_POST['wpcasldap_ldap_wp_site'];

			 update_site_option('wpcasldap_cas_version',$cas_version);
			 update_site_option('wpcasldap_include_path',$include_path);
			  update_site_option('wpcasldap_casserver',$casserver);
			 //update_site_option('wpcasldap_server_hostname',$server_hostname);
			 //update_site_option('wpcasldap_server_port',$server_port);
			 //update_site_option('wpcasldap_server_path',$server_path);
			 update_site_option('wpcasldap_useradd',$useradd);
			 update_site_option('wpcasldap_userrole',$userrole);
			 update_site_option('wpcasldap_ldapuri',$ldapuri);
			 //update_site_option('wpcasldap_ldaphost',$ldaphost);
			 //update_site_option('wpcasldap_ldapport',$ldapport);
			 update_site_option('wpcasldap_useldap',$useldap);
			 update_site_option('wpcasldap_ldapbasedn',$ldapbasedn);
			 update_site_option('wpcasldap_ldapuser',$ldapuser);

			 update_site_option('wpcasldap_ldappassword',$ldappassword);

			 update_site_option('wpcasldap_casorldap_attribute',$casorldap_attribute);
			 update_site_option('wpcasldap_casatt_name',$casatt_name);
			 update_site_option('wpcasldap_casatt_operator',$casatt_operator);
			 update_site_option('wpcasldap_casatt_user_value_to_compare',$casatt_user_value_to_compare);
			 update_site_option('wpcasldap_casatt_wp_role',$casatt_wp_role);
			 update_site_option('wpcasldap_casatt_wp_site',$casatt_wp_site);

			 update_site_option('wpcasldap_ldap_query',$ldap_query);
			 update_site_option('wpcasldap_ldap_operator',$ldap_operator);
			 update_site_option('wpcasldap_ldap_user_value_to_compare',$ldap_user_value_to_compare);
			 update_site_option('wpcasldap_ldap_wp_role',$ldap_wp_role);
			 update_site_option('wpcasldap_ldap_wp_site',$ldap_wp_site);
			 
		}
	}
?>