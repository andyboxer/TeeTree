<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class testServiceRepeater
{
    private $args = null;
    private $logfile = "/tmp/TeeTreeRepeater.log";

    public function __construct($args)
    {
        $this->args = $args;
    }

    public function getStuff($data)
    {
        $retval = str_shuffle($data[0]);
        return "bakatcha:". $retval;
    }

    public function _dontWait($data)
    {
        for($i = 0; $i < 10; $i++)
        {
            sleep(1);
            file_put_contents($this->logfile, $i. "\n", FILE_APPEND);
        }
        return "nothing reaches client";
    }

    public function doLongRunning($data)
    {
        for($i = 0; $i < 10; $i++)
        {
            sleep(1);
            file_put_contents($this->logfile, (isset($this->args[0])?$this->args[0]:''). " - ". $i. ":". $data. "\n", FILE_APPEND);
        }
        return (isset($this->args[0])?$this->args[0]:''). " this will get picked up later by the client ". $data;
    }
}