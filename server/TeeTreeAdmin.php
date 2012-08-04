<?php
/**
 * @package TeeTreeExample
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

// First ensure that it is our version of TeeTreeConfiguration that loads first
require_once( __DIR__. "/../testServices/TeeTreeConfiguration.php");

// Now we call the bootstrap to setup the TeeTree environment ( I think I'll call this a Kettle :)
require_once( __DIR__. "/../bootstrap/TeeTreeBootStrap.php");

$command = (isset($argv[1])) ? $argv[1] : '';

switch($command)
{
    case 'boot':
         // Check to see if we have class path and port parameters to process
        $classPath = '';
        $port = 0;
        if(count($argv) === 3)
        {
            $classPath = $argv[1];
            $port = $argv[2];
        }
        $classPath = ($classPath === '') ? TeeTreeConfiguration::TEETREE_SERVICE_CLASS_PATH : $classPath;
        $port = ($port === 0) ? TeeTreeConfiguration::TEETREE_SERVER_PORT : $port;

        // Now we can get started and kick off the TeeTree Controller process
        print("TeeTree controller starting  on port {$port} ...\n");
        // We start the service controller passing a port nunber and the class path to our services classes ( This is strictly one directory intentionally and must be r/o )
        try
        {
            $controller = new TeeTreeController($classPath, $port);
        }
        catch(TeeTreeException $ex)
        {
            print("Failed to start TeeTree Controller error message = ". $ex->getMessage());
        }
        // This process will now continue untill the TeeTreeController::stopServer call is made using the same port no.
        break;

    case "start":
        // Check to see if we have class path and port parameters to process
        $classPath = '';
        $port = 0;
        if(count($argv) === 3)
        {
            $classPath = $argv[1];
            $port = $argv[2];
        }
        $classPath = ($classPath === '') ? TeeTreeConfiguration::TEETREE_SERVICE_CLASS_PATH : $classPath;
        $port = ($port === 0) ? TeeTreeConfiguration::TEETREE_SERVER_PORT : $port;

        // Now we can get started and kick off the TeeTree Controller process
        print("TeeTree controller starting ...\n");
        // We start the service controller passing a port nunber and the class path to our services classes ( This is strictly one directory intentionally and must be r/o )
        TeeTreeController::startServer($classPath, $port);
        // This process will now continue untill the TeeTreeController::stopServer call is made using the same port no.
    break;

    case "count":
        // Return a count of the total number of listening remote service instances
        $count = TeeTreeController::getActiveServiceListenerCount();
        print("TeeTree active remote service instance servers = {$count}\n");
    break;

    case "ping":
        // Return a count of the total number of listening remote service instances
        $pong = TeeTreeController::pingServer('localhost', TeeTreeConfiguration::TEETREE_SERVER_PORT);
        print("TeeTree controller says '". (($pong) ? "ere I am" : "Tee who?"). "'\n");
    break;

    case "stop":
        // So we want to bail, kill the server
        TeeTreeController::stopServer('localhost', TeeTreeConfiguration::TEETREE_SERVER_PORT);
        print("TeeTree controller stopped ...\n");
    break;

    default:
        print("Usage: php ControllerLauncher.php start|stop|count|ping\n");
    break;

}

?>