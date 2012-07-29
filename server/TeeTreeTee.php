<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once 'TeeTreeServiceMessage.php';

class TeeTreeTee
{
    const ACCEPT_TIMEOUT = 10;

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
    private $logger;

    public function __construct($glom = "0.0.0.0")
    {
        $this->logger = new TeeTreeLogger("/tmp/TeeTreecallHandler.log");
        $this->classPath = getenv("TEETREE_CLASS_PATH");
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
                if($this->constructMessage->serviceMessageType === TeeTreeServiceMessage::TEETREE_CONSTRUCTOR) return;
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
                $portMessage = new TeeTreeServiceMessage($this->constructMessage->serviceClass, null, $this->servicePort, TeeTreeServiceMessage::TEETREE_PORT_MESSAGE);
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
            $message = new TeeTreeServiceMessage($this->constructMessage->serviceClass, 'construct', $ex->getMessage(), TeeTreeServiceMessage::TEETREE_ERROR );
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
            $response = new TeeTreeServiceMessage($className, null, 'Class '. $className. " not found in file ". $filename, TeeTreeServiceMessage::TEETREE_ERROR);
            fwrite($this->outPipe, $response->getEncoded());
            fflush($this->outPipe);
            $die = true;
        }
        fclose($this->outPipe);
        if($die) die();
    }

    private function callHandler()
    {
        //TODO: add security check class name and use secObj
        do
        {
            $this->logger->log("TeeTree worker waiting");
            if($this->clientConnection = @stream_socket_accept($this->serviceServer, self::ACCEPT_TIMEOUT))
            {
                while(!feof($this->clientConnection))
                {
                    $this->logger->log("TeeTree worker reading");
                    $message = $this->readMessage();
                    if(strlen($message) > 0)
                    {
                        $this->logger->log("TeeTree worker message received : ". print_r($message, true));
                        $request = TeeTreeServiceMessage::decode($message);
                        $response = $this->executeRequest($request);
                        if($response->serviceMessageType !== TeeTreeServiceMessage::TEETREE_CALL_NORETURN
                            && $response->serviceMessageType !== TeeTreeServiceMessage::TEETREE_FINAL
                            && $response->serviceMessageType !== TeeTreeServiceMessage::TEETREE_TERMINATE)
                        {
                            $this->logger->log("TeeTree worker sending response : ". $response->getEncoded());
                            if(!fwrite($this->clientConnection, $response->getEncoded()))
                            {
                                throw new Exception("Unable send response for message :". $response->getEncoded());
                            }
                        }
                    }
                }
            }
            else
            {
                break;
            }
        }while($response->serviceMessageType !== TeeTreeServiceMessage::TEETREE_TERMINATE);
        $this->logger->log("TeeTree worker exiting");
    }
    private function readMessage()
    {
        $buffer = '';
        $message = '';
        while ($buffer !== "\n" && !feof($this->clientConnection)) {
            $buffer = fgets($this->clientConnection, 2);
            $message .= $buffer;
        }
        return $message;
    }

    private function executeRequest($request)
    {
        $method = $request->serviceMethod;
        if($request->serviceMessageType === TeeTreeServiceMessage::TEETREE_FINAL) return $request;
        if(method_exists($this->serviceObject, $method))
        {
            try
            {
                $returnVal = $this->serviceObject->$method($request->serviceData);
                return new TeeTreeServiceMessage($request->serviceClass, $method, $returnVal, $request->serviceMessageType);
            }
            catch(Exception $ex)
            {
                return new TeeTreeServiceMessage($request->serviceClass, $method, $ex->getMessage(), TeeTreeServiceMessage::TEETREE_ERROR);
            }
        }
        else
        {
            return new TeeTreeServiceMessage($request->serviceClass, $method, "Service method {$request->serviceClass}::{$method} does not exist", TeeTreeServiceMessage::TEETREE_ERROR);
        }
        return null;
    }
}
?>
