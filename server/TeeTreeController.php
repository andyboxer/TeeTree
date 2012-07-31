<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeController
{
    private static $TeeTreeController = null;
    private $listener;
    private $processes = array();
    private $workerPorts = array();
    protected $classPath;
    protected $servicePort;
    public $TeeTreeLogger;


    public function __construct($classPath, $port)
    {
        if(!isset($GLOBALS['connections'])) $GLOBALS['connections'] = array();
        if(!isset($GLOBALS['buffers'])) $GLOBALS['buffers'] = array();
        $this->classPath = $classPath;
        $this->servicePort = $port;
        $this->TeeTreeLogger = new TeeTreeLogger();
        $this->TeeTreeLogger->log('Service controller started on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_START, 'service controller');
        $this->listener = new TeeTreeListener($this, $port);
    }


    public function makeTee($id, $message)
    {
        if (is_resource($this->connectTee($id, $pipes)))
        {
            fwrite($pipes[0], $id . "|". (TeeTreeConfiguration::MINIMUM_SERVICE_PORT + $id) .  "|". $message);
            fclose($pipes[0]);
            $data = '';
            do
            {
                if(($buffer =  fgets($pipes[1], 2)) === false) break;
                $data .= $buffer;
                fputs($GLOBALS['connections'][$id], $buffer);
                fflush($GLOBALS['connections'][$id]);
            } while ($buffer != "\n");
            $this->workerPorts[] = trim($data, "\n");
            fclose($pipes[1]);
        }
        else
        {
            throw new TeeTreeExceptionUnableToMakeTee("Unable to create tee for message :". $message);
        }
    }

    private function connectTee($id, &$pipes = array())
    {
        $command = TeeTreeConfiguration::PATH_TO_PHP_EXE. " " . __DIR__ . "/TeeTreeMakeTee.php";
        $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", TeeTreeConfiguration::DEFAULT_ERROR_LOG, "a"));
        return $this->processes[$id] = proc_open($command, $descriptorspec, $pipes, null, array("TEETREE_CLASS_PATH" => $this->classPath, "TEETREE_CONTROLLER_PORT" => $this->servicePort));
    }

    public static function startServer($classPath = __DIR__ , $port = 2000)
    {
        $TeeTreeLogger = new TeeTreeLogger();
        $TeeTreeLogger->log('Service controller starting on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_START, 'service controller');

        $command = TeeTreeConfiguration::PATH_TO_PHP_EXE. " " . __DIR__ . "/TeeTreeLauncher.php";
        $descriptorspec = array(
        0 => array("pipe", 'r'),
        1 => array("file",  TeeTreeConfiguration::DEFAULT_SERVER_LOG, "a"),
        2 => array("file", TeeTreeConfiguration::DEFAULT_ERROR_LOG, "a"));
        self::$TeeTreeController = $process = proc_open($command, $descriptorspec, $pipes, null, array("TEETREE_CLASS_PATH" => $classPath, "TEETREE_CONTROLLER_PORT" => $port));
        $TeeTreeLogger->log('Service controller started on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_START, 'service controller');
    }

    public static function stopServer($host, $port)
    {
        if (!($serviceServer = stream_socket_client('tcp://'. $host. ':'. $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT)))
        {
            echo "error in server stop $errstr ($errno)<br />\n";
        }
        else
        {
            fwrite($serviceServer, "exit\n");
            fflush($serviceServer);
            fclose($serviceServer);
            $TeeTreeLogger = new TeeTreeLogger();
            $TeeTreeLogger->log('Service controller stopped on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_STOP, 'service controller');
        }
    }

    public static function pingServer($host, $port)
    {
        if (!($serviceServer = stream_socket_client('tcp://'. $host. ':'. $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT)))
        {
            echo "error in server ping $errstr ($errno)<br />\n";
        }
        else
        {
            $TeeTreeLogger = new TeeTreeLogger();
            $TeeTreeLogger->log('Service controller ping on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_PING, 'service controller');
            fwrite($serviceServer, "ping\n");
            fflush($serviceServer);
            $data = '';
            do
            {
                $buffer =  fgets($serviceServer, 2);
                $data .= $buffer;
            }while ($buffer != "\n");
            if(preg_match("/^pong\n/", $data))
            {
                $TeeTreeLogger->log('Service controller pong on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_PONG, 'service controller');
                return true;
            }
        }
        return false;
    }
}
?>