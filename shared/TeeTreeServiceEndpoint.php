<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeServiceEndpoint
{
    protected function readMessage($serviceConnection)
    {
        if($serviceConnection)
        {
            try
            {
                $response = @stream_get_line($serviceConnection, TeeTreeConfiguration::MAX_MESSAGE_SIZE, "\n");
            }
            catch(Exception $ex)
            {
                $code = socket_last_error();
                $errorMessage = socket_strerror($code);
                throw new Exception("Error receiving service message response on ". stream_socket_get_name($serviceConnection, false). " $code :". $errorMessage);
            }
            if( $response !== false)
            {
                return TeeTreeServiceMessage::decode($response);
            }
            elseif($code = socket_last_error())
            {
                $errorMessage = socket_strerror($code);
                throw new Exception("Error receiving service message response on ". stream_socket_get_name($serviceConnection, false). " $code :". $errorMessage);
            }
        }
        else
        {
            throw new Exception("Attempted to receive a message from a non-existant service connection");
        }
    }

    protected function writeMessage($serviceConnection, TeeTreeServiceMessage $message)
    {
        if($serviceConnection)
        {
            if (!@stream_socket_sendto($serviceConnection, $message->getEncoded()))
            {
                $code = socket_last_error();
                $errorMessage = socket_strerror($code);
                throw new Exception("Error sending service message to service {$message->serviceClass}::{$message->serviceMethod} :". $errorMessage);
            }
        }
        else
        {
            throw new Exception("Attempted to send a message on a non-existant service connection");
        }
    }

}