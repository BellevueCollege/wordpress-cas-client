<?php
/*
Plugin Name: WordPress CAS Client
Plugin URI: https://github.com/BellevueCollege/wordpress-cas-client
Description: Integrates WordPress with existing <a href="http://en.wikipedia.org/wiki/Central_Authentication_Service">CAS</a> single sign-on architectures. Additionally this plugin can use a LDAP server (such as Active Directory) for populating user information after the user has successfully logged on to WordPress. This plugin is a fork of the <a href="http://wordpress.org/extend/plugins/wpcas-w-ldap">wpCAS-w-LDAP</a> plugin.
Version: 1.2.2.1
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

// attempt to fetch the optional config file
if ( file_exists( dirname( __FILE__ ) . '/config.php' ) ) {
	include_once( dirname( __FILE__ ) . '/config.php' );
}

if ( file_exists( dirname( __FILE__ ) . '/network-settings-ui.php' ) ) {
	include_once( dirname( __FILE__ ) . '/network-settings-ui.php' );
}

define( 'CAPABILITY', 'edit_themes' );

/*
 * This global variable is set to either 'get_option' or 'get_site_option'
 * depending on multisite option value.
 */
global $get_options_func;
$get_options_func = 'get_option';

/*
 * This global variable is defaulted to 'options.php' , but for network
 * setting we want the form to submit to itself, so we will leave it empty.
 */
global $form_action;
$form_action = 'options.php';
if ( is_multisite( ) ) {
	update_network_settings( );
	add_action( 'network_admin_menu', 'cas_client_settings' );

	$get_options_func = 'get_site_option';
	$form_action = '';
}

global $wp_cas_ldap_options;
if ( $wp_cas_ldap_options ) {
	if ( ! is_array( $wp_cas_ldap_options ) ) {
		$wp_cas_ldap_options = array( );
	}
}

$wp_cas_ldap_use_options = wp_cas_ldap_get_options( );

global $cas_configured;
$cas_configured = true;

// try to configure the phpCAS client
if ( empty( $wp_cas_ldap_use_options['include_path'] ) ||
    ! include_once( $wp_cas_ldap_use_options['include_path'] ) ) {
	$cas_configured = false;
}

if ( empty( $wp_cas_ldap_use_options['server_hostname'] ) ||
		empty( $wp_cas_ldap_use_options['server_path'] ) ||
		empty( $wp_cas_ldap_use_options['server_port'] ) ) {
	$cas_configured = false;
}

if ( $cas_configured && ! isset( $_SESSION['CAS_INI'] ) ) {
	phpCAS::client($wp_cas_ldap_use_options['cas_version'],
		$wp_cas_ldap_use_options['server_hostname'],
		intval( $wp_cas_ldap_use_options['server_port'] ),
		$wp_cas_ldap_use_options['server_path']);

	$_SESSION['CAS_INI'] = true;

	/*
	 * function added in phpCAS v. 0.6.0
	 * checking for static method existance is frustrating in php4
	 */
	$php_cas = new phpCas();
	if ( method_exists( $php_cas, 'setNoCasServerValidation' ) ) {
		phpCAS::setNoCasServerValidation( );
	}
	unset( $php_cas );
	// if you want to set a cert, replace the above few lines
}

// plugin hooks into authentication system
add_action( 'wp_authenticate', array( 'WP_CAS_LDAP', 'authenticate' ), 10, 2 );
add_action( 'wp_logout', array( 'WP_CAS_LDAP', 'logout' ) );
add_action( 'lost_password', array( 'WP_CAS_LDAP', 'disable_function' ) );
add_action( 'retrieve_password', array( 'WP_CAS_LDAP', 'disable_function' ) );
add_action( 'password_reset', array( 'WP_CAS_LDAP', 'disable_function' ) );
add_filter( 'show_password_fields', array( 'WP_CAS_LDAP', 'show_password_fields' ) );

/*
 * Added condition not multisite because if multisite is true thn it should
 * only show the settings in network admin menu.
 */
