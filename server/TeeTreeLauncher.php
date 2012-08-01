<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

if(isset($argv) && (count($argv) === 4))
{
    require_once($argv[2]. "/TeeTreeConfiguration.php");
    require_once(__DIR__ . "/../config/TeeTreeBootStrap.php");

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