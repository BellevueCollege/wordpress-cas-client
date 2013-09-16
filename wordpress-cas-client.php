<?php
/*
Plugin Name: WordPress CAS Client
Plugin URI: https://github.com/BellevueCollege/wordpress-cas-client
Description: Integrates WordPress with existing <a href="http://en.wikipedia.org/wiki/Central_Authentication_Service">CAS</a> single sign-on architectures. Additionally this plugin can use a LDAP server (such as Active Directory) for populating user information after the user has successfully logged on to WordPress. This plugin is a fork of the <a href="http://wordpress.org/extend/plugins/wpcas-w-ldap">wpCAS-w-LDAP</a> plugin.
Version: 1.1.0.0
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


if (file_exists( dirname(__FILE__).'/config.php' ) ) 
	include_once( dirname(__FILE__).'/config.php' ); // attempt to fetch the optional config file

if (file_exists( dirname(__FILE__).'/network-settings-ui.php' ) ) 
	include_once( dirname(__FILE__).'/network-settings-ui.php' ); // attempt to fetch the optional config file

define("CAPABILITY","edit_themes");
// This global variable is set to either 'get_option' or 'get_site_option' depending on multisite option value
global $get_options_func ;
$get_options_func = "get_option";
//This global variable is defaulted to 'options.php' , but for network setting we want the form to submit to itself, so we will leave it empty
global $form_action;
$form_action = "options.php";
	if(is_multisite())
	{
		updateNetworkSettings();
		add_action( 'network_admin_menu', 'cas_client_settings' );

		error_log("multisite true");
		$get_options_func = "get_site_option";
		$form_action = "";
	}
	error_log("version :". $get_options_func('wpcasldap_cas_version'));
	error_log("version :". $get_options_func('wpcasldap_server_hostname'));





global $wpcasldap_options;
if($wpcasldap_options)
{
	if (!is_array($wpcasldap_options))
		$wpcasldap_optons = array();
}

$wpcasldap_use_options = wpcasldap_getoptions();
//error_log("options :".print_r($wpcasldap_use_options,true));
$cas_configured = true;

// try to configure the phpCAS client
if ($wpcasldap_use_options['include_path'] == '' ||
		(include_once $wpcasldap_use_options['include_path']) != true)
	$cas_configured = false;

if ($wpcasldap_use_options['server_hostname'] == '' ||
		$wpcasldap_use_options['server_path'] == '' ||
		intval($wpcasldap_use_options['server_port']) == 0)
	$cas_configured = false;

if ($cas_configured) {
	phpCAS::client($wpcasldap_use_options['cas_version'], 
		$wpcasldap_use_options['server_hostname'], 
		intval($wpcasldap_use_options['server_port']), 
		$wpcasldap_use_options['server_path']);
	
	// function added in phpCAS v. 0.6.0
	// checking for static method existance is frustrating in php4
	$phpCas = new phpCas();
	if (method_exists($phpCas, 'setNoCasServerValidation'))
		phpCAS::setNoCasServerValidation();
	unset($phpCas);
	// if you want to set a cert, replace the above few lines
 }

// plugin hooks into authentication system
add_action('wp_authenticate', array('wpCASLDAP', 'authenticate'), 10, 2);
add_action('wp_logout', array('wpCASLDAP', 'logout'));
add_action('lost_password', array('wpCASLDAP', 'disable_function'));
add_action('retrieve_password', array('wpCASLDAP', 'disable_function'));
add_action('password_reset', array('wpCASLDAP', 'disable_function'));
add_filter('show_password_fields', array('wpCASLDAP', 'show_password_fields'));

if (is_admin() && !is_multisite()) {// Added condition not multisite because if multisite is true thn it should only show the settings in network admin menu.
	add_action( 'admin_init', 'wpcasldap_register_settings' );
	add_action( 'admin_menu', 'wpcasldap_options_page_add' );	
}
class wpCASLDAP {
	
	/*
	 We call phpCAS to authenticate the user at the appropriate time 
	 (the script dies there if login was unsuccessful)
	 If the user is not provisioned and wpcasldap_useradd is set to 'yes', wpcasldap_nowpuser() is called
	*/
	
	function authenticate() {
		global $wpcasldap_use_options, $cas_configured, $blog_id;

		if ( !$cas_configured )
			die( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ));

		if( phpCAS::isAuthenticated() ){
			// CAS was successful

			if ( $user = get_user_by( 'login', phpCAS::getUser() )){ // user already exists
					error_log("correct login");
					// Update user information from ldap
					if ($wpcasldap_use_options['useldap'] == 'yes' && function_exists('ldap_connect') ) {
						
						$existingUser = get_ldap_user(phpCAS::getUser());	
						//var_dump($existingUser);
						if($existingUser)	
						{
							
							$userdata = $existingUser->get_user_data();
							$userdata["ID"] = $user->ID;
							
							unset($userdata['role']);//Remove role from userdata

							$userID = wp_update_user( $userdata );
							
							if ( is_wp_error( $userID ) ) {
								//error_log("Update user failing");
								$error_string = $userID->get_error_message();
								//error_log($error_string);
								echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
								
							}
						}
						else
						{
							error_log("existing user returned false");
						}
					}
				$udata = get_userdata($user->ID);
				
				$userExists = is_user_member_of_blog( $user->ID, $blog_id);
				if (!$userExists) {
					if (function_exists('add_user_to_blog')) { add_user_to_blog($blog_id, $user->ID, $wpcasldap_use_options['userrole']); }
				}
				
				// the CAS user has a WP account
				wp_set_auth_cookie( $user->ID );

				if( isset( $_GET['redirect_to'] )){
					wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url(  ));
					error_log("check if die1 :".$_GET['redirect_to']);
					error_log("compare returns :".preg_match( '/^http/', $_GET['redirect_to']));

					die();
				}
				error_log("check if die2");		
				wp_redirect( site_url( '/wp-admin/' ));
				die();

			}else{
				// the CAS user _does_not_have_ a WP account
				if (function_exists( 'wpcasldap_nowpuser' ) && $wpcasldap_use_options['useradd'] == 'yes')
				{
					error_log("check if die3");
					wpcasldap_nowpuser( phpCAS::getUser() );
				}
					
				else
					die( __( 'you do not have permission here', 'wpcasldap' ));
			}
		}else{
			// hey, authenticate
			phpCAS::forceAuthentication();
			die();
		}
	}
	
	
	// hook CAS logout to WP logout
	function logout() {
		global $cas_configured;
		global $get_options_func;
		if (!$cas_configured)
			die( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ));

		phpCAS::logout( array( 'url' => $get_options_func( 'siteurl' )));
		exit();
	}

	// hide password fields on user profile page.
	function show_password_fields( $show_password_fields ) {
		if( 'user-new.php' <> basename( $_SERVER['PHP_SELF'] ))
			return false;

		$random_password = substr( md5( uniqid( microtime( ))), 0, 8 );

?>
<input id="wpcasldap_pass1" type="hidden" name="pass1" value="<?php echo $random_password ?>" />
<input id="wpcasldap_pass2" type="hidden" name="pass2" value="<?php echo $random_password ?>" />
<?php
		return false;
	}

	// disabled reset, lost, and retrieve password features
	function disable_function() {
		die( __( 'Sorry, this feature is disabled.', 'wpcasldap' ));
	}
}

