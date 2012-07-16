<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

if(!defined('CACHED_OBJECT_NO_CACHE')) define('CACHED_OBJECT_NO_CACHE', false);
if(!defined('BASE_PATH')) define('BASE_PATH', realpath(dirname(__FILE__).  '/../'). '/');
if(!defined('DEFAULT_DATASET')) define('DEFAULT_DATASET', 'unittest');
require_once __DIR__. '/../config/base.php';

$multi = new multi_command();

for($i = 0; $i < 500; $i++)
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

