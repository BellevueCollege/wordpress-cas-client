<?php
/*
Plugin Name: WordPress CAS Client
Plugin URI: https://github.com/BellevueCollege/wordpress-cas-client
Description: Integrates WordPress with existing <a href="http://en.wikipedia.org/wiki/Central_Authentication_Service">CAS</a> single sign-on architectures. Additionally this plugin can use a LDAP server (such as Active Directory) for populating user information after the user has successfully logged on to WordPress. This plugin is a fork of the <a href="http://wordpress.org/extend/plugins/wpcas-w-ldap">wpCAS-w-LDAP</a> plugin.
Version: 1.2.0.0
Author: Bellevue College
Author URI: http://www.bellevuecollege.edu
License: GPL2
*/

/* 
 Copyright (C) 2009 Ioannis C. Yessios

 This plugin owes a huge debt to 
 Casey Bisson's wpCAS, copyright (C) 2008
 and released under GPL.
 http://wordpress.org/extend/plugins/wpcasldap/

 Casey Bisson's plugin owes a huge debt to Stephen Schwink's CAS Authentication plugin, copyright (C) 2008 
 and released under GPL. 
 http://wordpress.org/extend/plugins/cas-authentication/

 It also borrowed a few lines of code from Jeff Johnson's SoJ CAS/LDAP Login plugin
 http://wordpress.org/extend/plugins/soj-casldap/

 This plugin honors and extends Bisson's and Schwink's work, and is licensed under the same terms.

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA	 02111-1307	 USA 
*/

// include common functions, etc.

define("CAPABILITY","edit_themes");
define("CAS_DEFAULT_PORT",'443');
define("CAS_DEFAULT_PATH","/");
define("SCHEME","https://");
define("DEFAULT_CASFILE_PATH", dirname(__FILE__).'/CAS/CAS.php');
// This global variable is set to either 'get_option' or 'get_site_option' depending on multisite option value
global $get_options_func ;
//This global variable is defaulted to 'options.php' , but for network setting we want the form to submit to itself, so we will leave it empty
global $form_action;

include_once(dirname(__FILE__)."/utilities.php");
include_once(dirname(__FILE__) . "/casManager.php");
include_once(dirname(__FILE__) . "/ldapManager.php");
include_once(dirname(__FILE__) . "/ldapUser.php");

if (file_exists( dirname(__FILE__).'/config.php' ) )
    /** @noinspection PhpIncludeInspection */
    include_once( dirname(__FILE__).'/config.php' ); // attempt to fetch the optional config file

if (file_exists( dirname(__FILE__).'/cas-server-ui.php' ) ) 
	include_once( dirname(__FILE__).'/cas-server-ui.php' ); // attempt to fetch the optional config file 


if (file_exists( dirname(__FILE__).'/cas-password-encryption.php' ) ) 
	include_once( dirname(__FILE__).'/cas-password-encryption.php' ); 

// helps separate debug output
debug_log("================= Executing wordpress-cas-client.php ===================\n");

if (file_exists( dirname(__FILE__).'/cas-client-ui.php' ) ) 
	include_once( dirname(__FILE__).'/cas-client-ui.php' ); // attempt to fetch the optional config file

$get_options_func = "get_option";
updateSettings();
	if(is_multisite())
	{
		
		add_action( 'network_admin_menu', 'cas_client_settings' );
		debug_log("multisite true");
		$get_options_func = "get_site_option";
	}
	debug_log("version :". $get_options_func('wpcasldap_cas_version'));
	debug_log("version :". $get_options_func('wpcasldap_server_hostname'));

global $wpcasldap_options;
if($wpcasldap_options)
{
	if (!is_array($wpcasldap_options))
		$wpcasldap_optons = array();
}

$wpcasldap_use_options = wpcasldap_getoptions();

global $ldapManager;
$ldapManager = new ldapManager($wpcasldap_use_options['ldapuser'], $wpcasldap_use_options['ldappassword'], $wpcasldap_options['ldapuri']);

error_log("options :".print_r($wpcasldap_use_options,true));


