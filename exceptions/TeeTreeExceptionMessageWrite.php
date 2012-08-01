<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeExceptionMessageWrite extends TeeTreeException
{
    public function __construct($message)
    {
        parent::__construct($message, parent::TEETREE_EXCEPTION_MESSAGE_WRITE);
    }
}
?>