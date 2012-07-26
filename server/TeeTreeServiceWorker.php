<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once 'TeeTreeServiceMessage.php';

class TeeTreeServiceWorker
{
    const ACCEPT_TIMEOUT = 60;

    protected $logFile = "/tmp/serviceTest.txt";
    protected $serviceServer = null;
    protected $clientConnectionId = null;
    protected $clientConnection = null;
    protected $constructMessage = null;
    private $glom = "0.0.0.0";
    private $classPath = "";
    private $servicePort = null;
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
            if(preg_match("/^(\d+)\|(\d+)\|(.*)$/", $requestString, $matches))
            {
                $this->servicePort = intval($matches[2]);
                $this->clientConnectionId = intval($matches[1]);
                $this->constructMessage = TeeTreeServiceMessage::decode($matches[3]);
                if($this->constructMessage->isConstructor) return;
            }
            throw new Exception("Request on contructor channel is not a constructor. request '". $requestString. "'");
        }
        throw new Exception("Request channel not open. Request '". $requestString. "'");
    }

    private function openServiceChannel()
    {
        try
        {
            if($this->serviceServer = stream_socket_server ('tcp://'. $this->glom. ':'.$this->servicePort, $errno, $errstr))
            {
                stream_set_blocking($this->serviceServer, 0);
                $portMessage = new TeeTreeServiceMessage($this->constructMessage->serviceClass, 'construct', $this->servicePort);
                if(!fwrite($this->outPipe, $portMessage->getEncoded()))
                {
                    throw new Exception("Unable to send constructor response on port ". $this->servicePort);
                }
            }
            else
            {
                throw new Exception("failed to open service channel on port ". $this->servicePort);
            }
        }
        catch (Exception $ex)
        {
            $message = new TeeTreeServiceMessage($this->constructMessage->serviceClass, 'construct', $ex->getMessage(), true );
            if(!fwrite($this->outPipe, $message->getEncoded()))
            {
                throw new Exception("Error opening service channel '". $ex->getMessage(). "'");
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
            $response = new TeeTreeServiceMessage($className, 'contructor', 'Class '. $className. " not found in file ". $filename, true);
            fwrite($this->outPipe, $response->getEncoded());
            fflush($this->outPipe);
            $die = true;
        }
        fclose($this->outPipe);
        if($die) die();
    }

    private function callHandler()
    {// to do add security check class name and use secObj
        if($this->clientConnection = stream_socket_accept($this->serviceServer, self::ACCEPT_TIMEOUT))
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
                    $request = TeeTreeServiceMessage::decode($data);
                    if(method_exists($this->serviceObject, $request->serviceMethod))
                    {
                        $method = $request->serviceMethod;
                        try
                        {
                            $returnVal = $this->serviceObject->$method($request->serviceData);
                            $response = new TeeTreeServiceMessage($request->serviceClass, $request->serviceMethod, $returnVal);
                        }
                        catch(Exception $ex)
                        {
                            $response = new TeeTreeServiceMessage($request->serviceClass, $request->serviceMethod, $ex->getMessage(), true);
                        }
                    }
                    else
                    {
                        $response = new TeeTreeServiceMessage($request->serviceClass, $request->serviceMethod, "Service method {$request->serviceClass}::{$request->serviceMethod} does not exist", true);
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
