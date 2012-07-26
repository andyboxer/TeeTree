<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class testServiceHello
{
    private $rawArguments = null;

    public function __construct($args = null)
    {
        $this->rawArguments = $args;
    }

    public function sayHello($data)
    {
        $data = (object) array("test1" => "this is a test for an object return value");
        return $data;
    }

    public function brokenCall()
    {
        throw new Exception("This service call is broken");
    }
}