if ( is_admin( ) && ! is_multisite( ) ) {
	add_action( 'admin_init', 'wp_cas_ldap_register_settings' );
	add_action( 'admin_menu', 'wp_cas_ldap_options_page_add' );
}

/**
 * WP_CAS_LDAP class hook for WordPress.
 */
class WP_CAS_LDAP {

	/**
	 * authenticate function hook for WordPress.
	 *
	 * We call phpCAS to authenticate the user at the appropriate time
	 * (the script dies there if login was unsuccessful)
	 * If the user is not provisioned and wpcasldap_useradd is set to 'yes'
	 * wp_cas_ldap_now_puser() is called.
	 */
	function authenticate( ) {
		global $wp_cas_ldap_use_options, $cas_configured, $blog_id;

		if ( ! $cas_configured ) {
			exit( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ) );
		}

		if ( phpCAS::isAuthenticated( ) ) {
			// CAS was successful

			$user = get_user_by( 'login', phpCAS::getUser( ) );
			// If user already exists
			if ( $user ) {
					// Update user information from ldap
					if ( 'yes' === $wp_cas_ldap_use_options['useldap'] && function_exists( 'ldap_connect' ) ) {

						$existing_user = get_ldap_user( phpCAS::getUser( ) );
						if ( $existing_user ) {
							$user_data = $existing_user->get_user_data( );
							$user_data['ID'] = $user->ID;

							//Remove role from userdata
							unset( $user_data['role'] );

							$user_id = wp_update_user( $user_data );

							if ( is_wp_error( $user_id ) ) {
								$error_string = $user_id->get_error_message( );
								error_log( 'Update user failed: ' . $error_string );
								echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
							}
						}
					}

				$user_exists = is_user_member_of_blog( $user->ID, $blog_id );
				if ( ! $user_exists ) {
					if ( function_exists( 'add_user_to_blog' ) ) {
						add_user_to_blog( $blog_id, $user->ID, $wp_cas_ldap_use_options['userrole'] );
					}
				}

				// the CAS user has a WP account
				wp_set_auth_cookie($user->ID);

				if ( isset( $_GET['redirect_to'] ) ) {
					wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( ) );
					exit( );
				}
				wp_redirect( site_url( '/wp-admin/' ) );
				exit( );

			} else {
				// the CAS user _does_not_have_ a WP account
				if ( function_exists( 'wp_cas_ldap_now_puser' ) && 'yes' === $wp_cas_ldap_use_options['useradd'] ) {
					wp_cas_ldap_now_puser( phpCAS::getUser( ) );
				} else {
					exit( __( 'you do not have permission here', 'wpcasldap' ) );
				}
			}
		} else {
			// Authenticate the user
			phpCAS::forceAuthentication( );
			exit( );
		}
	}

	/**
	 * logout function hook for WordPress.
	 */
	function logout( ) {
		global $cas_configured;
		global $get_options_func;
		if ( ! $cas_configured ) {
			exit( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ) );
		}

		phpCAS::logout( array( 'url' => $get_options_func( 'siteurl' ) ) );
		exit( );
	}

	/**
	 * show_password_fields method hook for WordPress.
	 *
	 * We overide the show_password_fields functionality in WordPress to hide the
	 * password field on user profile pages.
	 *
	 * @param bool $show_password_fields required param by WordPress
	 * @returns false
	 */
	function show_password_fields( $show_password_fields ) {
		if ( 'user-new.php' !== basename( $_SERVER['PHP_SELF'] ) ) {
			return false;
		}

		$random_password = generate_password( 32, 64 );
		echo '<input id="wpcasldap_pass1" type="hidden" name="pass1" value="' . $random_password . '" />';
		echo '<input id="wpcasldap_pass2" type="hidden" name="pass2" value="' . $random_password . '" />';
		return false;
	}

	/**
	* disable_function method hook for WordPress.
	*
	* Disable reset, lost, and retrieve password features in WordPress.
	*/
	function disable_function( ) {
		exit( __( 'Sorry, this feature is disabled.', 'wpcasldap' ) );
	}
}

