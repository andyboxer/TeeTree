<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeLogger
{
    const SERVICE_CONTROLLER_START = 'TTSVR02';
    const SERVICE_CONTROLLER_STOP = 'TTSVR03';
    const SERVICE_CONTROLLER_PING = 'TTSRV04';
    const SERVICE_CONTROLLER_PONG = 'TTSVR05';

    private $logFile =  TeeTreeConfiguration::DEFAULT_SERVER_LOG;

    public function __construct($filename = null)
    {
        if($filename !== null) $this->logFile = $filename;
    }

    public function log($message, $code = '0', $source = 'unknown', $filename = null)
    {
        $msg = new TeeTreeLogMessage($message, $code, $source);
        if($filename === null) $filename = $this->logFile;
        file_put_contents($filename, $msg, FILE_APPEND);
    }

    public function logException(Exception $e, $source = null)
    {
        if($source === null) $source = $e->getFile();
        $msg = "{$e->getMessage()} \nCODE: {$e->getCode()} \nFILE {$e->getFile()} \nLINE: {$e->getLine()} \nTRACE: {$e->getTraceAsString()} \n";
        $this->log($msg, $e->getCode(), $source, TeeTreeConfiguration::DEFAULT_ERROR_LOG);
    }
}