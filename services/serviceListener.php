<?php
/**
 * @package objectServices
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class serviceListener
{
    private $socket;
    private $base;
    private $socketName;
    private $socketPort;
    private $events = array();
    private static $serviceController;
    private static $serviceId = 0;

    public function __construct($controller, $port)
    {
        self::$serviceController = $controller;
        $this->socketPort = $port;
        if($this->socket = stream_socket_server ('tcp://0.0.0.0:'. $port, $errno, $errstr))
        {
            stream_set_blocking($this->socket, 0);
            $this->base = event_base_new();
            $this->event = event_new();
            event_set($this->event, $this->socket, EV_READ | EV_PERSIST, 'serviceListener::eventAccept', $this->base);
            event_base_set($this->event, $this->base);
            event_add($this->event);
            event_base_loop($this->base);
        }
        else
        {
            throw new Exception("Failed to start service controller on port ". $port);
        }
    }

    public static function eventAccept($socket, $flag, $base)
    {
        self::$serviceId += 1;
        $connection = stream_socket_accept($socket);
        stream_set_blocking($connection, 0);
        $buffer = event_buffer_new($connection, 'serviceListener::eventRead', null, 'serviceListener::eventError', self::$serviceId);
        event_buffer_base_set($buffer, $base);
        event_buffer_timeout_set($buffer, 30, 30);
        event_buffer_watermark_set($buffer, EV_READ, 0, 0xffffff);
        event_buffer_priority_set($buffer, 10);
        event_buffer_enable($buffer, EV_READ | EV_PERSIST );
        $GLOBALS['connections'][self::$serviceId] = $connection;
        $GLOBALS['buffers'][self::$serviceId] = $buffer;
        self::$serviceController->logger->log("Connection :". self::$serviceId.
        						": Concurrent :". count($GLOBALS['connections']).
        						": Local      :". stream_socket_get_name($connection, false).
        						": Remote     :". stream_socket_get_name($connection, true), SERVICE_CONTROLLER_ACCEPT, 'serviceController');
    }

    public static function eventError($buffer, $error, $id)
    {
        self::closeThread($id);
    }

    public static function eventRead($buffer, $id)
    {
        $message = event_buffer_read($buffer, 256);
        if(preg_match("/^exit/i", $message)) die();
        if(preg_match("/^ping/i", $message))
        {
            self::pong($id);
            return;
        }
        try
        {
            self::$serviceController->spawnWorker($id, $message);
        }
        catch(Exception $ex)
        {
            $errorMessage = new serviceMessage('Listener', 'error', $message. "|". $ex->getMessage(), true);
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