<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeServiceLongRunning
{
     private $constructParams = null;

    public function __construct($args = null)
    {
        $this->constructParams = $args;
    }

    public function doLongRunning($data)
    {
        for($i = 0; $i < 10; $i++)
        {
            usleep(200000);
            $filename = split(":", $data[0]);
            $filename = preg_replace("/ /", "", $filename[0]);
            file_put_contents("/var/log/TeeTree/test_". $filename. ".log", $i. ":". serialize($this->constructParams). ":". serialize($data). "\n", FILE_APPEND);
        }
        return "This message will be recived only if the calling client stops to wait for a return value (i.e NORETURN is not set)";
    }
}