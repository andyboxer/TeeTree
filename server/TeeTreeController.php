<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

/**
 *
 * This class provides the TeeTree Remote Service Invocation controller
 * This class is used to controll and monitor the TeeTree service listener
 * it acts as a bridge between client and server during object instantiation.
 *
 */
class TeeTreeController
{
    // A static singleton instance of the TeeTree controller
    private static $TeeTreeController = null;
    // The current connection listener
    private $listener;
    // An array of all current TeeTree process objects
    private $processes = array();
    // The configured classpath for this instance of the controller ( Note: this is set to be a single directory and MUST be readonly for security reasons )
    protected $classPath;
    // The configure TeeTree controller p[ort for this instance of the controller
    protected $servicePort;
    // A logger instance, this defaults to writing to the file configured at TeeTreeConfiguration::TEETREE_SERVER_LOG
    public $TeeTreeLogger;


    /**
     *
     * Create an instance of the TeeTree Controller
     * This constructor call will instantiate, configure and start the TeeTree listener and is a BLOCKING call
     * Controller logging is written by default to the file configured at TeeTreeConfiguration::TEETREE_SERVER_LOG
     *
     * @param string $classPath - The absolute path to a readonly directory containing the TeeTree service classes.
     * @param integer $port - The port number with which to instantiate the controller tcp listener
     */
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

    /**
     *
     * This method will request shutdown for all processes in the processes array
     * The shutdown method is called on each of the process objects in order to terminate it.
     *
     */
    private function closeFinishedProcesses()
    {
        foreach($this->processes as $key => $process)
        {
            if($process->shutdown()) unset($this->processes[$key]);
        }
    }

    /**
     *
     * This method will request the termination of the client service object instances
     * associated with each of the current processes.
     * The terminateClient method of each process object is used for this.
     */
    public function terminateProcesses()
    {
        foreach($this->processes as $key => $process)
        {
            if($process->isRunning())
            {
                $process->terminateClient();
                unset($this->processes[$key]);
            }
        }
    }

    /**
     *
     * This method makes the initial connection between a TeeTree service client and a remote service instance
     * OR if a remote instance of the same class type in the process pool is idle then that will be used instead.
     *
     * @param integer $id - This is the event instance id and is used to track each invocation call.
     * @param string $message - The json encoded message from the TeeTree client instance,
     *
     * Note: ALL messages are json encoded instances of the TeeTreeServiceMessage class with a couple of exceptions ( see below for details ).
     */
    public function makeTee($id, $message)
    {
        // decode the request message into an object of stdClass
        $request = TeeTreeServiceMessage::decode($message);

        // check to see if there is an existing process with the same class type available in the process pool
        if($process = $this->findWaitingProcess($request->serviceClass))
        {
            // There is a matching process, send back a service port message containing it's service port number to the waiting client instance.
            $response = new TeeTreeServiceMessage($request->serviceClass , $request->serviceMethod, $process->servicePort, TeeTreeServiceMessage::TEETREE_PORT_MESSAGE);
            @fputs($GLOBALS['connections'][$id], $response->getEncoded());
            fflush($GLOBALS['connections'][$id]);
        }
        // no existing instance, no worries well knock one up now
        else
        {
            $tee = $this->connectTee($id, $message, $pipes);
            if($tee->isRunning())
            {
                // send our constructor message from the client to the MakeTee script and pipe back it's service port message to the waiting client instance.
                fwrite($pipes[0], $id . "|". (TeeTreeConfiguration::MINIMUM_SERVICE_PORT + $id) .  "|". $message);
                fclose($pipes[0]);
                $data = '';
                do
                {
                    if(($buffer =  fgets($pipes[1], 2)) === false) break;
                    $data .= $buffer;
                    @fputs($GLOBALS['connections'][$id], $buffer);
                    fflush($GLOBALS['connections'][$id]);
                } while ($buffer != "\n");
                fclose($pipes[1]);

            }
            else
            {
                throw new TeeTreeExceptionUnableToMakeTee("Unable to create tee for message :". $message);
            }
        }
        $this->closeFinishedProcesses();
    }

