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

// set loops to continually hammer the server call this script with a numeric argument in order to set the number of iterations.
$loops = ((isset($argv[1])) ? $argv[1] : 20);
$iterations = ((isset($argv[2])) ? $argv[2] : 200);
// record test start time
$testStart = time();

for($j = 0; $j < $loops; $j++)
{
    $multi = new multi_command();
    $start = time();

    for($i = 0; $i < $iterations; $i++)
    {
        $command = TeeTreeConfiguration::PATH_TO_PHP_EXE . " ". __DIR__. "/CallLimitTest.php ". $i;
        $multi->add_command($command);
    }

    $multi->execute();
    $errors = array();
    $count = 0;
    foreach($multi->get_commands() as $command)
    {
        if(preg_match("/ERROR/", $command['response']))
        {
            $errors[] = $command['response'];
        }
        if(preg_match("/RESULTS/", $command['response'])) $count++;
        //print_r($command['response']);
    }
    //print_r($errors);
    print("Total thread = ". $count. " run time = ". (time() - $start). " seconds\n");
}

print("Total runtime = ". (time() - $testStart). " seconds\n");

?>

