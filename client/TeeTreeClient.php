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
 * This class forms the client side end point for a remote TeeTree service object instance.
 * The TeeTreeClient class provides the network connections and communications neccessary
 * to invoke a remote TeeTree service instance and make calls against it.
 *
 * The __call magic method is used to capture calls to the TeeTree client object instance
 * translate them into network messages, field return values and handle any exceptions meantime.
 *
 * This class must be extended in your client code for each remote service class you intend to use
 * The class names of your subclasses must be the same as those of the remote classes you wish to instantiate from.
 *
 * eg. In order to use a remote service object called AService I must creat a TeeTree client proxy class for that remote class
 *
 *     class AService extends TeeTreeClient{}
 *
 * Once defined you may instantiate this class in your source code and make method calls against it.
 *
 * eg.
 *
 * 		$aservice = new AService();
 *
 * Calls made on the object will be translated into network messages and passed to the remote service object.
 * Return values are passed back and returned from the method calls.
 *
 * eg. $returnValue = $aservice->amethod();
 *
 *
 */
class TeeTreeClient extends TeeTreeServiceEndpoint
{
    // The remote host to which we are connecting
    protected $serviceHost;
    // The port number of the TeeTree controller instance
    protected $serviceControllerPort;
    // The port number of the invoked remote TeeTree service instance
    protected $servicePort = null;
    // The connection resource for the TeeTree event listener connection
    protected $serviceConnection = null;
    // The instantiation parameters obtained during this object instantiation
    protected $data = null;

    public function __construct()
    {
        // First we parse out any host / port values sent in the construction params
        // This provides a way to dynamically set the host and port for a client object instance.
        // The host and port values may be sent using string prefixes as follows
        // eg. new TeeTreeClient('host:www.remoteserver.co', 'port:88')
        // This would cause the client to attempt to connect to remoteserver.co on port 88
        // When using the constructors in this way the host and port parameters will be
        // removed from the constructor parameters before they are passed to the remote constructor
        $this->parseArgs(func_get_args());

        // Now connect to the TeeTree Controller & prepare out remote object instance
        $this->connectServiceController();

        // We can now attempt to connect to the remote object
        $this->connectService();
    }
    /**
     *
     * This function extracts port and host parameters from the constructor parameters array
     * If they exist they are removed from the construction parameter array
     *
     * @param array $args the constructor parameter array
     */
    private final function parseArgs(array $args)
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

    /**
     *
     * Override the __get magic method in order to give read-only access to the objects constructor parameter arrray.
     * @param string $name - the called property name.
     */
    public function __get($name)
    {
        if(isset($this->data[$name])) return $this->data[$name];
        return null;
    }

    /**
     *
     * Clean up on the way out
     */
    public function __destruct()
    {
        $this->finishTee();
    }

    /**
     *
     * Sends a shutdown message to the remote object instance
     * This affects the connection to the remote service only
     * and does not neccessarily stop the remote service instancance.
     */
    public final function finishTee()
    {
        $request = new TeeTreeServiceMessage(get_called_class(), null, null, TeeTreeServiceMessage::TEETREE_FINAL);
        if($this->serviceConnection) $this->writeMessage($this->serviceConnection, $request);
    }

