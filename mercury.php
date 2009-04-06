#!/usr/bin/php -q
<?php
/**
 * System_Daemon turns PHP-CLI scripts into daemons.
 * 
 * PHP version 5
 *
 * @category  System
 * @package   System_Daemon
 * @author    Kevin <kevin@vanzonneveld.net>
 * @copyright 2008 Kevin van Zonneveld
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @version   SVN: Release: $Id: logparser.php 183 2009-02-17 14:02:53Z kevin $
 * @link      http://trac.plutonia.nl/projects/system_daemon
 */
 
/**
 * System_Daemon Example Code
 * 
 * If you run this code successfully, a daemon will be spawned
 * but unless have already generated the init.d script, you have
 * no real way of killing it yet.
 * 
 * In this case wait 3 runs, which is the maximum for this example. 
 * 
 * 
 * In panic situations, you can always kill you daemon by typing
 * 
 * killall -9 logparser.php
 * OR:
 * killall -9 php
 * 
 */
 
// Allowed arguments & their defaults 
$runmode = array(
    "no-daemon" => false, 
    "help" => false,
    "write-initd" => false
);
 
// Scan command line attributes for allowed arguments
foreach ($argv as $k=>$arg) {
    if (substr($arg, 0, 2) == "--" && isset($runmode[substr($arg, 2)])) {
        $runmode[substr($arg, 2)] = true;
    }
}
 
// Help mode. Shows allowed argumentents and quit directly
if ($runmode["help"] == true) {
    echo "Usage: ".$argv[0]." [runmode]\n";
    echo "Available runmodes:\n"; 
    foreach ($runmode as $runmod=>$val) {
        echo " --".$runmod."\n";
    }
    die();
}
 
// Make it possible to test in source directory
// This is for PEAR developers only
ini_set('include_path', ini_get('include_path').':..');
 
// Include Class
error_reporting(E_ALL);
require_once "System/Daemon.php";
 
// Setup
$options = array(
    "appName" => "mercury",
    "appDir" => dirname(__FILE__),
    "appDescription" => "Informs the user of their social interactions.",
    "authorName" => "Alastair Dewar",
    "authorEmail" => "alastair@alastairdewar.co.uk",
    "sysMaxExecutionTime" => "0",
    "sysMaxInputTime" => "0",
    "sysMemoryLimit" => "256M",
    "appRunAsGID" => 1000,
    "appRunAsUID" => 1000
);
 
System_Daemon::setOptions($options);
 
// Overrule the signal handler with any function
System_Daemon::setSigHandler(SIGCONT, array("System_Daemon",
    "defaultSigHandler"));
 
 
// This program can also be run in the forground with runmode --no-daemon
if (!$runmode["no-daemon"]) {
    // Spawn Daemon 
    System_Daemon::start();
}

// With the runmode --write-initd, this program can automatically write a 
// system startup file called: 'init.d'
// This will make sure your daemon will be started on reboot 
if (!$runmode["write-initd"]) {
    System_Daemon::log(System_Daemon::LOG_INFO, "not writing ".
        "an init.d script this time");
} else {
    if (($initd_location = System_Daemon::writeAutoRun()) === false) {
        System_Daemon::log(System_Daemon::LOG_NOTICE, "unable to write ".
            "init.d script");
    } else {
        System_Daemon::log(System_Daemon::LOG_INFO, "sucessfully written ".
            "startup script: ".$initd_location);
    }
}
 
// This variable gives your own code the ability to breakdown the daemon:
$runningOkay = true;
 
require("Mercury.class.php");
$core = new Mercury();

if(!$core->setup_account("twitter", "example", "example"))
{
	$runningOkay = false;
	System_Daemon::log(System_Daemon::LOG_ERR,
    System_Daemon::getOption("appName").
    " failed to set up Twitter account.");
}


// While checks on 2 things in this case:
// - That the Daemon Class hasn't reported it's dying
// - That your own code has been running Okay
while (!System_Daemon::isDying() && $runningOkay) {
    // What mode are we in?
    $mode = "'".(System_Daemon::isInBackground() ? "" : "non-" ).
        "daemon' mode";
    
    // Log something using the Daemon class's logging facility
    // Depending on runmode it will either end up:
    //  - In the /var/log/mercury.log
    //  - On screen (in case we're not a daemon yet)  
    System_Daemon::log(System_Daemon::LOG_INFO,
        System_Daemon::getOption("appName").
        " checking all protocols");
    
    $core->check_all_protocols();
        
    // In the actuall logparser program, You could replace 'true'
    // With e.g. a  parseLog('vsftpd') function, and have it return
    // either true on success, or false on failure.
    $runningOkay = true;
    //$runningOkay = parseLog('vsftpd');
    
    // Should your parseLog('vsftpd') return false, then
    // the daemon is automatically shut down.
    // An extra log entry would be nice, we're using level 3,
    // which is critical.
    // Level 4 would be fatal and shuts down the daemon immediately,
    // which in this case is handled by the while condition.
    if (!$runningOkay) {
        System_Daemon::log(System_Daemon::LOG_ERR, "parseLog() ".
            "produced an error, ".
            "so this will be my last run");
    }
    
    // Relax the system by sleeping for a little bit
    // NOTE Cannot query twitter more than 100 times per hour
    sleep(60);
}
 
// Shut down the daemon nicely
// This is ignored if the class is actually running in the foreground
System_Daemon::stop();
?>