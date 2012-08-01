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
            throw new TeeTreeExceptionBadPortNo("Service controller port not yet set");
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
            throw new TeeTreeExceptionBadPortNo("Service dialog port not yet set");
        }
    }

    private function connectServiceController()
    {
        $tries = 0;
        do
        {
            usleep(100 * $tries);
            $serviceClient = @stream_socket_client($this->buildControllerConnectString(), $errno, $errstr, TeeTreeConfiguration::CLIENT_CONNECT_TIMEOUT, STREAM_CLIENT_CONNECT);
        } while(!$serviceClient && ($tries++ < TeeTreeConfiguration::CONTRUCTOR_MAX_RETRY));

        if($serviceClient)
        {
            $this->servicePort = null;
            $tries = 0;
            $request = new TeeTreeServiceMessage(get_called_class(), null, $this->data, TeeTreeServiceMessage::TEETREE_CONSTRUCTOR);
            do
            {
                $response = $this->converse($serviceClient, $request);

                if($response && $response->serviceMessageType === TeeTreeServiceMessage::TEETREE_PORT_MESSAGE)
                {
                    $this->servicePort = $response->serviceData;
                    break;
                }

            }while($tries++ < TeeTreeConfiguration::CONTRUCTOR_MAX_RETRY);
            if($this->servicePort === null)
            {
                throw new TeeTreeExceptionBadPortNo("Service port message not recieved as response from constructor");
            }
            stream_socket_shutdown($serviceClient, STREAM_SHUT_WR);
        }
        else
        {
            throw new TeeTreeExceptionServerConnectionFailed("Unable to connect to service controller at ". $this->buildControllerConnectString(). ". $errstr");
        }
    }

    private function connectService()
    {
        if(!($serviceConnection = @stream_socket_client($this->buildServiceConnectString(), $errno, $errstr, TeeTreeConfiguration::CLIENT_CONNECT_TIMEOUT)))
        {
            throw new TeeTreeExceptionServiceClientConnectionFailed("Unable to connect to service at ". $this->buildServiceConnectString(). ". $errstr");
        }
        else
        {
            stream_set_timeout($serviceConnection, TeeTreeConfiguration::READWRITE_TIMEOUT);
            $this->serviceConnection = $serviceConnection;
        }
    }

    private function converse($serviceConnection, $request)
    {
        if(!$serviceConnection || !is_resource($serviceConnection))
        {
            throw new TeeTreeExceptionClientNotConnected("No service connection found to converse with for request :". $request->getEncoded());
        }
        $this->writeMessage($serviceConnection, $request);
        return $this->readMessage($serviceConnection);
    }
}

?>