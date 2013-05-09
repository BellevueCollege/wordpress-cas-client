<?php
/*
// Optional configuration file for wpCASLDAP plugin
// 
// Settings in this file override any options set in the 
// wpCASLDAP menu in Options. Any settings added to the
// $wpcasldap_options array, will not show up on the
// Options Page. 
//
// I would suggest commenting out the settings you want 
// to appear on the options page.
//
*/


// the configuration array
$wpcasldap_options = array (
	'cas_version' => '2.0',
	'include_path' => '/absolute/path/to/CAS.php',
	'server_hostname' => 'server.university.edu',
	'server_port' => '443',
	'server_path' => '/url-path/',

	'ldaphost' => 'server.university.edu',
	'ldapport' => '389',
	'ldapbasedn' => 'o=university.edu',

	'useradd' => 'yes',
	'useldap' => 'yes',
	'email_suffix' => 'mailserver.university.edu',
	'userrole' => 'subscriber'
);
		
?>
