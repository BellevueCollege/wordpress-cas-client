<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shawn.south@bellevuecollege.edu
 * Date: 9/25/13
 * Time: 4:40 PM
 * To change this template use File | Settings | File Templates.
 */

function debug_log($message)
{
    $debug_log_path = "/var/tmp/wordpress-cas-client-debug.log";
    if(!error_log($message."\n", 3, $debug_log_path))
    {
        error_log("UNABLE TO WRITE TO DEBUG LOG (".$debug_log_path."): '".$message."'");
    }
}

?>