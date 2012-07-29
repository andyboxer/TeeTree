<?php

require_once('TeeTreeServiceMessage.php');

class TeeTreeServiceEndpoint
{
    const MAX_MESSAGE_SIZE = 1000000;

    protected function readMessage($serviceConnection)
    {
        try
        {
            if($serviceConnection)
            {
                if(($response = stream_get_line($serviceConnection, self::MAX_MESSAGE_SIZE, "\n")) !== false)
                {
                    return TeeTreeServiceMessage::decode($response);
                }
                else
                {
                    $code = socket_last_error();
                    $errorMessage = socket_strerror($code);
                    throw new Exception("Error receiving service message response on ". stream_socket_get_name($serviceConnection, false). " :". $errorMessage);
                }
            }
            else
            {
                throw new Exception("Attempted to receive a message from a non-existant service connection");
            }
        }
        catch(Exception $ex)
        {
            throw new Exception("Unable to receive message on ". stream_socket_get_name($serviceConnection, false). " :". $ex->getMessage());
        }
    }

    protected function writeMessage($serviceConnection, TeeTreeServiceMessage $message)
    {
        try
        {
            if($serviceConnection)
            {
                if (!stream_socket_sendto($serviceConnection, $message->getEncoded()))
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
        catch(Exception $ex)
        {
            throw new Exception("Unable to send message on ". stream_socket_get_name($serviceConnection, false). " :". $ex->getMessage());
        }
    }

}