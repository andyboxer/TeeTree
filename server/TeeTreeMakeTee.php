<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
if($classPath = getenv("TEETREE_CLASS_PATH"))
{
    require_once($classPath. "/TeeTreeConfiguration.php");
    require_once(__DIR__ . "/../bootstrap/TeeTreeBootStrap.php");
    $tee = new TeeTreeTee();
    die();
}

die("TeeTree configuration path was not set for making tee!");
?>