/**
 * Modulo function that works with negative numbers
 *
 * PHP's standard modulo operator returns negative numbers as is without
 * modulus operation (ie -3 % 7 == -3 not the expected 3 % 7 == 4). This
 * function returns the expected negative module operation on negative numbers.
 *
 * @param int $num
 * @param int $mod
 * @return int
 */
function true_modulo( $num, $mod ) {
	return ( $mod + ( $num % $mod ) ) % $mod;
}

/**
 * Generate secure passwords of random length
 *
 * Generate passwords that have printable keyboard characters that are a
 * random length between the $min and $max paramters passed to the function.
 *
 * @param int $min the minimum length of the random password to generate
 * @param int $max the maximum length of the random password ro generate
 * @return string a random password that is a random length between the $min
 *                and $max paramter values
 */
function generate_password( $min, $max ) {
	$password = '';

	/*
	 * Generate cryptographically strong pseudo-random bytes as $bytes using the
	 * openssl_random_pseudo_bytes fuction. Number of bytes generated determined
	 * by $min and $max variables passed to this function.
	 */
	do {
		$bytes = openssl_random_pseudo_bytes( mt_rand( $min, $max ), $crypto_strong );
	} while ( false === $crypto_strong );

	/*
	 * Iterate through $bytes one byte at a time and modulus each byte's decimal
	 * value to be between 33 and 126 for valid keyboard characters on the ASCII
	 * table.
	 */
	foreach ( str_split( $bytes ) as $byte ) {
		$integer_value = ord( $byte );
		$integer_value = true_modulo( ( $integer_value - 33 ), 94 ) + 33;
		$password .= chr( $integer_value );
	}

	return $password;
}

/**
 * wp_cas_ldap_now_puser function
 *
 * @param string $new_user_id the username of a user
 */
function wp_cas_ldap_now_puser( $new_user_id ) {
	global $wp_cas_ldap_use_options;
	$user_data = '';
	if ( 'yes' === $wp_cas_ldap_use_options['useldap'] && function_exists( 'ldap_connect' ) ) {
		$new_user = get_ldap_user( $new_user_id );

		if ( $new_user ) {
			$user_data = $new_user->get_user_data( );
		} else {
			error_log( 'User not found on LDAP Server: ' . $new_user_id );
		}
	} else {
		$user_data = array(
			'user_login' => $new_user_id,
			'user_pass'  => generate_password( 32, 64 ),
			'user_email' => $new_user_id . '@' . $wp_cas_ldap_use_options['email_suffix'],
			'role'       => $wp_cas_ldap_use_options['userrole'],
		);
	}

	if ( ! function_exists( 'wp_insert_user' ) ) {
		include_once ( ABSPATH . WPINC . '/registration.php' );
	}

	if ( $user_data ) {
		$user_id = wp_insert_user( $user_data );
		if ( is_wp_error( $user_id ) ) {
			$error_string = $user_id->get_error_message( );
			error_log( 'Inserting a user in wp failed: ' . $error_string );
			echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
			return;
		} else {
			wp_set_auth_cookie( $user_id );

			if ( isset( $_GET['redirect_to'] ) ) {
				wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url( ) );
				exit( );
			}

			wp_redirect( site_url( '/wp-admin/' ) );
			exit( );
		}
	}
}

/**
 * get_ldap_user function
 *
 * @param string $uid ldap sAMAccountName value to match
 * @return false|WP_CAS_LDAP_User returns WP_CAS_LDAP_User object as long as user is
 *                             found on the ldap server, otherwise false.
 */
