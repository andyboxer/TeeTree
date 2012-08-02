<?php
/**
 *
 * This class provides curl_multi type functionality for arrays of command line commands
 *
 */
class multi_command
{
    private $commands = array();

    /**
     *
     * Add a command to the command array
     */
    public function add_command($cmd, &$response = null)
    {
        $this->commands[] = array('cmd'=>$cmd, 'response'=>$response);
    }

    /**
     *
     * Get the array of commands
     */
    public function get_commands()
    {
        return $this->commands;
    }

    /**
     *
     * Clear the array of commands
     */
    public function clear_commands()
    {
        $this->commands = array();
    }

    /**
     *
     * Return count of the number of stored commands
     */
    public function count_commands()
    {
        return count($this->commands);
    }

    /**
     *
     * Execute the batch of commands using proc_open
     * The wrapper will execute each process in paralell then wait until all the processes have returned.
     * Responses from the processes executed are stored in the 'response' element of the $command array.
     * Client code may call the get_commands() method to retrieve the command array and interogate the response values.
     *
     */
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