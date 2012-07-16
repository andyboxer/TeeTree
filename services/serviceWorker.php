<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class serviceWorker
{
    // Gap between min and max should be at least twice the maximum no. of concurrent service instances required
    // Local system resources will limit the maximum number of service instances allowed

    const THREAD_PORT_MIN = 10000;
    const THREAD_PORT_MAX = 50000;

    protected $logFile = "/tmp/serviceTest.txt";
    protected $serviceServer = null;
    protected $clientConnection = null;
    protected $constructMessage = null;
    private $glom = "0.0.0.0";
    private $classPath = "";
    private $serviceObject = null;
    private $inPipe = null;
    private $outPipe = null;

    public function __construct($glom = "0.0.0.0")
    {
        $this->classPath = getenv("SERVICE_CLASS_PATH");
        $this->glom = $glom;
        $this->inPipe = fopen('php://stdin','r');
        $this->outPipe = fopen('php://stdout','w');
        $this->createInstance();
        $this->callHandler();
    }

    public function __destruct()
    {
        try{ if($this->inPipe && is_resource($this->inPipe)) fclose($this->inPipe);} catch(Exception $ex) { /* just fold  */ }
        try{ if($this->outPipe && is_resource($this->outPipe)) fclose($this->outPipe);} catch(Exception $ex) { /* just fold */ }
        try{ if($this->serviceServer && is_resource($this->serviceServer)) fclose($this->serviceServer);} catch(Exception $ex) { /* just fold */ }
        try{ if($this->clientConnection && is_resource($this->clientConnection)) fclose($this->clientConnection);} catch(Exception $ex) { /* just fold */ }
    }

    private function readConstructor()
    {
        if(is_resource($this->inPipe))
        {
            $requestString = fread($this->inPipe, 1024);
            fclose($this->inPipe);
            $this->constructMessage = serviceMessage::decode($requestString);
            if($this->constructMessage->isConstructor) return;
            throw new Exception("Request on contructor channel is not a constructor. ". $this->constructMessage->getEcoded());
        }
        throw new Exception("Request channel not open. ". $this->constructMessage->getEcoded());
    }

    private function openServiceChannel()
    {
        $port = rand(self::THREAD_PORT_MIN, self::THREAD_PORT_MAX);
        try
        {
            while(!($this->serviceServer = stream_socket_server ('tcp://'. $this->glom. ':'.$port, $errno, $errstr)))
            {
                $port = rand(self::THREAD_PORT_MIN, self::THREAD_PORT_MAX);
            }
            stream_set_blocking($this->serviceServer, 0);
            $portMessage = new serviceMessage($this->constructMessage->serviceClass, 'construct', $port);
            if(!fwrite($this->outPipe, $portMessage->getEncoded()))
            {
                throw new Exception("Unable to send constructor response");
            }
        }
        catch (Exception $ex)
        {
            $message = new serviceMessage($this->constructMessage->serviceClass, 'construct', $ex->getMessage(), true );
            if(!fwrite($this->outPipe, $message->getEncoded()))
            {
                throw new Exception("Unable to send constructor response");
            }
        }
    }

    private function createInstance()
    {
        $die = false;
        $this->readConstructor();
        $className = $this->constructMessage->serviceClass;
        $filename = "{$this->classPath}/{$className}.php";

        if(file_exists($filename))
        {
            require_once($filename);
            if(class_exists($className))
            {
                $this->openServiceChannel();
                $this->serviceObject = new $className($this->constructMessage->serviceData);
            }
        }
        if(!$this->serviceObject)
        {
            $response = new serviceMessage($className, 'contructor', 'Class '. $className. " not found in file ". $filename, true);
            fwrite($this->outPipe, $response->getEncoded());
            fflush($this->outPipe);
            $die = true;
        }
        fclose($this->outPipe);
        if($die) die();
    }

    private function callHandler()
    {// to do add security check class name and use secObj
        if($this->clientConnection = stream_socket_accept($this->serviceServer))
        {
            while(!feof($this->clientConnection))
            {
                $buffer = '';
                $data = '';
                while ($buffer !== "\n" && !feof($this->clientConnection)) {
                    $buffer =  fgets($this->clientConnection, 2);
                    $data .= $buffer;
                }

                if(strlen($data) > 0)
                {
                    $request = serviceMessage::decode($data);
                    if(method_exists($this->serviceObject, $request->serviceMethod))
                    {
                        $method = $request->serviceMethod;
                        try
                        {
                            $returnVal = $this->serviceObject->$method($request->serviceData);
                            $response = new serviceMessage($request->serviceClass, $request->serviceMethod, $returnVal);
                        }
                        catch(Exception $ex)
                        {
                            $response = new serviceMessage($request->serviceClass, $request->serviceMethod, $ex->getMessage(), true);
                        }
                    }
                    else
                    {
                        $response = new serviceMessage($request->serviceClass, $request->serviceMethod, "Service method {$request->serviceClass}::{$request->serviceMethod} does not exist", true);
                        $request->serviceMethod = "";
                    }

                    if(preg_match("/^_(.*)$/", $request->serviceMethod) === 0)
                    {
                        if(!fwrite($this->clientConnection, $response->getEncoded(). "\n"))
                        {
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            throw new Exception("Service worker connection timed out");
        }
    }
}
?>
