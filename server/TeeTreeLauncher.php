<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

set_include_path(get_include_path(). PATH_SEPARATOR. __DIR__ . PATH_SEPARATOR. realpath(__DIR__. "/../library"));
require_once 'TeeTreeLogger.php';
require_once 'TeeTreeController.php';

$TeeTreeLogger = new TeeTreeLogger("/tmp/TeeTreeLauncher.log");

if(isset($argv) && (count($argv) === 4))
{
    $TeeTreeLogger->log("TeeTree launcher started", SERVICE_LAUNCHER_START, 'TeeTree launcher');
    $TeeTreeLogger->log("TeeTree Class Path  ". $argv[2], SERVICE_LAUNCHER_START, 'TeeTree launcher');
    $TeeTreeLogger->log("TeeTree Server Port ". $argv[1], SERVICE_LAUNCHER_START, 'TeeTree launcher');
    if($argv[3] === 'start')
    {
        $controller = new TeeTreeController($argv[2], $argv[1]);
    }
    elseif($argv[3] === 'stop')
    {
        TeeTreeController::stopServer('localhost', $argv[1]);
    }
}
else
{
    echo <<<USAGE
Usage: php TeeTreeLauncher.php <ServicePort> <ClassPath> <stop|start>


USAGE;

}

?>