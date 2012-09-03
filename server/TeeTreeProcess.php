<?php
class TeeTreeProcess extends TeeTreeServiceEndpoint
{
    public $id;
    public $processHandle;
    public $serviceClass;
    public $servicePort;
    public $startTime;
    public $lastUsedTime;

    public function __construct($id, $handle, $class, $port)
    {
        $this->id = $id;
        $this->processHandle = $handle;
        $this->serviceClass = $class;
        $this->servicePort = $port;
        $this->startTime = $this->lastUsedTime = time();
    }

    public function isUsable()
    {
        $now = time();
        if(  (!empty($this->servicePort)) && (($now - $this->lastUsedTime) >= TeeTreeConfiguration::OBJECT_REUSE_DELAY))
        {
            $this->lastUsedTime = $now;
            $status = $this->portStatus();
            return $status == 'LISTEN';
        }
        return false;
    }

    public function isRunning()
    {
        $status = proc_get_status($this->processHandle);
        return $status['running'] == 1;
    }

    public function portStatus()
    {
        if(!empty($this->servicePort))
        {
            // Note: stderr is re-directed to /dev/null here to avoid spurious messages should the pipe get rudely interupted.
            $cmd = "netstat -an --inet --tcp  | grep ". $this->servicePort. " | awk 'BEGIN{ listen = \"NULL\" } { if ( $6==\"LISTEN\" && listen==\"NULL\" ) { listen=\"LISTEN\" } if ( $6==\"ESTABLISHED\" ) { listen =\"ESTABLISHED\" } } END{ print listen }' 2> /dev/null";
            $response = @shell_exec($cmd);
            return trim($response, " \n");
        }
    }

    public function shutdown($force = false)
    {
        if(is_resource($this->processHandle))
        {
            $status = proc_get_status($this->processHandle);
            if((!$status['running']) || $force)
            {
                @proc_close($this->processHandle);
                return true;
            }
        }
        return false;
    }

    public function terminateClient()
    {
        if($serviceConnection = @stream_socket_client('tcp://localhost:'. $this->servicePort, $errno, $errstr, TeeTreeConfiguration::CLIENT_CONNECT_TIMEOUT))
        {
            stream_set_timeout($serviceConnection, TeeTreeConfiguration::READWRITE_TIMEOUT);
            $request = new TeeTreeServiceMessage($this->serviceClass, null, null, TeeTreeServiceMessage::TEETREE_TERMINATE);
            $this->writeMessage($serviceConnection, $request);
        }
    }
}

?>