<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

set_include_path(get_include_path(). PATH_SEPARATOR. __DIR__ . PATH_SEPARATOR. realpath(__DIR__. "/../library"));
require_once 'logger.php';
require_once 'serviceController.php';

$logger = new logger("/tmp/serviceLauncher.log");

if(isset($argv) && (count($argv) === 4))
{
    $logger->log("Service launcher started", SERVICE_LAUNCHER_START, 'service launcher');
    $logger->log("Service Class Path  ". $argv[2], SERVICE_LAUNCHER_START, 'service launcher');
    $logger->log("Service Server Port ". $argv[1], SERVICE_LAUNCHER_START, 'service launcher');
    if($argv[3] === 'start')
    {
        $controller = new serviceController($argv[2], $argv[1]);
    }
    elseif($argv[3] === 'stop')
    {
        serviceController::stopServer('localhost', $argv[1]);
    }
}
else
{
    echo <<<USAGE
Usage: php serviceLauncher.php <ServicePort> <ClassPath> <stop|start>


USAGE;

}

?>