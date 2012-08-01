<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeBootStrap
{
    public static $BASE_PATH;

    public static function boot()
    {
        self::$BASE_PATH = realpath(__DIR__ . "/../");
        spl_autoload_register('TeeTreeBootStrap::autoLoad');
    }

    public static function autoLoad($class)
    {
        $loaderGlobs = array("shared/", "server/" , "client/", "exceptions/");
        foreach($loaderGlobs as $glob)
        {
            $load_files = glob(self::$BASE_PATH. "/{$glob}{$class}.php");
            foreach($load_files as $file)
            {
                require_once($file);
                return;
            }
        }
    }
}

TeeTreeBootStrap::boot();

?>