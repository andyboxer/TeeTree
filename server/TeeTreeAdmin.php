<?php
/**
 * @package TeeTreeExample
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

// Now we call the bootstrap to setup the TeeTree environment ( I think I'll call this a Kettle :)
require_once( __DIR__. "/../bootstrap/TeeTreeBootStrap.php");

$command = (isset($argv[1])) ? $argv[1] : '';
list($port, $classPath) = TeeTreeParseStartupParams($argv);

switch($command)
{
    case 'boot':

        // We start the service controller passing a port nunber and the class path to our services classes ( This is strictly one directory intentionally and must be r/o )
        try
        {
            requireConfig($classPath);
            $controller = new TeeTreeController($classPath, $port);
        }
        catch(TeeTreeException $ex)
        {
            print("Failed to start TeeTree Controller error message = ". $ex->getMessage());
        }
        // This process will now continue untill the TeeTreeController::stopServer call is made using the same port no.
        break;

    case "start":

        requireConfig($classPath);
        // Now we can get started and kick off the TeeTree Controller process
        print("TeeTree controller starting  on port {$port} ...\n");
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
        $pong = TeeTreeController::pingServer(TeeTreeConfiguration::TEETREE_SERVER_HOST,$port);
        print("TeeTree controller says '{$pong}'\n");
        break;

    case "stop":

        requireConfig($classPath);
        // So we want to bail, kill the server
        print("TeeTree controller shutting down  on port {$port} ...\n");
        TeeTreeController::stopServer(TeeTreeConfiguration::TEETREE_SERVER_HOST, $port);
        print("TeeTree controller stopped ...\n");
        break;

    default:
        print("Usage: php TeeTreeAdmin.php start|stop|count|ping\n");
        break;

}

function TeeTreeParseStartupParams($argv)
{
    // Getparams from the enironment if set
    $classPath = getenv("TEETREE_CLASS_PATH");
    $port = getenv("TEETREE_CONTROLLER_PORT");
    // if not set then try to get them from script parameters
    if(!($classPath && $port))
    {
        if(count($argv) === 4)
        {
            $classPath = $argv[2];
            $port = $argv[3];
        }
        else
        {
            print("Missing class path or port, Service cannot start");
            die();
        }
    }
    return array($port, $classPath);
}

function requireConfig($classPath)
{
    if(file_exists($classPath. "/TeeTreeConfiguration.php"))
    {
        require_once $classPath. "/TeeTreeConfiguration.php";
    }
    else
    {
        print("Unable to find TeeTreeConfiguration file '". $classPath. "/TeeTreeConfiguration.php'. Service cannot start");
        die();
    }
}

?>