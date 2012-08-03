<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once __DIR__. "/../testServices/TeeTreeConfiguration.php";
require_once __DIR__ . "/../bootstrap/TeeTreeBootStrap.php";

$idx = (isset($argv[1])) ? $argv[1] : 0;

try
{
    class ExampleServiceClass extends TeeTreeClient{protected $serviceHost = 'localhost'; protected $serviceControllerPort = TeeTreeConfiguration::TEETREE_SERVER_PORT;}

    $exampleObject = new ExampleServiceClass();
    $time = microtime(true);
    print('RESULTS:'."\n");
    for($i = 0; $i < 50; $i++) echo "{$exampleObject->shuffle("this is a nice day for a walk in the park")} No. {$i}\n";
    print("Time taken in seconds = ". (microtime(true) - $time). "\n");
}
catch(Exception $ex)
{
    print('ERROR:'. $ex->getMessage());
}

?>

