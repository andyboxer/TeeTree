<?php
class multi_command
{
    private $commands = array();

    public function add_command($cmd, &$response = null)
    {
        $this->commands[] = array('cmd'=>$cmd, 'response'=>$response);
    }

    public function get_commands()
    {
        return $this->commands;
    }

    public function clear_commands()
    {
        $this->commands = array();
    }

    public function count_commands()
    {
        return count($this->commands);
    }

    public function execute()
    {
        foreach($this->commands as &$command)
        {
            $command['proc'] = popen($command['cmd'], "r");
        }

        foreach($this->commands as &$command)
        {
            $response = '';
            while( !feof($command['proc']))
            {
                $response .= fgets($command['proc']);
            }
            $command['response'] = $response;
            pclose($command['proc']);
            unset($command['proc']);
        }
    }
}