// plugin hooks into authentication system
add_action('wp_authenticate', array('casManager', 'authenticate'), 10, 2);
add_action('wp_logout', array('casManager', 'logout'));
add_action('lost_password', array('casManager', 'disable_function'));
add_action('retrieve_password', array('casManager', 'disable_function'));
add_action('password_reset', array('casManager', 'disable_function'));
add_filter('show_password_fields', array('casManager', 'show_password_fields'));


if (is_admin() && !is_multisite()) {// Added condition not multisite because if multisite is true thn it should only show the settings in network admin menu.
	add_action( 'admin_init', 'wpcasldap_register_settings' );
	add_action( 'admin_menu', 'wpcasldap_options_page_add' );	
}

function wpcasldap_nowpuser($newuserid) {
	global $wpcasldap_use_options;
  global $ldapManager;
	$userdata = "";
	//error_log("\nThis is true:".$wpcasldap_use_options['useldap']);
	//error_log("\nThis is true:".function_exists("ldap_connect"));
	if ($wpcasldap_use_options['useldap'] == 'yes' && function_exists('ldap_connect') ) {
	//if ($wpcasldap_use_options['useldap'] == 'yes' ) {
		$newuser = $ldapManager->GetUser($newuserid, $wpcasldap_use_options["ldapbasedn"]);
		
		//echo "<pre>";print_r($newuser);echo "</pre>";
		//error_log("new user value :".$newuserid);
		//exit();
		if($newuser)
			$userdata = $newuser->GetData();
		else
			echo "User not found in LDAP";
		//echo "<br/> userdata returned :".print_r($userdata,true)."<br/> ";
	} else {
		$userdata = array(
				'user_login' => $newuserid,
				'user_password' => substr( md5( uniqid( microtime( ))), 0, 8 ),
				'user_email' => $newuserid.'@'.$wpcasldap_use_options['email_suffix'],
				'role' => $wpcasldap_use_options['userrole'],
			);
	}
	if (!function_exists('wp_insert_user'))
		include_once ( ABSPATH . WPINC . '/registration.php');
	

	if($userdata)
	{	
		$user_id = wp_insert_user( $userdata );
		if ( is_wp_error( $user_id ) ) {
			//error_log("inserting a user in wp failed");
	   		$error_string = $user_id->get_error_message();
	   		echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
	   		return;
		}
		/*
		if ( !$user_id || !$user) {
			error_log("This is coming here");
			$errors['registerfail'] = sprintf(__('<strong>ERROR</strong>: The login system couldn\'t register you in the local database. Please contact the <a href="mailto:%s">webmaster</a> '), get_option('admin_email'));
			return;
		} */else {
			wp_new_user_notification($user_id, $user_pass);
			wp_set_auth_cookie( $user->ID );

			if( isset( $_GET['redirect_to'] )){
				wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url(  ));
				die();
			}

			wp_redirect( site_url( '/wp-admin/' ));
			die();
		}
	}
}


function sid2str($sid)
{
$srl = ord($sid[0]);
$number_sub_id = ord($sid[1]);
$x = substr($sid,2,6);
$h = unpack('N',"\x0\x0".substr($x,0,2));
$l = unpack('N',substr($x,2,6));
$iav = bcadd(bcmul($h[1],bcpow(2,32)),$l[1]);
for ($i=0; $i<$number_sub_id; $i++)
{
$sub_id = unpack('V', substr($sid, 8+4*$i, 4));
$sub_ids[] = $sub_id[1];
}
return sprintf('S-%d-%d-%s', $srl, $iav, implode('-',$sub_ids));
}






//----------------------------------------------------------------------------
//		ADMIN OPTION PAGE FUNCTIONS
//----------------------------------------------------------------------------

