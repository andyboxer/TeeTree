<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
require_once __DIR__. "/../testServices/TeeTreeConfiguration.php";
require_once __DIR__ . "/../config/TeeTreeBootStrap.php";

$multi = new multi_command();

for($i = 0; $i < 20; $i++)
{
   $command = TeeTreeConfiguration::PATH_TO_PHP_EXE . " ". __DIR__. "/TeeTreeTest.php";
   $multi->add_command($command);
}

$multi->execute();

foreach($multi->get_commands() as $command)
{
    print_r($command['response']);
}

?>

