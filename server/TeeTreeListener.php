<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeListener
{
    private $socket;
    private $base;
    private $socketName;
    private $socketPort;
    private $events = array();
    private static $TeeTreeController;
    private static $serviceId = 0;


    public function __construct($controller, $port)
    {
        self::$TeeTreeController = $controller;
        $this->socketPort = $port;
        if($this->socket = stream_socket_server ('tcp://0.0.0.0:'. $port, $errno, $errstr))
        {
            stream_set_blocking($this->socket, 0);
            $this->base = event_base_new();
            $this->event = event_new();
            event_set($this->event, $this->socket, EV_READ | EV_PERSIST, 'TeeTreeListener::eventAccept', $this->base);
            event_base_set($this->event, $this->base);
            event_add($this->event);
            event_base_loop($this->base);
        }
        else
        {
            throw new TeeTreeExceptionControllerStartFailed("Failed to start service controller on port ". $port);
        }
    }

    private static function set_service_id()
    {
        do
        {
            $service_id = self::$serviceId++;
            $port = TeeTreeConfiguration::MINIMUM_SERVICE_PORT + $service_id;
            $in_use = self::port_in_use($port);
        }while($in_use);
        return $service_id;
    }

    private static function port_in_use($port)
    {
        $cmd = "netstat -nl -A inet | awk 'BEGIN {FS=\"[ :]+\"}{print $5}' | grep ". $port;
        $result = shell_exec($cmd);
        return strlen($result) > 0;
    }


    public static function eventAccept($socket, $flag, $base)
    {
        $service_id = self::set_service_id();
        $connection = stream_socket_accept($socket);
        stream_set_blocking($connection, 0);
        $buffer = event_buffer_new($connection, 'TeeTreeListener::eventRead', null, 'TeeTreeListener::eventError', $service_id);
        event_buffer_base_set($buffer, $base);
        event_buffer_timeout_set($buffer, 60, 60);
        event_buffer_watermark_set($buffer, EV_READ, 0, 0xffffff);
        event_buffer_priority_set($buffer, 60);
        event_buffer_enable($buffer, EV_READ | EV_PERSIST );
        $GLOBALS['connections'][$service_id] = $connection;
        $GLOBALS['buffers'][$service_id] = $buffer;
    }

    public static function eventError($buffer, $error, $id)
    {
        self::closeThread($id);
    }

    public static function eventRead($buffer, $id)
    {
        $message = event_buffer_read($buffer, 2046);
        if(preg_match("/^exit/i", $message)) die();
        if(preg_match("/^ping/i", $message))
        {
            self::pong($id);
            return;
        }
        try
        {
            self::$TeeTreeController->makeTee($id, $message);
        }
        catch(Exception $ex)
        {
            $errorMessage = new TeeTreeServiceMessage('Listener', null, $message. "|". $ex->getMessage(), TeeTreeServiceMessage::TEETREE_ERROR);
            if(isset($GLOBALS['connections'][$id]))
            fwrite($GLOBALS['connections'][$id], $errorMessage->getEncoded());
        }
    }

    private static function pong($id)
    {
        if(isset($GLOBALS['connections'][$id]))
        fwrite($GLOBALS['connections'][$id], "pong\n");
    }

    private static function closeThread($id)
    {
        event_buffer_disable($GLOBALS['buffers'][$id], EV_READ | EV_PERSIST);
        event_buffer_free($GLOBALS['buffers'][$id]);
        fclose($GLOBALS['connections'][$id]);
        unset($GLOBALS['buffers'][$id], $GLOBALS['connections'][$id]);
    }
}