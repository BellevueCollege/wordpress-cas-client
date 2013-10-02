<?php
if (file_exists( dirname(__FILE__).'/cas-password-encryption.php' ) ) 
	include_once( dirname(__FILE__).'/cas-password-encryption.php' ); 

	function updateSettings()
	{
		//saving CAS network settings
		$update_function = "update_option";
		if(is_multisite())
			$update_function = "update_site_option";
		if(isset($_POST['wpcasldap_include_path']))
		{
			error_log("post================");
			
			if(isset($_POST['wpcasldap_cas_version']))
				$update_function('wpcasldap_cas_version',$_POST['wpcasldap_cas_version']);
			
			if(isset($_POST['wpcasldap_include_path']))
				$update_function('wpcasldap_include_path',$_POST['wpcasldap_include_path']);
			
			if(isset($_POST['wpcasldap_casserver']))
				 $update_function('wpcasldap_casserver',$_POST['wpcasldap_casserver']);

			if(isset($_POST['wpcasldap_useradd']))
				 $update_function('wpcasldap_useradd',$_POST['wpcasldap_useradd']);

			if(isset($_POST['wpcasldap_userrole']))
				 $update_function('wpcasldap_userrole',$_POST['wpcasldap_userrole']);

			if(isset($_POST['wpcasldap_ldapuri']))
				 $update_function('wpcasldap_ldapuri',$_POST['wpcasldap_ldapuri']);

			if(isset($_POST['wpcasldap_useldap']))
				 $update_function('wpcasldap_useldap',$_POST['wpcasldap_useldap']);

			if(isset($_POST['wpcasldap_ldapbasedn']))
				 $update_function('wpcasldap_ldapbasedn',$_POST['wpcasldap_ldapbasedn']);

			if(isset($_POST['wpcasldap_ldapuser']))
				 $update_function('wpcasldap_ldapuser',$_POST['wpcasldap_ldapuser']);

			if(isset($_POST['wpcasldap_email_suffix']))
				 $update_function('wpcasldap_email_suffix',$_POST['wpcasldap_email_suffix']);
			
			//Encrypt password
			if(isset($_POST['wpcasldap_ldappassword']))
			{
				$ldappassword = $_POST['wpcasldap_ldappassword'];
				$ldappassword = wpcasclient_encrypt($ldappassword,$GLOBALS['ciphers']);
				$update_function('wpcasldap_ldappassword',$ldappassword);
			}





			//$casorldap_attribute = $_POST['wpcasldap_casorldap_attribute'];	

			//CAS Attributes
			/*
			$casatt_name = $_POST['wpcasldap_casatt_name'];	
			$casatt_operator = $_POST['wpcasldap_casatt_operator'];	
			$casatt_user_value_to_compare = $_POST['wpcasldap_casatt_user_value_to_compare'];	
			$casatt_wp_role = $_POST['wpcasldap_casatt_wp_role'];	
			$casatt_wp_site = $_POST['wpcasldap_casatt_wp_site'];	
			*/
			
			//LDAP Attributes
			/*
			$ldap_query = $_POST['wpcasldap_ldap_query'];
			$ldap_operator = $_POST['wpcasldap_ldap_operator'];
			$ldap_user_value_to_compare = $_POST['wpcasldap_ldap_user_value_to_compare'];
			$ldap_wp_role = $_POST['wpcasldap_ldap_wp_role'];
			$ldap_wp_site = $_POST['wpcasldap_ldap_wp_site'];
			*/

			 /*
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
			 */
		}
	}
?>