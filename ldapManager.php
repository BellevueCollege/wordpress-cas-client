<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ssouth
 * Date: 9/25/13
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */

include_once(dirname(__FILE__) . "/utilities.php");

/**
 * Class ldapManager
 */
class ldapManager
{
  /**
   *
   */
  const URI_SCHEME = "ldap";
  /**
   *
   */
  const SSL_URI_SCHEME = "ldaps";
  /**
   *
   */
  const DEFAULT_PORT = "389";
  /**
   *
   */
  const SSL_DEFAULT_PORT = "636";
  /**
   *
   */
  const OPT_PROTOCOL_VERSION = LDAP_OPT_PROTOCOL_VERSION;

  // fields
  /**
   * @var null
   */
  private $connection = null;

  //  unset properties
  /**
   * @var string
   */
  public $Uri = "";

  // interface methods
  /**
   * @param string $uri
   *
   * @return null|resource
   */
  public function Connect($uri = "")
  {
    if ($uri == "")
    {
      debug_log("No hostname provided to Connect(), falling back on Uri property.");
      if ($this->Uri == "")
      {
        error_log("Unable to connect - no LDAP URI was provided.");
        return null;
      }
      $uri = $this->Uri;
    }
    else
    {
      $this->Uri = $uri;
    }
    debug_log("Establishing LDAP connection: " . $uri . "...");

    try
    {
      $uri_parts = ldapManager::ParseUri($uri);
      $port = $uri["port"];
      $scheme = $uri_parts["scheme"];

      if ($scheme == ldapManager::SSL_URI_SCHEME)
      {
        debug_log("LDAPS detected.");
        $port = $this->ValidateAndSetPort($port, ldapManager::SSL_DEFAULT_PORT);
      }
      else
      {
        debug_log("LDAPS not specified - establishing UNENCRYPTED connection.");
        $port = $this->ValidateAndSetPort($port, ldapManager::DEFAULT_PORT);
      }

      $connection_url = http_build_url("", $uri_parts, HTTP_URL_STRIP_PORT);
      debug_log("Connection URL: '" . $connection_url . "' on port [" . $port . "]");

      $this->connection = ldap_connect($connection_url, $port);
    }
    catch (Exception $ex)
    {
      $error_msg = "LDAP connection to '" . $uri . "' failed: " . $ex->getMessage();
      debug_log($error_msg);
      error_log($error_msg);
    }
    return $this->connection;
  }

  /**
   *
   */
  public function Close()
  {
    if (isset($this->connection))
    {
      ldap_close($this->connection);
    }
    else
    {
      debug_log("No connection exists, so call to Close() skipped.");
    }
  }

  /**
   * @param $login
   * @param $password
   *
   * @return bool
   */
  public function Bind($login, $password)
  {
    if (isset($this->connection))
    {
      return @ldap_bind($this->connection, $login, $password);
    }
    error_log("Unable to Bind() to LDAP until a connection has been established.");
    return false;
  }

  /**
   * @param $flag
   * @param $value
   *
   * @return bool
   */
  public function SetOption($flag, $value)
  {
    if (isset($this->connection))
    {
      return ldap_set_option($this->connection, $flag, $value);
    }
    error_log("Unable to set LDAP options until a connection has been established.");
    return false;
  }

  /**
   * @param $base_dn
   * @param $filter
   * @param $attribute_array
   *
   * @return null|resource
   */
  public function Search($base_dn, $filter, $attribute_array)
  {
    if (isset($this->connection))
    {
      return ldap_search($this->connection, $base_dn, $filter, $attribute_array);
    }
    error_log("Unable to search LDAP until a connection has been established.");
    return null;
  }

  /**
   * @param $search_results
   *
   * @return array|null
   */
  public function GetSearchResults($search_results)
  {
    if (isset($this->connection))
    {
      return ldap_get_entries($this->connection, $search_results);
    }
    error_log("Unable to retrieve LDAP search results until a connection has been established.");
    return null;
  }

  /**
   * @param $uri
   *
   * @return mixed
   */
  public static function ParseUri(&$uri)
  {
    $components = parse_url($uri);
    if (empty($components['host']) && !empty($components['path']))
    {
      debug_log("path :" . $components['path']);
      $ldap_uri = ldapManager::URI_SCHEME . $uri;
      debug_log("cas url :" . $ldap_uri);
      $components = parse_url($ldap_uri);
      debug_log("components after editing uri :" . print_r($components, true));
    }
    return $components;
  }

  /**
   * @param $port
   * @param $defaultPort
   *
   * @return string
   */
  private function ValidateAndSetPort($port, $defaultPort)
  {
    $is_valid = is_numeric($port) || intval($port) > 0;

    if (!$is_valid)
    {
      debug_log("No port specified - using default (" . $port . ")");
      return $defaultPort;
    }
    return $port;
  }
}