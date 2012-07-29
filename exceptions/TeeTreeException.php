<?php
class TeeTreeException extends Exception
{
    // fetch server and client details as available and add to error message
    const TEETREE_EXCEPTION = 'TT0000';
    const TEETREE_EXCEPTION_MESSAGE_DECODE_FAILED = 'TT0001';
    const TEETREE_EXCEPTION_MESSAGE_RETURNED = 'TT0002';

    public function __construct($message, $code, $previous)
    {
        //TODO: add server and connection details here
        parent::__construct($message, $code, $previous);
    }

    private function writeToExceptionLog()
    {
        //TODO: write to exception log
    }
}