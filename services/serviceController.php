<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once('serviceListener.php');
require_once('logger.php');
require_once('serviceMessage.php');

class serviceController
{
    private $listeners = array();
    private $processes = array();
    protected $classPath;
    protected $servicePort;
    public $logger;
    private static $serviceController = null;
    private static $logfile = "/tmp/serviceController.log";

    public function __construct($classPath, $port)
    {
        if(!isset($GLOBALS['connections'])) $GLOBALS['connections'] = array();
        if(!isset($GLOBALS['buffers'])) $GLOBALS['buffers'] = array();
        $this->classPath = $classPath;
        $this->servicePort = $port;
        $this->logger = new logger(self::$logfile);
        $this->logger->log('Service listener started on port '. $port, SERVICE_LISTENER_START, 'service controller');
        $this->listeners[] = new serviceListener($this, $port);
    }

    public function spawnWorker($id, $message)
    {
        $command = "/usr/local/zend/bin/php ". __DIR__. "/serviceSpawn.php";
        $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", "/tmp/serviceInstance-error.log", "a")
        );

        $this->processes[] = $process = proc_open($command, $descriptorspec, $pipes, null, array("SERVICE_CLASS_PATH" => $this->classPath, "SERVICE_PORT" => $this->servicePort));

        if (is_resource($process)) {
            fwrite($pipes[0], $message);
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
                    throw new Exception("Spawn worker failed");
                }
            }while ($buffer != "\n");

            $constructMessage = serviceMessage::decode($data);

            fclose($pipes[1]);
            $this->logger->log('Service worker spawned on service port '. $constructMessage->serviceData, SERVICE_WORKER_START, 'service controller');
        }
        // if failed return error message to client
    }

    public static function startServer($classPath = __DIR__, $port = 2000)
    {
        $command = "/usr/local/zend/bin/php ". __DIR__. "/serviceLauncher.php";
        $descriptorspec = array(
        0 => array("pipe", 'r'),
        1 => array("file",  "/tmp/serviceLauncher.log", "a"),
        2 => array("file", "/tmp/serviceInstance-error.log", "a")
        );

        self::$serviceController = $process = proc_open($command, $descriptorspec, $pipes, null, array("SERVICE_CLASS_PATH" => $classPath, "SERVICE_PORT" => $port));
        $logger = new logger(self::$logfile);
        $logger->log('Service controller started on port '. $port, SERVICE_CONTROLLER_START, 'service controller');
    }

    public static function stopServer($host, $port)
    {
        if (!($serviceServer = stream_socket_client('tcp://'. $host. ':'. $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT)))
        {
            echo "$errstr ($errno)<br />\n";
        }
        else
        {
            fwrite($serviceServer, "exit\n");
            fflush($serviceServer);
            fclose($serviceServer);
            $logger = new logger(self::$logfile);
            $logger->log('Service controller stopped on port '. $port, SERVICE_CONTROLLER_STOP, 'service controller');
        }
    }

    public static function pingServer($host, $port)
    {
        if (!($serviceServer = stream_socket_client('tcp://'. $host. ':'. $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT)))
        {
            echo "$errstr ($errno)<br />\n";
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