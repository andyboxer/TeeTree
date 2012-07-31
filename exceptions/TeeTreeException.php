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
    const TEETREE_EXCEPTION_MESSAGE_READ = '130003';
    const TEETREE_EXCEPTION_MESSAGE_WRITE = '130004';
    const TEETREE_EXCEPTION_BAD_PORT_NO = '130005';
    const TEETREE_EXCEPTION_SERVER_CONNECTION_FAILED = '130006';
    const TEETREE_EXCEPTION_SERVICE_CLIENT_CONNECTION_FAILED = '130007';
    const TEETREE_EXCEPTION_CLIENT_NOT_CONNECTED = '130008';
    const TEETREE_EXCEPTION_UNABLE_TO_MAKE_TEE = '130009';
    const TEETREE_EXCEPTION_CONTROLLER_START_FAILED = '130010';
    const TEETREE_EXCEPTION_SERVICE_CONSTRUCTOR_FAILED = '130011';
  /*  const TEETREE_EXCEPTION = 'TT00';
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
*/
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
