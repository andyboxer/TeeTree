<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeClient extends TeeTreeServiceEndpoint
{
    const CONNECT_TIMEOUT = 60;
    const READWRITE_TIMEOUT = 600;

    protected $serviceHost;
    protected $serviceControllerPort;
    protected $servicePort = null;
    protected $serviceConnection = null;
    protected $data = null;

    public function __construct()
    {
        $this->parseArgs(func_get_args());
        $this->connectServiceController();
        $this->connectService();
    }

    private function parseArgs($args)
    {
        $del = array();
        foreach($args as $value)
        {
            if(is_array($value) || is_object($value)) continue;
            if(preg_match("/^host:(.*)/", $value, $matches))
            {
                $del[] = $this->serviceHost = $matches[1];
            }
            if(preg_match("/^port:(.*)/", $value, $matches))
            {
                $del[] = $this->serviceControllerPort = $matches[1];
            }
        }
        $this->data = array_diff($args, $del);
    }

    public function __get($name)
    {
        if(isset($this->data[$name])) return $this->data[$name];
        return null;
    }

    public function __destruct()
    {
        $this->finishTee();
    }

    public function finishTee()
    {
        $request = new TeeTreeServiceMessage(get_called_class(), null, null, TeeTreeServiceMessage::TEETREE_FINAL);
        if($this->serviceConnection) $this->writeMessage($this->serviceConnection, $request);
    }

    public function __call($name, $args)
    {
        $request = new TeeTreeServiceMessage(get_called_class(), $name, $args);
        if($request->serviceMessageType === TeeTreeServiceMessage::TEETREE_CALL_NORETURN || $request->serviceMessageType === TeeTreeServiceMessage::TEETREE_CALL_NOWAIT)
        {
            $this->writeMessage($this->serviceConnection, $request);
            return;
        }
        return $this->converse($this->serviceConnection, $request)->serviceData;
    }

    public function getLastResponse()
    {
        return $this->readMessage($this->serviceConnection);
    }

    private function buildControllerConnectString()
    {
        if(strlen($this->serviceControllerPort) > 0)
        {
            return "tcp://". $this->serviceHost. ":". $this->serviceControllerPort;
        }
        else
        {
            throw new Exception("Service controller port not yet set");
        }
    }

    private function buildServiceConnectString()
    {
        if(strlen($this->servicePort) > 0)
        {
            return "tcp://". $this->serviceHost. ":". $this->servicePort;
        }
        else
        {
            throw new Exception("Service dialog port not yet set");
        }
    }

    private function connectServiceController()
    {
        if (!($serviceServer = stream_socket_client($this->buildControllerConnectString(), $errno, $errstr, self::CONNECT_TIMEOUT, STREAM_CLIENT_CONNECT)))
        {
            throw new Exception("Unable to connect to service controller at ". $this->buildControllerConnectString());
        }
        else
        {
            $request = new TeeTreeServiceMessage(get_called_class(), null, $this->data, TeeTreeServiceMessage::TEETREE_CONSTRUCTOR);
            $response = $this->converse($serviceServer, $request);
            stream_socket_shutdown($serviceServer, STREAM_SHUT_WR);
            if($response->serviceMessageType === TeeTreeServiceMessage::TEETREE_PORT_MESSAGE)
            {
                $this->servicePort = $response->serviceData;
            }
            else
            {
                throw new Exception("Service port message not recieved as response from constructor");
            }
        }
    }

    private function connectService()
    {
        if(!($serviceConnection = stream_socket_client($this->buildServiceConnectString(), $errno, $errstr, self::CONNECT_TIMEOUT)))
        {
            throw new Exception("Unable to connect to service at ". $this->buildServiceConnectString());
        }
        else
        {
            stream_set_timeout($serviceConnection, self::READWRITE_TIMEOUT);
            $this->serviceConnection = $serviceConnection;
        }
    }

    private function converse($serviceConnection, $request)
    {
        if(!$serviceConnection || !is_resource($serviceConnection))
        {
            throw new Exception("No service connection found to converse with");
        }
        $this->writeMessage($serviceConnection, $request);
        return $this->readMessage($serviceConnection);
    }
}

?>