    /**
     *
     * Overrides the __call magic method in order to capture method calls on the instantiated object
     * The method call is translated into a remote service request and sent to the TeeTree service instance.
     * Depending upone the type of the method call the return value may be collected in the following modes
     *
     * TEETREE_CALL			  : This is a standard remote service instance method call the remote object method is called and
     * 						  : calling code will wait for a return value before continuing.
     *                        : In order to make such a call the normal method call syntax is used
     *
     * TEETREE_CALL_NOWAIT    : This is a non block mode and the method call once triggered on the remote object instance returns
     * 				          : calling code may continue and return to collect the return value later using the getLastResponse method.
     *                        : In order to make such a call you may either call using the normal method syntax and adding the
     *                        : call modify parameter TeeTreeServiceMessage::TEETREE_CALL_NOWAIT to the end of the call parameter list
     *                        : eg. $aservice->aMethod("my method", "call parameters", TeeTreeServiceMessage::TEETREE_CALL_NOWAIT);
     *                        : this MUST be the last parameter in the parameter list.
     *                        : OR you can call using the required method name with _NOWAIT appended to it
     *                        : eg. $aservice->aMethod_NOWAIT("my method", "call parameters");
     *
     * TEETREE_CALL_NORETURN  : This is a non blocking fire and forget mode. The remote service instance method call is triggered
     *                        : the method immediately returns and the call code may continue. No return value will be sent back to the client.
     *                        : In order to make such a call you may either call using the normal method syntax and adding the
     *                        : call modify parameter TeeTreeServiceMessage::TEETREE_CALL_NORETURN to the end of the call parameter list
     *                        : eg. $aservice->aMethod("my method", "call parameters", TeeTreeServiceMessage::TEETREE_CALL_NORETURN);
     *                        : this MUST be the last parameter in the parameter list.
     *                        : OR you can call using the required method name with _NORETURN appended to it
     *                        : eg. $aservice->aMethod_NORETURN("my method", "call parameters");
     *
     * @param string $name - The name of the called method
     * @param array $args - The method call parameter list
     */
    public final function __call($name,array $args)
    {
        $request = new TeeTreeServiceMessage(get_called_class(), $name, $args);
        if($request->serviceMessageType === TeeTreeServiceMessage::TEETREE_CALL_NORETURN || $request->serviceMessageType === TeeTreeServiceMessage::TEETREE_CALL_NOWAIT)
        {
            $this->writeMessage($this->serviceConnection, $request);
            return;
        }
        return $this->converse($this->serviceConnection, $request)->serviceData;
    }

    /**
     *
	 * This method will read a response message from the remote service client.
	 * It will wait until a full line has been returned from the client
	 * Or the read time out ( see the TeeTreeConfiguration file for details on timeouts
     */
    public final function getLastResponse()
    {
        return $this->readMessage($this->serviceConnection);
    }

    /**
     *
     * Convenience method to build the TeeTree controller connection string
     * Ensuring a valid controller port has been set.
     */
    private final function buildControllerConnectString()
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

     /**
     *
     * Convenience method to build the TeeTree remote service connection string
     * Ensuring a valid remote service port has been set.
     */
    private final function buildServiceConnectString()
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

    /**
     *
     * This method creates a connection to the TeeTree Controller
     * If a successfull connection has been made an attempt is made to instantiate the remote service object
     * If the remote service object is correctly instantiated the a valid service connection port number will be returned
     * Using this port number the client object may connect directly to the remote service object instance.
     *
     * @throws TeeTreeExceptionBadPortNo - The port number message returned from the remote service invocation was invalid
     * @throws TeeTreeExceptionServerConnectionFailed - The connection to the TeeTree server failed
     */
    private final function connectServiceController()
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
                usleep(100);
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

    /**
     * This method opens a tcp connection direct to the process which hosts the remote service object instance
     * This method uses the port number returned above from the remote service object instantiation call.
     *
     * @throws TeeTreeExceptionServiceClientConnectionFailed - The method was unable to connect to the remote service object instance.
     */
    private final function connectService()
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

    /**
     *
     * This method performs a standard blocking remote method call.
     * Using the writeMessage and readMessage calls supplied by the parent TeeTreeServiceEndpoint class
     * a remote method call message is sent to the remote object instance and the return values are
     * waited for and returned to the calling code.
     *
     * @param resource $serviceConnection - The connection to the remote service object instance.
     * @param TeeTreeServiceMessage $request - The method call details and parameters
     * @throws TeeTreeExceptionClientNotConnected - The TeeTree remote client instance connection passed in was not valid.
     */
    private final function converse($serviceConnection, TeeTreeServiceMessage $request)
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