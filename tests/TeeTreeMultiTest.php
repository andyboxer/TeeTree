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

$multi = new multi_command();

for($i = 0; $i < 50; $i++)
{
   $command = TeeTreeConfiguration::PATH_TO_PHP_EXE . " ". __DIR__. "/TeeTreeTest.php ". $i;
   $multi->add_command($command);
}

$multi->execute();

$md5s = array();
foreach($multi->get_commands() as $command)
{
    $md5 = "NO MD5?\n";
    preg_match("/MD5:(\w+)\n/", $command['response'], $matches);
    if(isset($matches[1]))
    {
        if(!isset($md5s[$matches[1]])) $md5s[$matches[1]] = 0;
        $md5s[$matches[1]]++;
        print($matches[1]. "\n");
    }
    print('.');
}
print("\n". print_r($md5s, true). "\n");
?>

