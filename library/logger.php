<?php
require_once __DIR__. "/logMessageCodes.php";

class logger
{
    private $logFile = '/tmp/logger.log';

    public function __construct($logFile = null)
    {
        if($logFile !== null) $this->logFile = $logFile;
        elseif(defined('SERVICE_LOGFILE')) $this->logFile = SERVICE_LOGFILE;
    }

    public function log($message, $code = '0', $source = 'unknown', $filename = null)
    {
        $msg = new logMessage($message, $code, $source);
        $this->logMsg($msg, $filename);
    }

    public function logMsg(logMessage $msg, $filename = null)
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