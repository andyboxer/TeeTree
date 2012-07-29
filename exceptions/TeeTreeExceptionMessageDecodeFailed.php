<?php
class TeeTreeMessageDecodeFailed extends TeeTreeException
{
    public function __construct($message)
    {
       parent::__construct($message, parent::TEETREE_EXCEPTION_MESSAGE_DECODE_FAILED);
    }
}