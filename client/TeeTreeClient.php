<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once('TeeTreeServiceMessage.php');

class TeeTreeClient
{
    const CONNECT_TIMEOUT = 60;

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
        if($this->serviceConnection) $this->say($this->serviceConnection, $request);
    }

    public function __call($name, $args)
    {
        $request = new TeeTreeServiceMessage(get_called_class(), $name, $args);
        if($request->serviceMessageType === TeeTreeServiceMessage::TEETREE_NOWAIT_NORETURN || $request->serviceMessageType === TeeTreeServiceMessage::TEETREE_NOWAIT)
        {
            $this->say($this->serviceConnection, $request);
            return;
        }
        return $this->converse($this->serviceConnection, $request)->serviceData;
    }

    public function getLastResponse()
    {
        return $this->listen($this->serviceConnection);
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
            fclose($serviceServer);
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

    private function converse($serviceConnection, $request)
    {
        if(!$serviceConnection || !is_resource($serviceConnection))
        {
            throw new Exception("No service connection found to converse with");
        }
        $this->say($serviceConnection, $request);
        return $this->listen($serviceConnection);
    }

    private function listen($serviceConnection, $request = null)
    {
        $response = '';
        do
        {
            try
            {
                $buffer =  fgets($serviceConnection, 2);
            }
            catch(Exception $ex)
            {
                $methodName = ($request !== null) ? $request->serviceMethod : 'unknown';
                return new TeeTreeServiceMessage(get_called_class(), $methodName , $ex->getMessage(), TeeTreeServiceMessage::TEETREE_ERROR);
            }
            if(strlen($response) === 0 && $buffer === "\n") $buffer = '';
            $response .= $buffer;
        } while ($buffer !== "\n" && !feof($serviceConnection));
        return TeeTreeServiceMessage::decode($response);
    }

    private function say($serviceConnection, $request)
    {
        try
        {
            if($serviceConnection) fwrite($serviceConnection, $request->getEncoded());
        }
        catch(Exception $ex)
        {
            throw new Exception("Unable to send message to service at ". $this->buildServiceConnectString());
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
            $this->serviceConnection = $serviceConnection;
        }
    }
}

?>