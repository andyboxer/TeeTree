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
    const TEETREE_NOWAIT = "TEETREE_NOWAIT";
    const TEETREE_NOWAIT_NORETURN = "TEETREE_NOWAIT_NORETURN";
    const TEETREE_FINAL = "TEETREE_FINAL";
    const TEETREE_TERMINATE = "TEETREE_TERMINATE";

    public $serviceClass = null;
    public $serviceMethod = null;
    public $serviceData = null;
    public $isConstructor = false;
    public $isError = false;
    private $isLast = false;
    private $doResponse = true;

    public function __construct($class, $method, $data = null, $isError = false, $close = false)
    {
        $this->serviceClass = $class;
        $this->serviceMethod = $method;
        $this->serviceData = $data;
        $this->isConstructor = ($method === 'construct');
        $this->isError = $isError;
        $this->isLast = $close;
    }

    public function getConstructPortNumber()
    {
        if($this->isConstructor && preg_match("/^(\d+)$/", $this->serviceData, $matches))
        {
            return $matches[1];
        }
        throw new Exception("Constructor called on non construction response");
    }

    public function getEncoded()
    {
        return json_encode($this). "\n";
    }

    public static function decode($json)
    {
        if(strlen($json) === 0) return null;
        if($object = json_decode($json))
        {
            $message = new self($object->serviceClass, $object->serviceMethod, $object->serviceData, $object->isError);
            if($message->isError)
            {
                throw new Exception($message->serviceData);
            }
            return $message;
        }
        throw new Exception("Unable to decode service message '". $json. "'");
    }
}
?>