    /**
     *
     * This method creates a new instance of the TeeTreeMakeTee script in a new process
     * The process handle and the port number for this instance are wrapped in a TeeTreeProcess object and stored in the processes array
     *
     * @param integer $id - The event instance id
     * @param string $message - The json encoded constructor request message from the TeeTree client object
     * @param array $pipes - A reference to an array which will be populated by this call with the pipes opened on the remote service object instance container process.
     */
    private function connectTee($id, $message, &$pipes = array())
    {
        $command = TeeTreeConfiguration::PATH_TO_PHP_EXE. " " . __DIR__ . "/TeeTreeMakeTee.php";
        $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", TeeTreeConfiguration::TEETREE_ERROR_LOG, "a"));
        $processHandle = @proc_open($command, $descriptorspec, $pipes, null, array("TEETREE_CLASS_PATH" => $this->classPath, "TEETREE_CONTROLLER_PORT" => $this->servicePort));
        $request = TeeTreeServiceMessage::decode($message);
        $process = new TeeTreeProcess($id, $processHandle, $request->serviceClass, TeeTreeConfiguration::MINIMUM_SERVICE_PORT + $id);
        return $this->processes[$id] = $process;
    }

    /**
     *
     * Find an idle process instance of the correct class type
     *
     * @param string $class - the class type for which to search.
     */
    private function findWaitingProcess($class)
    {
        usort($this->processes, function($a, $b){return ($a->id === $b->id) ? 0 : (($a->id < $b->id) ? 1 : -1);} );
        foreach($this->processes as $process)
        {
            if(($process->serviceClass == $class) && $process->isUsable() ) return $process;
        }
        return null;
    }

    /**
     *
     * Static method to enable scripted start of the TeeTree controller
     * @param string $classPath - The path to the service class directory
     * @param integer $port - The port on which the controller should listen
     */
    public static function startServer($classPath = __DIR__ , $port = 2000)
    {
        $TeeTreeLogger = new TeeTreeLogger();
        $TeeTreeLogger->log('Service controller starting on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_START, 'service controller');

        $command = TeeTreeConfiguration::PATH_TO_PHP_EXE. " " . __DIR__ . "/TeeTreeLauncher.php ". $port. " \"{$classPath}\" start &";
        $descriptorspec = array(
        0 => array("pipe", 'r'),
        1 => array("file",  TeeTreeConfiguration::TEETREE_SERVER_LOG, "a"),
        2 => array("file", TeeTreeConfiguration::TEETREE_ERROR_LOG, "a"));
        self::$TeeTreeController = $process = @proc_open($command, $descriptorspec, $pipes, null, array("TEETREE_CLASS_PATH" => $classPath, "TEETREE_CONTROLLER_PORT" => $port));
    }

    /**
     *
     * Static method to enable scripted stop of the TeeTree controller
     * @param string $host - The host name / ip address of the TeeTree controller host
     * @param string $port - The port on which the TeeTree controller is listening
     *
     * @throws TeeTreeExceptionServerConnectionFailed
     */
    public static function stopServer($host, $port)
    {
        $serviceServer = self::openControllerConnection($host, $port);
        @fwrite($serviceServer, "exit\n");
        fflush($serviceServer);
        fclose($serviceServer);
        $TeeTreeLogger = new TeeTreeLogger();
        $TeeTreeLogger->log('Service controller stopped on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_STOP, 'service controller');
    }

    /**
     *
     * Static method to enable scripted heartbeat testing of a running TeeTree controller instance
     * @param string $host - The host name / IP adddress of the TeeTree controller host
     * @param integer $port - The port on which the TeeTree controller is listening
     *
     * @throws TeeTreeExceptionServerConnectionFailed
     */
    public static function pingServer($host, $port)
    {
        $serviceServer = self::openControllerConnection($host, $port);
        $TeeTreeLogger = new TeeTreeLogger();
        //$TeeTreeLogger->log('Service controller ping on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_PING, 'service controller');
        @fwrite($serviceServer, "ping\n");
        fflush($serviceServer);
        $data = '';
        do
        {
            $buffer =  @fgets($serviceServer, 2);
            $data .= $buffer;
        }while ($buffer != "\n");
        if(preg_match("/^pong\n/", $data))
        {
            //$TeeTreeLogger->log('Service controller pong on port '. $port, TeeTreeLogger::SERVICE_CONTROLLER_PONG, 'service controller');
            return true;
        }
        return false;
    }

    private static function openControllerConnection($host, $port)
    {
        $retry = 0;
        do
        {
            $serviceServer = @stream_socket_client('tcp://'. $host. ':'. $port, $errno, $errstr, TeeTreeConfiguration::CLIENT_CONNECT_TIMEOUT, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);

        } while( !$serviceServer  && ($retry++ < TeeTreeConfiguration::CONSTRUCTOR_MAX_RETRY));
        if(!$serviceServer) throw new TeeTreeExceptionServerConnectionFailed($errstr);
        return $serviceServer;
    }
}
?>