<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
set_include_path(get_include_path(). PATH_SEPARATOR. __DIR__ . PATH_SEPARATOR. realpath(__DIR__. "/../shared"));
require_once 'TeeTreeLogger.php';
require_once 'TeeTreeServiceWorker.php';

$worker = new TeeTreeServiceWorker();
die();
?>