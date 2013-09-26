<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ssouth
 * Date: 9/25/13
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */

include_once(dirname(__FILE__)."/utilities.php");

class ldapManager
{
    const OPT_PROTOCOL_VERSION = LDAP_OPT_PROTOCOL_VERSION;

    // fields
    private $connection = null;

    // properties
    public $SecurePort = 636;


    // interface methods
    public function Connect($host, $port)
    {
        debug_log("Establishing LDAP connection: ".$host.":".$port."...");

        try
        {
            $idx = stripos($host, "ldaps://");

            if (false !== $idx && 0 == $idx)
            {
                debug_log("LDAPS detected.");
                if(!is_numeric($port) || intval($port) <= 0)
                {
                    // use default port for LDAPS
                    $port = strval($this->SecurePort);
                    debug_log("No port specified - using default (".$port.")");
                }
            }
            else
            {
                debug_log("LDAPS not specified - establishing UNENCRYPTED connection.");
                if(!is_numeric($port) || intval($port) <= 0)
                {
                    debug_log("No port specified - using default.");
                    // use default port settings
                    $this->connection = ldap_connect($host);
                }
            }

            // By this point $port should be non-negative. Ensure that it's also an integer.
            $this->connection = ldap_connect($host, strval(intval($port)));
        }
        catch (Exception $e)
        {
            $error_msg = "LDAP connection to '" . $host . "' failed: " . $e->getMessage();
            debug_log($error_msg);
            error_log($error_msg);
        }
        return $this->connection;
    }

    public function Close()
    {
        if(isset($this->connection))
        {
            ldap_close($this->connection);
        }
        else
        {
            debug_log("No connection exists, so call to Close() skipped.");
        }
    }

    public function Bind($login, $password)
    {
        if(isset($this->connection))
        {
            return @ldap_bind($this->connection, $login, $password);
        }
        error_log("Unable to Bind() to LDAP until a connection has been established.");
        return false;
    }

    public function SetOption($flag, $value)
    {
        if(isset($this->connection))
        {
            return ldap_set_option($this->connection, $flag, $value);
        }
        error_log("Unable to set LDAP options until a connection has been established.");
        return false;
    }

    public function Search($base_dn, $filter, $attribute_array)
    {
        if(isset($this->connection))
        {
            return ldap_search($this->connection, $base_dn, $filter, $attribute_array);
        }
        error_log("Unable to search LDAP until a connection has been established.");
        return null;
    }

    public function GetSearchResults($search_results)
    {
        if(isset($this->connection))
        {
            return ldap_get_entries($this->connection, $search_results);
        }
        error_log("Unable to retrieve LDAP search results until a connection has been established.");
        return null;
    }
}