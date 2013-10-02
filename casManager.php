<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ssouth
 * Date: 10/1/13
 * Time: 6:34 PM
 * To change this template use File | Settings | File Templates.
 */
include_once( dirname(__FILE__)."/wordpress-cas-client.php");
include_once(dirname(__FILE__) . "/ldapManager.php");
include_once(dirname(__FILE__) . "/ldapUser.php");

class casManager
{
  private $cas_configured = true;
  private $ldapManager;
  private $options;

  function __construct()
  {
    $options = wpcasldap_getoptions();
    $this->options = $options;

    $this->ConfigureCasClient($options);

    $this->ldapManager = new ldapManager($options['ldapuser'], $options['ldappassword'], $options['ldapuri']);
  }

  /*
   We call phpCAS to authenticate the user at the appropriate time
   (the script dies there if login was unsuccessful)
   If the user is not provisioned and wpcasldap_useradd is set to 'yes', wpcasldap_nowpuser() is called
  */
  function authenticate()
  {
    global $blog_id;

//    if ( !$this->cas_configured )
//      die( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ));

    if( phpCAS::isAuthenticated() ){
      // CAS was successful

      if ( $user = get_user_by( 'login', phpCAS::getUser() )){ // user already exists
        debug_log("correct login");
        // Update user information from ldap
        if ($this->options['useldap'] == 'yes' && function_exists('ldap_connect') ) {

//						$existingUser = get_ldap_user(phpCAS::getUser());
          $existingUser = $this->ldapManager->GetUser(phpCAS::getUser(), $this->options["ldapbasedn"]);
          //var_dump($existingUser);
          if($existingUser)
          {

            $userdata = $existingUser->GetData();
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
          if (function_exists('add_user_to_blog')) { add_user_to_blog($blog_id, $user->ID, $this->options['userrole']); }
        }

        // the CAS user has a WP account
        wp_set_auth_cookie( $user->ID );

        if( isset( $_GET['redirect_to'] )){
          wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url(  ));
          debug_log("check if die1 :".$_GET['redirect_to']);
          debug_log("compare returns :".preg_match( '/^http/', $_GET['redirect_to']));

          die();
        }
        debug_log("check if die2");
        wp_redirect( site_url( '/wp-admin/' ));
        die();

      }else{
        // the CAS user _does_not_have_ a WP account

        echo "Coming here";
        if (function_exists( 'wpcasldap_nowpuser' ) && $this->options['useradd'] == 'yes')
        {
          //error_log("check if die3");
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
    if (!$this->cas_configured)
      die( __( 'WordPress CAS Client plugin not configured', 'wpcasldap' ));

    phpCAS::logout( array( 'url' => get_option_wrapper( 'siteurl' )));
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

  private function ConfigureCasClient($options)
  {
    /** @noinspection PhpIncludeInspection */
    if ($options['include_path'] == '' || (include_once $options['include_path']) != true)
    {
      $this->cas_configured = false;
    }

    if ($options['server_hostname'] == '' || $options['server_path'] == '' || intval($options['server_port']) == 0)
    {
      $this->cas_configured = false;
    }

    if ($this->cas_configured)
    {
      phpCAS::client($options['cas_version'],
                     $options['server_hostname'],
                     intval($options['server_port']),
                     $options['server_path']);

      // function added in phpCAS v. 0.6.0
      // checking for static method existance is frustrating in php4
      $phpCas = new phpCas();

      if (method_exists($phpCas, 'setNoCasServerValidation'))
      {
        phpCAS::setNoCasServerValidation();
      }
      unset($phpCas);
      // if you want to set a cert, replace the above few lines
    }
  }
}