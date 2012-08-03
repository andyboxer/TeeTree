<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeServiceMessage
{
    const TEETREE_EMPTY         = "TEETREE_EMPTY";
    const TEETREE_CONSTRUCTOR   = "TEETREE_CONSTRUCTOR";
    const TEETREE_PORT_MESSAGE  = "TEETREE_PORT_MESSAGE";
    const TEETREE_CALL          = "TEETREE_CALL";
    const TEETREE_CALL_NOWAIT   = "TEETREE_CALL_NOWAIT";
    const TEETREE_CALL_NORETURN = "TEETREE_CALL_NORETURN";
    const TEETREE_FINAL         = "TEETREE_FINAL";
    const TEETREE_TERMINATE     = "TEETREE_TERMINATE";
    const TEETREE_ERROR         = "TEETREE_ERROR";

    public $serviceClass = null;
    public $serviceMethod = null;
    public $serviceData = null;
    public $serviceMessageType = self::TEETREE_EMPTY;

    public function __construct($class, $method, $data = null, $messageType = self::TEETREE_EMPTY)
    {
        $this->serviceClass = $class;
        $this->serviceMethod = $method;
        $this->setMessageType($data, $messageType);
        $this->serviceData = $data;
        if($this->serviceMessageType === self::TEETREE_PORT_MESSAGE) $this->parsePortMessage($data);
    }

    private function setMessageType(&$data, $messageType)
    {
        if($messageType === self::TEETREE_EMPTY)
        {
            if(!empty($data) && is_array($data))
            {
                $lastElement = end($data);
                if(is_string($lastElement) && preg_match("/^TEETREE_[A-Z_]+$/", $lastElement))
                {
                    return $this->serviceMessageType = array_pop($data);
                }
            }
            if(preg_match("/^(\w+)_(NOWAIT|NORETURN)$/", $this->serviceMethod, $matches))
            {
               $this->serviceMethod = $matches[1];
               return $this->serviceMessageType = (($matches[2] === 'NOWAIT') ? self::TEETREE_CALL_NOWAIT : (($matches[2] === 'NORETURN') ? self::TEETREE_CALL_NORETURN :  self::TEETREE_CALL));

            }
            $this->serviceMessageType = self::TEETREE_CALL;
        }
        else
        {
            $this->serviceMessageType = $messageType;
        }
    }

    private function parsePortMessage($data)
    {
        if(is_string($data) && preg_match("/^(\d+)$/", $data, $matches))
        {
            $this->serviceData = $matches[1];
        }
    }

    public function getEncoded()
    {
        return json_encode($this). "\n";
    }

    public static function decode($json)
    {
        $parts = preg_split("/\n/", $json, -1, PREG_SPLIT_NO_EMPTY);
        if((strlen($parts[0]) > 0) && ($object = json_decode($json)))
        {
            $message = new self($object->serviceClass, $object->serviceMethod, $object->serviceData, $object->serviceMessageType);
            if($message->serviceMessageType === self::TEETREE_ERROR)
            {
                throw new TeeTreeExceptionMessageReturned($message->serviceData);
            }
            return $message;
        }
        throw new TeeTreeExceptionMessageDecodeFailed("Unable to decode service message '". $json. "'");
    }
}
?>