function wpcasldap_nowpuser($newuserid) {
	global $wpcasldap_use_options;
	$userdata = "";
	//error_log("\nThis is true:".$wpcasldap_use_options['useldap']);
	//error_log("\nThis is true:".function_exists("ldap_connect"));
	if ($wpcasldap_use_options['useldap'] == 'yes' && function_exists('ldap_connect') ) {
	//if ($wpcasldap_use_options['useldap'] == 'yes' ) {
		$newuser = get_ldap_user($newuserid);
		
		//echo "<pre>";print_r($newuser);echo "</pre>";
		error_log("new user value :".$newuserid);
		//exit();
		if($newuser)
			$userdata = $newuser->get_user_data();
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
			error_log("inserting a user in wp failed");
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

function get_ldap_user($uid) {
	global $wpcasldap_use_options;
	$ds = ldap_connect($wpcasldap_use_options['ldaphost'],$wpcasldap_use_options['ldapport']);//ldap_connect($wpcasldap_use_options['ldaphost'],$wpcasldap_use_options['ldapport']);
	error_log("host :".$wpcasldap_use_options['ldaphost']);
	error_log("port :".$wpcasldap_use_options['ldapport']);
	//Can't connect to LDAP.
	if(!$ds) {
		$error = 'Error in contacting the LDAP server.';
		error_log("\n".$error);
	} else {	
		//error_log("\n".$filter);
		/*
		$ldap_dn = $wpcasldap_use_options['ldapbasedn'];
	    */
		//echo "<h2>Connected</h2>";
		
		// Make sure the protocol is set to version 3
		if(!ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
			$error = 'Failed to set protocol version to 3.';
		} else {
			//Connection made -- bind anonymously and get dn for username.
			$ldaprdn  = $GLOBALS['ldapUser'];     // ldap rdn or dn
			$ldappass = $GLOBALS['ldapPassword'];  // associated password
			error_log("username :".$ldaprdn);
			error_log("password :".$ldappass);
			//echo "ldap user :".$ldaprdn ;
			$bind = @ldap_bind($ds,$ldaprdn,$ldappass);
			//$bind = @ldap_bind($ds);
			//Check to make sure we're bound.
			if(!$bind) {
				$error = 'Anonymous bind to LDAP failed.';
				echo "\nERROR: ".$error;
				//exit();
			} else {

				/*
				This code is added to get all the groups a users belongs to.
				*/
				/*
				$GroupsDN = array();
				$filter = "sAMAccountName=".$uid;
				$attributes_ad = array("dn","givenName","sn","primaryGroupID");	
				//Query to get Primary group id			
				$search = ldap_search($ds, $wpcasldap_use_options['ldapbasedn'], $filter,$attributes_ad);
				$result = ldap_get_entries($ds, $search);
				error_log( "result:".print_r($result,true));

				$pri_grp_rid = $result[0]['primarygroupid'][0];
				echo "primaryGroupID :".$pri_grp_rid ;

				$r = ldap_read($ds, $wpcasldap_use_options['ldapbasedn'], '(objectclass=*)', array('objectSid')) or exit('ldap_search');
				$data = ldap_get_entries($ds, $r);
				$domain_sid = $data[0]['objectsid'][0];
				echo "<br/> domain sid:".$domain_sid;
				$domain_sid_s = sid2str($domain_sid);
				echo "<br/> domain sid s:".$domain_sid_s;
				//Request to get Primary group CN
				$r = ldap_search($ds, $wpcasldap_use_options['ldapbasedn'], "objectSid=${domain_sid_s}-${pri_grp_rid}", array('cn')) or exit('ldap_search');
				$data = ldap_get_entries($ds, $r);
				error_log("\n data:".print_r($data,true));
				//exit();
				
				$defaultGroupDN = $data[0]['dn'];
				$getCN = $data[0]['cn'][0];
				//$defaultGroupDN = "CN=".$getCN.",OU=Groups,DC=BellevueCollege,DC=EDU" ;
				echo "<br/> CN:".$getCN;
				

				echo("<br/> dn = ".$defaultGroupDN."\n");
				// This query is to get all the groups which are memberOf Primary Group 
				//Its not working right now. 
				if($defaultGroupDN !=null)
				{
					$GroupsDN[] = $defaultGroupDN ;
					$filter = "(memberof:1.2.840.113556.1.4.1941:=".$defaultGroupDN.")";
					$attributes_ad = array("CN");
					$search = ldap_search($ds, $wpcasldap_use_options['ldapbasedn'], $filter,$attributes_ad);
					$info = ldap_get_entries($ds, $search);
					echo("<br/>".print_r($info,true));
					for($i=0;$i<count($info);$i++)
		    		{
		    			
		    				if($info[$i]["dn"] !=null)
		    					$GroupsDN[] = $info[$i]["dn"] ;
		    				echo(print_r("<br/>".$info[$i]["dn"],true) ."<br/>");
		    			
		    			//var_dump($info[$i]);
		    		}
				}
				
				if($result[0]["dn"] !=null)
				{
					$filter = "(member:1.2.840.113556.1.4.1941:=".$result[0]["dn"].")";
					$attributes_ad = array("CN");
					$search = ldap_search($ds, $wpcasldap_use_options['ldapbasedn'], $filter,$attributes_ad);
					$info = ldap_get_entries($ds, $search);

					//error_log("\nresult identifier :".$info);
		    		error_log("\nenterries :".print_r($info,true));
		    		echo "count :".count($info);
		    		for($i=0;$i<count($info);$i++)
		    		{
		    			
		    				if($info[$i]["dn"] !=null)
		    					$GroupsDN[] = $info[$i]["dn"] ;
		    				echo(print_r($info[$i]["dn"],true) ."<br/>");
		    			
		    			//var_dump($info[$i]);
		    		}
		    		//var_dump($info);
		    		exit();
		    	}

				*/
				$search = ldap_search($ds, $wpcasldap_use_options['ldapbasedn'], "sAMAccountName=$uid",array('uid','mail','givenname','sn','rolename','cn','EmployeeID','sAMAccountName'));
				$info = ldap_get_entries($ds, $search);

		    	
				ldap_close($ds);
				return new wpcasldapuser($info);
			}
			ldap_close($ds);
		}
	}
	return FALSE;
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




class wpcasldapuser
{
	private $data = NULL;

	function __construct($member_array) {
		$this->data = $member_array;
	}

	function get_user_name() {
		if(isset($this->data[0]['cn'][0]))
			return $this->data[0]['cn'][0];
		else
			return FALSE;
	}
	
	function get_user_data() {
		global $wpcasldap_use_options;
		if (isset($this->data[0]['uid'][0]) || isset($this->data[0]['employeeid'][0])) // updating the if to have employeeid check also
		{
			$userrole = "";
			//echo "<br/> user login".$this->data[0]['samaccountname'][0];
			if($this->data[0]['employeeid'][0] != null)
			{
				$userrole = $GLOBALS["defaultEmployeeUserrole"];
			}
			else
			{
				$userrole = $GLOBALS["defaultStudentUserrole"];
			}
			return array(
				'user_login' => $this->data[0]['samaccountname'][0],
				'user_password' => substr( md5( uniqid( microtime( ))), 0, 8 ), 
				'user_email' => $this->data[0]['mail'][0],
				'first_name' => $this->data[0]['givenname'][0],
				'last_name' => $this->data[0]['sn'][0],
				'role' => $userrole,
				'nickname' => $this->data[0]['cn'][0],
				'user_nicename' => $this->data[0]['uid'][0]
			);
		}
		else 
			return false;
	}
	
}


//----------------------------------------------------------------------------
//		ADMIN OPTION PAGE FUNCTIONS
//----------------------------------------------------------------------------

function wpcasldap_register_settings() {
	global $wpcasldap_options;
	
	$options = array('email_suffix', 'cas_version', 'include_path', 'server_hostname', 'server_port', 'server_path', 'useradd', 'userrole', 'ldaphost', 'ldapport', 'ldapbasedn', 'useldap');




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
	add_submenu_page("settings.php","CAS Client Settings","CAS Client Settings","manage_network","casclient",'wpcasldap_options_page');
}

function wpcasldap_options_page_add() {

	

	if (function_exists('add_management_page')) 
		add_submenu_page('options-general.php', 'CAS Client Settings', 'CAS Client Settings', CAPABILITY, 'wpcasldap', 'wpcasldap_options_page');	
		//add_submenu_page('options-general.php', 'wpCAS with LDAP', 'wpCAS with LDAP', CAPABILITY, 'wpcasldap', 'wpcasldap_options_page');		
	else
		add_options_page( 'CAS Client Settings','CAS Client Settings',CAPABILITY, basename(__FILE__), 'wpcasldap_options_page');
		//add_options_page( __( 'wpCAS with LDAP', 'wpcasldap' ), __( 'wpCAS with LDAP', 'wpcasldap' ),CAPABILITY, basename(__FILE__), 'wpcasldap_options_page');

} 



function wpcasldap_getoptions() {
	global $wpcasldap_options;
	global $get_options_func;

	$out = array (
			'email_suffix' => $get_options_func('wpcasldap_email_suffix'),
			'cas_version' => $get_options_func('wpcasldap_cas_version'),
			'include_path' => $get_options_func('wpcasldap_include_path'),
			'server_hostname' => $get_options_func('wpcasldap_server_hostname'),
			'server_port' => $get_options_func('wpcasldap_server_port'),
			'server_path' => $get_options_func('wpcasldap_server_path'),
			'useradd' => $get_options_func('wpcasldap_useradd'),
			'userrole' => $get_options_func('wpcasldap_userrole'),
			'ldaphost' => $get_options_func('wpcasldap_ldaphost'),
			'ldapport' => $get_options_func('wpcasldap_ldapport'),
			'useldap' => $get_options_func('wpcasldap_useldap'),
			'ldapbasedn' => $get_options_func('wpcasldap_ldapbasedn')			
		);
	
	if (is_array($wpcasldap_options) && count($wpcasldap_options) > 0)
		foreach ($wpcasldap_options as $key => $val) {
			$out[$key] = $val;	
		}
		error_log("OUT :".print_r($out,true));
	return $out;
}

function wpcasldap_options_page() {


	global $wpdb, $wpcasldap_options,$form_action;
	
	//echo "<pre>"; print_r($wpcasldap_options); echo "</pre>";
	// Get Options
	$optionarray_def = wpcasldap_getoptions();
	
	?>
	<div class="wrap">
	<h2>CAS Client Settings</h2>
	<!-- <form method="post" action="options.php"> -->
	<form method="post" action="<?php echo $form_action?>">
		<?php settings_fields( 'wpcasldap' ); ?>

		<h3><?php _e('Configuration settings for WordPress CAS Client', 'wpcasldap') ?></h3>
		<h4><?php _e('Note', 'wpcasldap') ?></h4>
		<p>
			<?php _e('Now that you’ve activated this plugin, WordPress is attempting to authenticate using CAS, even if it’s not configured or misconfigured.', 'wpcasldap' ) ?><br />
			<?php _e('Save yourself some trouble, open up another browser or use another machine to test logins. That way you can preserve this session to adjust the configuration or deactivate the plugin.', 'wpcasldap') ?>"
		</p>

		<?php if (!isset($wpcasldap_options['include_path'])) : ?>
		<h4><?php _e('phpCAS include path', 'wpcasldap') ?></h4>
		<p>
			<small><em><?php _e('Note: The phpCAS library is required for this plugin to work. We need to know the server path to the CAS.php file.', 'wpcasldap') ?></em></small>
		</p>

		<table class="form-table">

	        <tr valign="top">
				<th scope="row">
					<label>
						<?php _e('CAS.php Path', 'wpcasldap') ?>
					</label>
				</th>

				<td>
					<input type="text" size="80" name="wpcasldap_include_path" id="include_path_inp" value="<?php echo $optionarray_def['include_path']; ?>" />
				</td>
			</tr>

		</table>
	<?php endif; ?>
    
    <?php if (!isset($wpcasldap_options['cas_version']) ||
			!isset($wpcasldap_options['server_hostname']) ||
			!isset($wpcasldap_options['server_port']) ||
			!isset($wpcasldap_options['server_path']) ) : ?>
	<h4><?php _e('phpCAS::client() parameters', 'wpcasldap') ?></h4>
	<table class="form-table">
	    <?php if (!isset($wpcasldap_options['cas_version'])) : ?>

		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('CAS version', 'wpcasldap') ?>
				</lable>
			</th>

			<td>
				<select name="wpcasldap_cas_version" id="cas_version_inp">
                    <option value="2.0" <?php echo ($optionarray_def['cas_version'] == '2.0')?'selected':''; ?>>CAS_VERSION_2_0</option>
                    <option value="1.0" <?php echo ($optionarray_def['cas_version'] == '1.0')?'selected':''; ?>>CAS_VERSION_1_0</option>
                </select>
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['server_hostname'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('Server Hostname', 'wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_server_hostname" id="server_hostname_inp" value="<?php echo $optionarray_def['server_hostname']; ?>" />
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['server_port'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('Server Port','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_server_port" id="server_port_inp" value="<?php echo $optionarray_def['server_port']; ?>" />
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['server_path'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('Server Path','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_server_path" id="server_path_inp" value="<?php echo $optionarray_def['server_path']; ?>" />
			</td>
		</tr>
        <?php endif; ?>
	</table>
	<?php endif; ?>

    <?php if (!isset($wpcasldap_options['useradd']) ||
			!isset($wpcasldap_options['userrole']) ||
			!isset($wpcasldap_options['useldap']) ||
			!isset($wpcasldap_options['email_suffix']) ) : ?>

	<h4><?php _e('Treatment of Unregistered User','wpcasldap') ?></h4>
		<table class="form-table">
		    <?php if (!isset($wpcasldap_options['useradd'])) : ?>
			<tr valign="top">
				<th scope="row">
					<label>
						<?php _e('Add to Database','wpcasldap') ?>
					</lable>
				</th>

				<td>

					<input type="radio" name="wpcasldap_useradd" id="useradd_yes" value="yes" <?php echo ($optionarray_def['useradd'] == 'yes')?'checked="checked"':''; ?> />
					<label for="useradd_yes">Yes &nbsp;</label>

					<input type="radio" name="wpcasldap_useradd" id="useradd_no" value="no" <?php echo ($optionarray_def['useradd'] != 'yes')?'checked="checked"':''; ?> />
					<label for="useradd_no">No &nbsp;</label>
				</td>
			</tr>
	        <?php endif; ?>
		    <?php if (!isset($wpcasldap_options['userrole'])) : ?>
			<tr valign="top">
				<th scope="row">
					<label>
						<?php _e('Default Role','wpcasldap') ?>
					</label>
				</th>

				<td>
					<select name="wpcasldap_userrole" id="cas_version_inp">
						<option value="subscriber" <?php echo ($optionarray_def['userrole'] == 'subscriber')?'selected':''; ?>>Subscriberssss</option>
						<option value="contributor" <?php echo ($optionarray_def['userrole'] == 'contributor')?'selected':''; ?>>Contributor</option>
						<option value="author" <?php echo ($optionarray_def['userrole'] == 'author')?'selected':''; ?>>Author</option>
						<option value="editor" <?php echo ($optionarray_def['userrole'] == 'editor')?'selected':''; ?>>Editor</option>
						<option value="administrator" <?php echo ($optionarray_def['userrole'] == 'administrator')?'selected':''; ?>>Administrator</option>
	                </select>
	            </td>
			</tr>
	        <?php endif; ?>
		    <?php if (!isset($wpcasldap_options['useldap'])) : ?>
				<?php if (function_exists('ldap_connect')) :

					//error_log("ldap connect exists");
				?>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('Use LDAP to get user info','wpcasldap') ?>
						</label>
					</th>

					<td>
						<input type="radio" name="wpcasldap_useldap" id="useldap_yes" value="yes" <?php echo ($optionarray_def['useldap'] == 'yes')?'checked="checked"':''; ?> />
						<label for="useldap_yes">Yes &nbsp;</label>

						<input type="radio" name="wpcasldap_useldap" id="useldap_no" value="no" <?php echo ($optionarray_def['useldap'] != 'yes')?'checked="checked"':''; ?> />
						<label for="useldap_no">No &nbsp;</label>
					</td>
				</tr>
				<?php
				else :
				?>
					<input type="hidden" name="wpcasldap_useldap" id="useldap_hidden" value="no" />
				<?php
				endif;
				?>
	        <?php endif; ?>

		   <?php if (!isset($wpcasldap_options['email_suffix'])) : ?>
		   <tr valign="center">
				<th scope="row">
					<label>
						<?php _e('E-mail Suffix','wpcasldap') ?>
					</label>
				</th>

				<td>
					<input type="text" size="50" name="wpcasldap_email_suffix" id="server_port_inp" value="<?php echo $optionarray_def['email_suffix']; ?>" />
				</td>
			</tr>
	        <?php endif; ?>
		</table>
	    <?php endif; ?>
    
    <?php if (function_exists('ldap_connect')) : ?>
    <?php if (!isset($wpcasldap_options['ldapbasedn']) ||
			!isset($wpcasldap_options['ldapport']) ||
			!isset($wpcasldap_options['ldaphost']) ) : ?>
	<h4><?php _e('LDAP parameters','wpcasldap') ?></h4>

	<table class="form-table">
	    <?php if (!isset($wpcasldap_options['ldaphost'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Host','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_ldaphost" id="ldap_host_inp" value="<?php echo $optionarray_def['ldaphost']; ?>" />
			</td>
		</tr>
        <?php endif; ?>
	    <?php if (!isset($wpcasldap_options['ldapport'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Port','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_ldapport" id="ldap_port_inp" value="<?php echo $optionarray_def['ldapport']; ?>"  />
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['ldapbasedn'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Base DN','wpcasldap') ?>
				</label>
			</th>
			<td>
				<input type="text" size="50" name="wpcasldap_ldapbasedn" id="ldap_basedn_inp" value="<?php echo $optionarray_def['ldapbasedn']; ?>" />
			</td>
		</tr>
        <?php endif; ?>
	</table>
    <?php endif; ?>
    <?php endif; ?>

	<div class="submit">
		<input type="submit" name="wpcasldap_submit" value="Update Options &raquo;" />
	</div>
	</form>
<?php
}