function get_ldap_user( $uid ) {
	global $wp_cas_ldap_use_options;
	$ds = ldap_connect( $wp_cas_ldap_use_options['ldaphost'], $wp_cas_ldap_use_options['ldapport'] );
	//Can't connect to LDAP.
	if ( ! $ds ) {
		error_log('Error in contacting the LDAP server: ' . $wp_cas_ldap_use_options['ldaphost'] . ':' . $wp_cas_ldap_use_options['ldapport']);
	} else {
		// Make sure the protocol is set to version 3
		if ( ! ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 ) ) {
			error_log( 'Failed to set LDAP protocol to version 3.' );
		} else {
			// Get LDAP service sccount username
			$ldap_user = $GLOBALS['ldapUser'];
			// Get service account associated password
			$ldap_pass = $GLOBALS['ldapPassword'];
			$bind = ldap_bind( $ds, $ldap_user, $ldap_pass );

			//Check to make sure we're bound.
			if ( ! $bind ) {
				error_log( 'LDAP Bind failed with Service Account: ' . $ldap_user );
			} else {
				$search = ldap_search(
					$ds,
					$wp_cas_ldap_use_options['ldapbasedn'],
					'sAMAccountName=' . $uid,
					array(
						'uid',
						'mail',
						'givenname',
						'sn',
						'rolename',
						'cn',
						'EmployeeID',
						'sAMAccountName',
					)
				);
				$info = ldap_get_entries( $ds, $search );

				return new WP_CAS_LDAP_User( $info );
			}
		}
		ldap_close( $ds );
	}
	return false;
}

/**
 * sid_to_str function
 *
 * @param mixed $sid unknown as function is not called
 * @return string
 */
function sid_to_str( $sid ) {
	$srl           = ord( $sid[0] );
	$number_sub_id = ord( $sid[1] );
	$x             = substr( $sid, 2, 6 );
	$h             = unpack( 'N', "\x0\x0" . substr( $x, 0, 2 ) );
	$l             = unpack( 'N', substr( $x, 2, 6 ) );
	$iav           = bcadd( bcmul( $h[1], bcpow( 2, 32 ) ), $l[1] );
	for ( $i = 0; $i < $number_sub_id; $i++ ) {
		$sub_id   = unpack( 'V', substr( $sid, 8 + 4 * $i, 4 ) );
		$sub_ids[] = $sub_id[1];
	}
	return sprintf( 'S-%d-%d-%s', $srl, $iav, implode( '-', $sub_ids ) );
}

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

/*
 *----------------------------------------------------------------------------
 *                         ADMIN OPTION PAGE FUNCTIONS
 *----------------------------------------------------------------------------
 */

/**
 * wp_cas_ldap_register_settings function
 */
function wp_cas_ldap_register_settings( ) {
	global $wp_cas_ldap_options;

	$options = array(
		'email_suffix',
		'cas_version',
		'include_path',
		'server_hostname',
		'server_port',
		'server_path',
		'useradd',
		'userrole',
		'ldaphost',
		'ldapport',
		'ldapbasedn',
		'useldap',
	);

	foreach ( $options as $o ) {
		if ( ! isset( $wp_cas_ldap_options[ $o ] ) ) {
			switch ( $o ) {
				case 'cas_verion':
					$cleaner = 'wp_cas_ldap_one_or_two';
					break;
				case 'useldap':
					$cleaner = 'wp_cas_ldap_yes_or_no';
					break;
				case 'email_suffix':
					$cleaner = 'wp_cas_ldap_strip_at';
					break;
				case 'userrole':
					$cleaner = 'wp_cas_ldap_fix_user_role';
					break;
				case 'server_port':
					$cleaner = 'intval';
					break;
				default:
					$cleaner = 'wp_cas_ldap_dummy';
			}
			register_setting('wpcasldap', 'wpcasldap_' . $o, $cleaner);
		}
	}
}

/**
 * wp_cas_ldap_strip_at function
 *
 * @param string $in domain suffix in email address.
 * @return string domain suffix without '@' symbol.
 */
function wp_cas_ldap_strip_at( $in ) {
	return str_replace( '@', '', $in );
}

/**
 * wp_cas_ldap_yes_or_no function
 *
 * @param string $in value is 'yes' or anything else.
 * @return string value will be 'yes or 'no'.
 */
