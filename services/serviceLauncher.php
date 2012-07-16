<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

if(!defined('BASE_PATH')) define('BASE_PATH', realpath(dirname(__FILE__).  '/../'). '/');
if(!defined('DEFAULT_DATASET')) define('DEFAULT_DATASET', 'serviceController');
if(!defined('SERVICE_LOGFILE')) define('SERVICE_LOGFILE', '/tmp/serviceService.log');
require_once __DIR__. '/../config/base.php';

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