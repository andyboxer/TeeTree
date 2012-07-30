<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeServiceHello
{
    private $constructParams = null;

    public function __construct($args = null)
    {
        $this->constructParams = $args;
    }

    public function getConstructorParams()
    {
        return $this->constructParams;
    }

    public function sayHello($data)
    {
        $data = (object) array("hello" => "world", "TestingTesting" => "this is a test for an object return value\nwith a line break inside");
        return $data;
    }

    public function brokenCall()
    {
        throw new Exception("This service call is broken");
    }
}