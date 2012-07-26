<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once 'TeeTreeListener.php';
require_once 'TeeTreeLogger.php';
require_once 'TeeTreeServiceMessage.php';
require_once 'TeeTreeUtils.php';

class TeeTreeController
{
    // min should be greater than the service controller port.
    // Local system resources will limit the maximum number of service instances allowed

    const THREAD_PORT_MIN = 11000;

    private $listener;
    private $processes = array();
    private $workerPorts = array();
    protected $classPath;
    protected $servicePort;
    public $TeeTreeLogger;
    private static $TeeTreeController = null;
    private static $logfile = "/tmp/TeeTreeController.log";

    public function __construct($classPath, $port)
    {
        if(!isset($GLOBALS['connections'])) $GLOBALS['connections'] = array();
        if(!isset($GLOBALS['buffers'])) $GLOBALS['buffers'] = array();
        $this->classPath = $classPath;
        $this->servicePort = $port;
        $this->TeeTreeLogger = new TeeTreeLogger(self::$logfile);
        $this->TeeTreeLogger->log('Service listener started on port '. $port, SERVICE_LISTENER_START, 'service controller');
        $this->listener = new TeeTreeListener($this, $port);
    }


    public function spawnWorker($id, $message)
    {
        $command = "/usr/local/zend/bin/php " . __DIR__ . "/TeeTreeServiceSpawn.php";
        $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", "/tmp/serviceInstance-error.log", "a")
        );

        $this->processes[$id] = $process = proc_open($command, $descriptorspec, $pipes, null, array("SERVICE_CLASS_PATH" => $this->classPath, "SERVICE_PORT" => $this->servicePort));

        if (is_resource($process)) {
            fwrite($pipes[0], $id . "|". (self::THREAD_PORT_MIN + $id) .  "|". $message);
            fclose($pipes[0]);
            $data = '';
            do
            {
                if(($buffer =  fgets($pipes[1], 2)) !== false)
                {
                    $data .= $buffer;
                    fputs($GLOBALS['connections'][$id], $buffer);
                    fflush($GLOBALS['connections'][$id]);
                }
                else
                {
                    break;
                }
            }while ($buffer != "\n");
            fclose($pipes[1]);
        }
        // if failed return error message to client
    }

    public static function startServer($classPath = __DIR__, $port = 2000)
    {
        $command = "/usr/local/zend/bin/php ". __DIR__. "/TeeTreeLauncher.php";
        $descriptorspec = array(
        0 => array("pipe", 'r'),
        1 => array("file",  "/tmp/TeeTreeLauncher.log", "a"),
        2 => array("file", "/tmp/serviceInstance-error.log", "a")
        );

        self::$TeeTreeController = $process = proc_open($command, $descriptorspec, $pipes, null, array("SERVICE_CLASS_PATH" => $classPath, "SERVICE_PORT" => $port));
        $TeeTreeLogger = new TeeTreeLogger(self::$logfile);
        $TeeTreeLogger->log('Service controller started on port '. $port, SERVICE_CONTROLLER_START, 'service controller');
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
            $TeeTreeLogger = new TeeTreeLogger(self::$logfile);
            $TeeTreeLogger->log('Service controller stopped on port '. $port, SERVICE_CONTROLLER_STOP, 'service controller');
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
            fwrite($serviceServer, "ping\n");
            fflush($serviceServer);
            $data = '';
            do
            {
                $buffer =  fgets($serviceServer, 2);
                $data .= $buffer;
            }while ($buffer != "\n");
            if(preg_match("/^pong\n/", $data)) return true;
        }
        return false;
    }
}
?>