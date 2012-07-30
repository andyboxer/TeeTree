<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeException extends Exception
{
    // fetch server and client details as available and add to error message
    const TEETREE_EXCEPTION = '13000';
    const TEETREE_EXCEPTION_MESSAGE_DECODE_FAILED = '130001';
    const TEETREE_EXCEPTION_MESSAGE_RETURNED = '130002';
   /* const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';
    const TEETREE_EXCEPTION = 'TT00';*/

    public function __construct($message, $code, Exception $previous = null)
    {
        //TODO: add server and connection details here
        parent::__construct($message, $code);
    }

    private function writeToExceptionLog()
    {
        //TODO: write to exception log
    }
}
?>
