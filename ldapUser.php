<?php
include_once( dirname(__FILE__)."/utilities.php");

/**
 * Created by JetBrains PhpStorm.
 * User: ssouth
 * Date: 10/1/13
 * Time: 5:45 PM
 * To change this template use File | Settings | File Templates.
 */

class ldapUser
{
  private $data = NULL;

  function __construct($member_array)
  {
    $this->data = $member_array;
  }

  function GetName()
  {
    if(isset($this->data[0]['cn'][0]))
      return $this->data[0]['cn'][0];
    else
      return FALSE;
  }

  function GetData()
  {
    if (isset($this->data[0]['uid'][0]) || isset($this->data[0]['employeeid'][0])) // updating the if to have employeeid check also
    {
      error_log("potential bug uid :".$this->data[0]['uid'][0]);
      $usernicename = sanitize_title_with_dashes($this->data[0]['samaccountname'][0]);
      debug_log("user nice name ".$usernicename);
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
        'user_nicename' => $usernicename
      );
    }
    else
      return false;
  }
}
