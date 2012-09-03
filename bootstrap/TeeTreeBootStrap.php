<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

/**
 *
 * This class provides an autloader for the TeeTree object classes required to run the TeeTree client and server
 *
 */

class TeeTreeBootStrap
{
    // This stores the home directory for this TeeTree installation.
    public static $TEETREE_HOME;
    // This stores the current directory and loads the autoloader
    public static function boot() {
        self::$TEETREE_HOME = realpath(__DIR__ . "/../");
        spl_autoload_register('TeeTreeBootStrap::autoLoad');
    }
    // The autoloader is configured with references to all the require TeeTree directories
    public static function autoLoad($class){
        $loaderGlobs = array("shared/", "server/" , "client/", "exceptions/");
        foreach($loaderGlobs as $glob){
            $load_files = glob(self::$TEETREE_HOME. "/{$glob}{$class}.php");
            foreach($load_files as $file){
                require_once($file);
                return;
            }
        }
    }
}
// When this file in included or required in client code we call boot automatically in order to configure the loader.
TeeTreeBootStrap::boot();
?>