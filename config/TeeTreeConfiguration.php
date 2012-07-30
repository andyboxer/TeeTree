<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeConfiguration
{
    const DEFAULT_SERVICE_PATH = "./../testServices";
    const DEFAULT_SERVICE_PORT = 11311;
    const PATH_TO_PHP_EXE = "/usr/local/zend/bin/php";
    const DEFAULT_ERROR_LOG = "/var/log/TeeTree/error.log";
    const DEFAULT_SERVER_LOG = "/var/log/TeeTree/TeeTree.log";
    const DEFAULT_TEST_LOG = "/var/log/TeeTree/TestService.log";
    const MINIMUM_SERVICE_PORT = 12000;
    const ACCEPT_TIMEOUT = 10;
    const READWRITE_TIMEOUT = 600;

}
?>