function wp_cas_ldap_yes_or_no( $in ) {
	return ( 'yes' === strtolower( $in ) ) ? 'yes' : 'no';
}

/**
 * wp_cas_ldap_one_or_two function
 *
 * @param string $in value is '1.0' or anything else.
 * @return string value will be '1.0' or '2.0'.
 */
function wp_cas_ldap_one_or_two( $in ) {
	return ( '1.0' === $in ) ? '1.0' : '2.0';
}

/**
 * wp_cas_ldap_fix_user_role function
 *
 * @param string $in value is 'subscriber', 'contributor', 'author', 'editor',
 *                  'administrator', or anything else.
 * @return string value will be 'subscriber', 'contributor', 'author',
 *                'editor', or 'administrator'.
 */
function wp_cas_ldap_fix_user_role( $in ) {
	$roles = array(
		'subscriber',
		'contributor',
		'author',
		'editor',
		'administrator',
	);
	if ( in_array( $in, $roles ) ) {
		return $in;
	} else {
		return 'subscriber';
	}
}

/**
 * wp_cas_ldap_dummy function
 *
 * @param string $in domain suffix in email address.
 * @return string domain suffix without @ symbol.
 */
function wp_cas_ldap_dummy( $in ) {
	return $in;
}

/**
 * cas_client_settings function hook for WordPress.
 */
function cas_client_settings( ) {
	add_submenu_page(
		'settings.php',
		'CAS Client',
		'CAS Client',
		'manage_network',
		'casclient',
		'wp_cas_ldap_options_page'
	);
}

/**
 * wp_cas_ldap_options_page_add function hook for WordPress.
 */
function wp_cas_ldap_options_page_add( ) {
	if ( function_exists( 'add_management_page' ) ) {
		add_submenu_page(
		  'options-general.php',
			'CAS Client',
			'CAS Client',
			CAPABILITY,
			'wpcasldap',
			'wp_cas_ldap_options_page'
		);
	} else {
		add_options_page(
			'CAS Client',
			'CAS Client',
			CAPABILITY,
			basename(__FILE__),
			'wp_cas_ldap_options_page'
		);
	}
}

/**
 * wp_cas_ldap_get_options function hook for WordPress.
 *
 * @return array contains plugin configuration options from database.
 */
function wp_cas_ldap_get_options( ) {
	global $wp_cas_ldap_options;
	global $get_options_func;

	$out = array (
		'email_suffix'    => $get_options_func( 'wpcasldap_email_suffix' ),
		'cas_version'     => $get_options_func( 'wpcasldap_cas_version' ),
		'include_path'    => $get_options_func( 'wpcasldap_include_path' ),
		'server_hostname' => $get_options_func( 'wpcasldap_server_hostname' ),
		'server_port'     => $get_options_func( 'wpcasldap_server_port' ),
		'server_path'     => $get_options_func( 'wpcasldap_server_path' ),
		'useradd'         => $get_options_func( 'wpcasldap_useradd' ),
		'userrole'        => $get_options_func( 'wpcasldap_userrole' ),
		'ldaphost'        => $get_options_func( 'wpcasldap_ldaphost' ),
		'ldapport'        => $get_options_func( 'wpcasldap_ldapport' ),
		'useldap'         => $get_options_func( 'wpcasldap_useldap' ),
		'ldapbasedn'      => $get_options_func( 'wpcasldap_ldapbasedn' ),
	);

	if ( is_array( $wp_cas_ldap_options ) && 0 < count( $wp_cas_ldap_options ) ) {
		foreach ( $wp_cas_ldap_options as $key => $val ) {
			$out[ $key ] = $val;
		}
	}
	return $out;
}

/**
 * wp_cas_ldap_options_page function hook for WordPress.
 */
