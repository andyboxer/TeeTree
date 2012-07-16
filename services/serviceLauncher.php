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

$dir = getenv("SERVICE_CLASS_PATH");
if(!$dir)
{
    $dir = __DIR__;
}

if(!$port = getenv("SERVICE_PORT"))
{
    $port = 2000;
}
$logger = new logger("/tmp/serviceLauncher.log");
$logger->log("Service launcher started", SERVICE_LAUNCHER_START, 'service launcher');
$logger->log("Service Class Path  ". $dir, SERVICE_LAUNCHER_START, 'service launcher');
$logger->log("Service Server Port ". $port, SERVICE_LAUNCHER_START, 'service launcher');
$controller = new serviceController($dir, $port);

?>