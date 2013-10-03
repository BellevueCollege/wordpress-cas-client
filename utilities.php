<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shawn.south@bellevuecollege.edu
 * Date: 9/25/13
 * Time: 4:40 PM
 * To change this template use File | Settings | File Templates.
 */

// Disable to turn off debug logging
define("ENABLE_DEBUG_LOG", true);
define("DEBUG_LOG_PATH", "/var/tmp/wordpress-cas-client-debug.log");

/**
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function str_starts_with($haystack, $needle)
{
  $idx = stripos($haystack, $needle);

  return (false !== $idx && 0 == $idx);
}

/**
 * @param $message
 */
function debug_log($message)
{
  if (ENABLE_DEBUG_LOG)
  {
    $date = new DateTime();
    $current_site = get_current_site();
    if (!error_log(trim($current_site->path, "/").": [".$date->format('Y-m-d H:i:s')."] ".$message . "\n", 3, DEBUG_LOG_PATH))
    {
      error_log("UNABLE TO WRITE TO DEBUG LOG (" . DEBUG_LOG_PATH . "): '" . $message . "'");
    }
  }
}

function class_autoloader($class)
{
  $classFile = dirname(__FILE__) . "/" . $class . ".php";

  if (file_exists($classFile))
  {
    include_once($classFile);
  }
  else
  {
    $error = "Failed to load '".$classFile."'";
    debug_log($error);
    error_log($error);
  }
}

?>