function wpcasldap_register_settings() {
	global $wpcasldap_options;
	
	$options = array('email_suffix' , 'casserver', 'cas_version', 'include_path', 'server_hostname', 'server_port', 'server_path', 'useradd', 'userrole', 'ldapuri', 'ldaphost', 'ldapport',
	 'ldapbasedn', 'useldap', 'ldapuser', 'ldappassword', 'casorldap_attribute', 'casatt_name', 'casatt_operator', 'casatt_user_value_to_compare', 'casatt_wp_role', 'casatt_wp_site', 'ldap_query', 
	 'ldap_operator', 'ldap_user_value_to_compare', 'ldap_wp_role', 'ldap__wp_site');


	foreach ($options as $o) {
		if (!isset($wpcasldap_options[$o])) {
			switch($o) {
				case 'cas_verion':
					$cleaner = 'wpcasldap_oneortwo';
					break;
				case 'useradd':
				case 'useldap':
					$cleaner = 'wpcasldap_yesorno';
					break;
				case 'email_suffix':
					$cleaner = 'wpcasldap_strip_at';
					break;
				case 'userrole':
					$cleaner = 'wpcasldap_fix_userrole';
					break;
				case 'ldapport':
				case 'server_port':
					$cleaner = 'intval';
					break;
				default:
					$cleaner = 'wpcasldap_dummy';
			}
			register_setting( 'wpcasldap', 'wpcasldap_'.$o,$cleaner );
		}
	}
}

function wpcasldap_strip_at($in) {
	return str_replace('@','',$in);
}
function wpcasldap_yesorno ($in) {
	return (strtolower($in) == 'yes')?'yes':'no';	
}

function wpcasldap_oneortwo($in) {
	return ($in == '1.0')?'1.0':'2.0';
}
function wpcasldap_fix_userrole($in) {
	$roles = array('subscriber','contributor','author','editor','administrator');
	if (in_array($in,$roles))
		return $in;
	else 
		return 'subscriber';
}
function wpcasldap_dummy($in) {
	return $in;
}

function cas_client_settings()
{
	add_submenu_page("settings.php","CAS Client","CAS Client","manage_network","casclient",'wpcasldap_options_page');
}

function wpcasldap_options_page_add() {

	if (function_exists('add_management_page')) 
		add_submenu_page('options-general.php', 'CAS Client', 'CAS Client', CAPABILITY, 'wpcasldap', 'wpcasldap_options_page');	
		//add_submenu_page('options-general.php', 'wpCAS with LDAP', 'wpCAS with LDAP', CAPABILITY, 'wpcasldap', 'wpcasldap_options_page');		
	else
		add_options_page( 'CAS Client','CAS Client',CAPABILITY, basename(__FILE__), 'wpcasldap_options_page');
		//add_options_page( __( 'wpCAS with LDAP', 'wpcasldap' ), __( 'wpCAS with LDAP', 'wpcasldap' ),CAPABILITY, basename(__FILE__), 'wpcasldap_options_page');

} 