function wp_cas_ldap_options_page( ) {
	global $wp_cas_ldap_options, $form_action;

	// Get Options
	$option_array_def = wp_cas_ldap_get_options( );
?>
	<div class="wrap">
	<h2>CAS Client</h2>
<?php
	echo '<form method="post" action="' . $form_action . '">';
	settings_fields( 'wpcasldap' );
	echo '<h3>';
	_e( 'Configuration settings for WordPress CAS Client', 'wpcasldap' );
	echo '</h3>';

	echo '<h4>';
	_e( 'Note', 'wpcasldap' );
	echo '</h4>';
?>
<p>
<?php
	_e( 'Now that you’ve activated this plugin, WordPress is attempting to authenticate using CAS, even if it’s not configured or misconfigured.', 'wpcasldap' );
	echo '<br />';
	_e( 'Save yourself some trouble, open up another browser or use another machine to test logins. That way you can preserve this session to adjust the configuration or deactivate the plugin.', 'wpcasldap' );
	echo '"';
?>
</p>
<?php
	if ( ! isset( $wp_cas_ldap_options['include_path'] ) ) {
		echo '<h4>';
		_e( 'phpCAS include path', 'wpcasldap' );
		echo '</h4>';
?>
<p>
<?php
		echo '<small><em>';
		_e( 'Note: The phpCAS library is required for this plugin to work. We need to know the server path to the CAS.php file.', 'wpcasldap' );
		echo '</em></small>';
?>
</p>

		<table class="form-table">

	        <tr valign="top">
				<th scope="row">
					<label>
<?php
		_e( 'CAS.php Path', 'wpcasldap' );
?>
					</label>
				</th>

				<td>
<?php
		echo '<input type="text" size="80" name="wpcasldap_include_path" id="include_path_inp" value="' . $option_array_def['include_path'] . '" />';
?>
				</td>
			</tr>

		</table>
<?php
	}

	if ( ! isset( $wp_cas_ldap_options['cas_version'] ) ||
      ! isset( $wp_cas_ldap_options['server_hostname'] ) ||
			! isset( $wp_cas_ldap_options['server_port'] ) ||
			! isset( $wp_cas_ldap_options['server_path'] ) ) {
		echo '<h4>';
		_e( 'phpCAS::client() parameters', 'wpcasldap' );
		echo '</h4>';
?>
	<table class="form-table">
<?php
		if ( ! isset( $wp_cas_ldap_options['cas_version'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
		_e( 'CAS version', 'wpcasldap' );
?>
				</lable>
			</th>

			<td>
				<select name="wpcasldap_cas_version" id="cas_version_inp">
<?php
		echo '<option value="2.0" ';
		echo ( '2.0' === $option_array_def['cas_version'] ) ? 'selected' : '';
		echo '>CAS_VERSION_2_0</option>';
		echo '<option value="1.0" ';
		echo ( '1.0' === $option_array_def['cas_version'] ) ? 'selected' : '';
		echo '>CAS_VERSION_1_0</option>';
?>
				</select>
			</td>
		</tr>
<?php
	}

		if ( ! isset( $wp_cas_ldap_options['server_hostname'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Server Hostname', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_server_hostname" id="server_hostname_inp" value="' . $option_array_def['server_hostname'] . '" />'
?>
			</td>
		</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['server_port'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Server Port', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_server_port" id="server_port_inp" value="' . $option_array_def['server_port'] . '" />';
?>
			</td>
		</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['server_path'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Server Path', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_server_path" id="server_path_inp" value="' . $option_array_def['server_path'] . '" />';
?>
			</td>
		</tr>
<?php
		}
?>
	</table>
<?php
	}

	if ( ! isset($wp_cas_ldap_options['useradd'] ) ||
		! isset( $wp_cas_ldap_options['userrole'] ) ||
		! isset( $wp_cas_ldap_options['useldap'] ) ||
		! isset( $wp_cas_ldap_options['email_suffix'] ) ) {

		echo '<h4>';
		_e( 'Treatment of Unregistered User', 'wpcasldap' );
		echo '</h4>';
?>
		<table class="form-table">
<?php
		if ( ! isset( $wp_cas_ldap_options['useradd'] ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label>
<?php
			_e( 'Add to Database', 'wpcasldap' );
?>
					</lable>
				</th>

				<td>

<?php
			echo '<input type="radio" name="wpcasldap_useradd" id="useradd_yes" value="yes" ';
			echo ( 'yes' === $option_array_def['useradd'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="useradd_yes">Yes &nbsp;</label>
<?php
			echo '<input type="radio" name="wpcasldap_useradd" id="useradd_no" value="no" ';
			echo ( 'yes' !== $option_array_def['useradd'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="useradd_no">No &nbsp;</label>
				</td>
			</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['userrole'] ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label>
<?php
			_e( 'Default Role', 'wpcasldap' );
?>
					</label>
				</th>

				<td>
					<select name="wpcasldap_userrole" id="cas_version_inp">
<?php
						echo '<option value="subscriber" ';
						echo ( 'subscriber' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Subscriber</option>';

						echo '<option value="contributor" ';
						echo ( 'contributor' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Contributor</option>';

						echo '<option value="author" ';
						echo ('author' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Author</option>';

						echo '<option value="editor" ';
						echo ( 'editor' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Editor</option>';

						echo '<option value="administrator" ';
						echo ( 'administrator' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Administrator</option>';
?>
	                </select>
	            </td>
			</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['useldap'] ) ) {
?>
				<tr valign="top">
					<th scope="row">
						<label>
<?php
			_e( 'Use LDAP to get user info', 'wpcasldap' );
?>
						</label>
					</th>

					<td>
<?php
			echo '<input type="radio" name="wpcasldap_useldap" id="useldap_yes" value="yes" ';
			echo ( 'yes' === $option_array_def['useldap'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
						<label for="useldap_yes">Yes &nbsp;</label>

<?php
			echo '<input type="radio" name="wpcasldap_useldap" id="useldap_no" value="no" ';
			echo ( 'yes' !== $option_array_def['useldap'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
						<label for="useldap_no">No &nbsp;</label>
					</td>
				</tr>
<?php
		} else {
?>
					<input type="hidden" name="wpcasldap_useldap" id="useldap_hidden" value="no" />
<?php
		}
	}

	if ( ! isset( $wp_cas_ldap_options['email_suffix'] ) ) {
?>
		   <tr valign="center">
				<th scope="row">
					<label>
<?php
		_e('E-mail Suffix', 'wpcasldap')
?>
					</label>
				</th>

				<td>
<?php
		echo '<input type="text" size="50" name="wpcasldap_email_suffix" id="server_port_inp" value="';
		echo $option_array_def['email_suffix'];
		echo '" />';
?>
				</td>
			</tr>
<?php
	}
?>
		</table>

<?php

	if ( function_exists( 'ldap_connect' ) ) {
		if ( ! isset( $wp_cas_ldap_options['ldapbasedn'] ) ||
				! isset( $wp_cas_ldap_options['ldapport'] ) ||
				! isset( $wp_cas_ldap_options['ldaphost'] ) ) {
			echo '<h4>';
			_e( 'LDAP parameters', 'wpcasldap' );
			echo '</h4>';
?>

	<table class="form-table">
<?php
			if ( ! isset( $wp_cas_ldap_options['ldaphost'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Host', 'wpcasldap' )
?>
				</label>
			</th>

			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldaphost" id="ldap_host_inp" value="';
				echo $option_array_def['ldaphost'];
				echo '" />';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldapport'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Port', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldapport" id="ldap_port_inp" value="';
				echo $option_array_def['ldapport'];
				echo '"  />';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldapbasedn'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Base DN', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldapbasedn" id="ldap_basedn_inp" value="';
				echo $option_array_def['ldapbasedn'];
				echo '" />';
?>
			</td>
		</tr>
<?php
			}
?>
	</table>
<?php
		}
	}
?>
	<div class="submit">
		<input type="submit" name="wpcasldap_submit" value="Save" />
	</div>
	</form>
<?php
}
