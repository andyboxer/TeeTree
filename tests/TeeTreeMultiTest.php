<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
require_once __DIR__. '/multi_command.php';

// set a port for the service connection
$testPort = 10700;

$multi = new multi_command();

for($i = 0; $i < 20; $i++)
{
   $command = "/usr/local/zend/bin/php ". __DIR__. "/runServiceTest.php";
   $multi->add_command($command);
}

$multi->execute();

foreach($multi->get_commands() as $command)
{
    print_r($command['response']);
}

?>
