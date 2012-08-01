<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeLogMessage
{
    private $log_date;
    private $code;
    private $origin;
    private $message;

    public function __construct($message = "NONE", $code = "-", $origin = "UNK")
    {
        $this->log_date = Date('Y-m-d H:i:s');
        $this->code = $code;
        $this->origin = $origin;
        $this->message = $message;
    }

    public function getLogArray()
    {
        $log_Array = array(
			'log_date' => $this->log_date,
			'origin' => $this->origin,
			'code' => $this->code,
			'message' => substr($this->message, 0, $this->max_message_len),
        );
        return $log_Array;
    }

    public function __toString()
    {
        return "Date: ". $this->log_date. ", Origin: $this->origin, Code: $this->code, Message: ". $this->message. "\n";
    }
}