function wpcasldap_getoptions() {
	global $wpcasldap_options;
	global $get_options_func;
    global $ldapManager;
	//Parse the url to retrieve server_name, server_port and path
	$cas_server = $get_options_func('wpcasldap_casserver');
	$componentsOfUrl = parse_cas_url($cas_server);
	error_log("url componenets :".print_r($componentsOfUrl,true));
	$host = "";
	$port = "";
	$path = "";
	if($componentsOfUrl)
	{
		if(isset($componentsOfUrl['host']))
		{
			$host = $componentsOfUrl['host'];
		}
		
		if(isset($componentsOfUrl['port']))
			$port = $componentsOfUrl['port'];
		else
			$port = CAS_DEFAULT_PORT;

		if(isset($componentsOfUrl['path']))
			$path = $componentsOfUrl['path'];
		else
			$path = CAS_DEFAULT_PATH;
	}

//error_log("hostname :".$host);
//error_log("port :".$port);
//error_log("path :".$path);

//Parse ldap URI to retrieve LDAP Host and LDAP Port

$ldap_uri = $get_options_func('wpcasldap_ldapuri');
$ldap_host = "";
$ldap_port = "";
$ldap_uri_components = ldapManager::ParseUri($ldap_uri);
if(isset($ldap_uri_components))
{
	if(isset($ldap_uri_components['host']))
	{
		$ldap_host = $ldap_uri_components['host'];
	}

	if(isset($ldap_uri_components['port']))
		$ldap_port = $ldap_uri_components['port'];
	else if(isset($ldap_uri_components['scheme']))
	{
		if(strtolower($ldap_uri_components['scheme']) == 'ldaps')
			$ldap_port = ldapManager::SSL_DEFAULT_PORT;
		else if(strtolower($ldap_uri_components['scheme']) == 'ldap')
			$ldap_port = ldapManager::DEFAULT_PORT;
	}
	else
		$ldap_port = ldapManager::DEFAULT_PORT;
}
//error_log("scheme :".$ldap_uri_components['scheme']);
//error_log("hostname :".$ldap_host);
//error_log("port :".$ldap_port);

//get ldap password and decrypt it
$ldapPassword = (string) $get_options_func('wpcasldap_ldappassword');

$ldapPassword = wpcasclient_decrypt($ldapPassword , $GLOBALS['ciphers'])  ;
$ldapPassword = $ldapPassword ? $ldapPassword : ""; // if the  decrypt function returns false thn set password to empty string

  // TODO: Are all of these settings still being used? (e.g. ldap_host?)
	$out = array (
			'email_suffix' => $get_options_func('wpcasldap_email_suffix'),
			'cas_version' => $get_options_func('wpcasldap_cas_version'),
			'include_path' => $get_options_func('wpcasldap_include_path'),
			'casserver' => $cas_server, //$get_options_func('wpcasldap_casserver'),
			'server_hostname' => $host,//$get_options_func('wpcasldap_server_hostname'),
			'server_port' => $port,//$get_options_func('wpcasldap_server_port'),
			'server_path' => $path,//$get_options_func('wpcasldap_server_path'),
			'useradd' => $get_options_func('wpcasldap_useradd'),
			'userrole' => $get_options_func('wpcasldap_userrole'),
			'ldapuri' => $ldap_uri,//$get_options_func('wpcasldap_ldapuri'),
			'ldaphost' => $ldap_host, //$get_options_func('wpcasldap_ldaphost'),
			'ldapport' => $ldap_port,// $get_options_func('wpcasldap_ldapport'),
			'useldap' => $get_options_func('wpcasldap_useldap'),
			'ldapbasedn' => $get_options_func('wpcasldap_ldapbasedn'),
			'ldapuser' => $get_options_func('wpcasldap_ldapuser'),
			'ldappassword' => $ldapPassword,			
			'casorldap_attribute' => $get_options_func('wpcasldap_casorldap_attribute'),
			'casatt_name' => $get_options_func('wpcasldap_casatt_name'),
			'casatt_operator' => $get_options_func('wpcasldap_casatt_operator'),
			'casatt_user_value_to_compare' => $get_options_func('wpcasldap_casatt_user_value_to_compare'),
			'casatt_wp_role' => $get_options_func('wpcasldap_casatt_wp_role'),
			'casatt_wp_site' => $get_options_func('wpcasldap_casatt_wp_site'),
			'ldap_query' => $get_options_func('wpcasldap_ldap_query'),
			'ldap_operator' => $get_options_func('wpcasldap_ldap_operator'),
			'ldap_user_value_to_compare' => $get_options_func('wpcasldap_ldap_user_value_to_compare'),
			'ldap_wp_role' => $get_options_func('wpcasldap_ldap_wp_role'),
			'ldap__wp_site' => $get_options_func('wpcasldap_ldap_wp_site'),
		);

	if (is_array($wpcasldap_options) && count($wpcasldap_options) > 0)
    {
		foreach ($wpcasldap_options as $key => $val) {
			$out[$key] = $val;
		}
    }

    //error_log("OUT :".print_r($out,true));
	return $out;
}

function parse_cas_url(&$cas_server_url)
{
  $components =  parse_url($cas_server_url);
  if($components)
  {
    if(empty($components['host']) && !empty($components['path']))
    {
      error_log("path :".$components['path']);
      $cas_server_url = SCHEME.$cas_server_url;
      error_log("cas url :".$cas_server_url);
      $components =  parse_url($cas_server_url);
      error_log("componenets after editing url :".print_r($components,true));
    }
  }
  return $components;
}

function get_option_wrapper($opt)
{
  global $get_options_func;
  return $get_options_func($opt);
}
?>


