<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class serviceClient
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
        if($this->serviceConnection) fclose($this->serviceConnection);
    }

    public function __call($name, $args)
    {
        $request = new serviceMessage(get_called_class(), $name, $args);
        if(preg_match("/^_(.*)$/", $name, $matches))
        {
            $this->say($this->serviceConnection, $request);
            return;
        }
        return $this->converse($this->serviceConnection, $request)->serviceData;
    }

    public function callNoWait($method, $args = null)
    {
        $request = new serviceMessage(get_called_class(), $method, $args);
        $this->say($this->serviceConnection, $request);
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
        if (!($serviceServer = stream_socket_client($this->buildControllerConnectString(), $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT)))
        {
            throw new Exception("Unable to connect to service controller at ". $this->buildControllerConnectString());
        }
        else
        {
            $request = new serviceMessage(get_called_class(), 'construct', $this->data);
            $response = $this->converse($serviceServer, $request);
            fclose($serviceServer);
            $this->servicePort = $response->getConstructPortNumber();
        }
    }

    private function converse($service, $request)
    {
        if(!$service || !is_resource($service))
        {
            throw new Exception("No service connection found to converse with");
        }
        $this->say($service, $request);
        return $this->listen($service);
    }

    private function listen($service, $request = null)
    {
        $response = '';
        do
        {
            try
            {
                $buffer =  fgets($service, 2);
            }
            catch(Exception $ex)
            {
                return new serviceMessage(get_called_class(),($request !== null) ? $request->serviceMethod : 'unknown' , $ex->getMessage(), true);
            }
            if(strlen($response) === 0 && $buffer === "\n") $buffer = '';
            $response .= $buffer;
        } while ($buffer !== "\n" && !feof($service));
        return serviceMessage::decode($response);
    }

    private function say($service, $request)
    {
        try
        {
            if($service) fwrite($service, $request->getEncoded());
        }
        catch(Exception $ex)
        {
            throw new Exception("Unable to send message to service at ". $this->buildServiceConnectString());
        }
    }

    private function connectService()
    {
        if(!($sfp = stream_socket_client($this->buildServiceConnectString(), $errno, $errstr, 30)))
        {
            throw new Exception("Unable to connect to service at ". $this->buildServiceConnectString());
        }
        else
        {
            $this->serviceConnection = $sfp;
        }
    }
}

?>