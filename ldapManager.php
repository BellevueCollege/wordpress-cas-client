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
   * The URL scheme to use for unencrypted LDAP connections
   */
  const URI_SCHEME = "ldap";
  /**
   * The URL scheme to use for encrypted SSL LDAP connections
   */
  const SSL_URI_SCHEME = "ldaps";
  /**
   * The default port to use for unencrypted LDAP connections
   */
  const DEFAULT_PORT = "389";
  /**
   * The default port to use for encrypted SSL LDAP connections
   */
  const SSL_DEFAULT_PORT = "636";
  /**
   * Option flag for setting the protocol version.
   */
  const OPT_PROTOCOL_VERSION = LDAP_OPT_PROTOCOL_VERSION;

  // fields
  /**
   * The LDAP connection
   *
   * @var null
   */
  private $connection = null;

  //  unset properties
  /**
   * The URL to use for LDAP connections.
   *
   * @var string
   */
  public $Uri = "";

  // interface methods
  /**
   * Connects to an LDAP server.
   *
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
      $scheme = empty($uri_parts["scheme"]) ? ldapManager::URI_SCHEME : $uri_parts["scheme"];

      if ($scheme == ldapManager::SSL_URI_SCHEME)
      {
        debug_log("LDAPS detected. (scheme == '$scheme')");
        $port = $this->SetPort($uri_parts, ldapManager::SSL_DEFAULT_PORT);
      }
      else
      {
        debug_log("LDAPS not specified - establishing UNENCRYPTED connection.");
        $port = $this->SetPort($uri_parts, ldapManager::DEFAULT_PORT);
      }

// Wordpress install complains that http_build_url() is not recognized. 9/30/2013 - shawn.south@bellevuecollege.edu
//      $connection_url = http_build_url("", $uri_parts, HTTP_URL_STRIP_PORT);
      $connection_url = $this->BuildUrl($scheme, $uri_parts);
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
   * Closes an LDAP connection.
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
   * Binds an LDAP connection by logging in.
   *
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
   * Sets an LDAP connection option.
   *
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
   * Performs an LDAP query.
   *
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
   * Parses restules returned from a call to Search()
   *
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
   * Parses a URI into its component parts.
   *
   * @param $uri
   *
   * @return mixed
   */
  public static function ParseUri(&$uri)
  {
    debug_log("Parsing URI: '$uri'");
    $components = parse_url($uri);
    debug_log("Parsed URI components: " . print_r($components, true));

    if (empty($components['host']) && !empty($components['path']))
    {
      debug_log("Empty 'host', but non-empty 'path' (".$components['path'].")");

      $ldap_uri = ldapManager::URI_SCHEME. "://". $uri;
      debug_log("url: " . $ldap_uri);

      $components = parse_url($ldap_uri);
      debug_log("components after editing uri:" . print_r($components, true));
    }
    return $components;
  }

  // Private methods

  /**
   * Sets the connection port.
   *
   * @param $uri_parts
   * @param $defaultPort
   *
   * @internal param $port
   * @return string
   */
  private function SetPort($uri_parts, $defaultPort)
  {
    if(empty($uri_parts["port"]))
    {
      return $defaultPort;
    }
    $port = $uri_parts["port"];

    $is_valid = is_numeric($port) || intval($port) > 0;

    if (!$is_valid)
    {
      debug_log("No port specified - using default (" . $defaultPort . ")");
      return $defaultPort;
    }
    return $port;
  }

  /**
   * Constructs a URL from its component parts.
   *
   * @param $scheme
   * @param $uri_parts
   *
   * @return string
   */
  public function BuildUrl($scheme, $uri_parts)
  {
    $hostpath = empty($uri_parts["host"]) ? $uri_parts["path"] : $uri_parts["host"] . (empty($uri_parts["path"]) ? "" : $uri_parts["path"]);
    return $scheme . "://" . $hostpath;
  }
}