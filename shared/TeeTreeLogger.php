<?php
require_once __DIR__. "/TeeTreeMessageCodes.php";
require_once __DIR__. "/TeeTreeLogMessage.php";

class TeeTreeLogger
{
    private $logFile = '/tmp/TeeTreeLogger.log';

    public function __construct($logFile)
    {
        $this->logFile = $logFile;
    }

    public function log($message, $code = '0', $source = 'unknown', $filename = null)
    {
        $msg = new TeeTreeLogMessage($message, $code, $source);
        $this->logMsg($msg, $filename);
    }

    public function logMsg(TeeTreeLogMessage $msg, $filename = null)
    {
        if($filename === null) $filename = $this->logFile;
        if((strlen($filename) > 0))
        {
            file_put_contents($filename, $msg, FILE_APPEND);
        }
    }

    public function logException(Exception $e, $location = 'unknown')
    {
        $msg = "ERROR : {$e->getMessage()} \nCODE: {$e->getCode()} \nFILE {$e->getFile()} \nLINE: {$e->getLine()} \nTRACE: {$e->getTraceAsString()} \n";
        $this->log($msg, $e->getCode(), $location);
    }
}