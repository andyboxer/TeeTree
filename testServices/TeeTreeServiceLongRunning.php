<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeServiceLongRunning
{
    public function doLongRunning($data)
    {
        for($i = 0; $i < 10; $i++)
        {
            usleep(200000);
            file_put_contents(TeeTreeConfiguration::DEFAULT_TEST_LOG, $i. ":". $data. "\n", FILE_APPEND);
        }
        return ("This message will be recived only if the calling client waits for a return value (i.e NORETURN is noe set)/n" . serialize($data) . "/n");
    }
}