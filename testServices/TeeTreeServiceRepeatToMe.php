<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeServiceRepeatToMe
{
    private $constructParams = null;

    public function __construct($args)
    {
        $this->constructParams = $args;
    }

    public function getContructorParams()
    {
        return $this->constructParams;
    }

    public function repeatToMe($data)
    {
        $retval = str_shuffle($data[0]);
        return "bakatcha:". $retval;
    }
}