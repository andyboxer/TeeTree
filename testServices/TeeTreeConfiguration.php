<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

class TeeTreeConfiguration
{
    // the path to the php executable file - this MUST be an absolute path
    // However this example assumes that the executable is on the current users path
    const PATH_TO_PHP_EXE = "php";

    // the port on which the TeeTree controller has been configured to listen
    const TEETREE_SERVER_PORT = 11311;

    // the initial value for service instance message channel ports
    const MINIMUM_SERVICE_PORT = 22000;

    // the maximum value for service instance message channel ports
    const MAXIMUM_SERVICE_PORT = 48000;

    // maximum size of a TeeTree message, any large than this and you may just be using the wrong mechanism for it's transport.
    const MAX_MESSAGE_SIZE = 1000000;

    // socket accept timeout for all socket listeners
    const ACCEPT_TIMEOUT = 120;

    // timeout for socket read and write operations
    const READWRITE_TIMEOUT = 30;

    // timeout for connection to the TeeTree controller
    const CLIENT_CONNECT_TIMEOUT = 120;

    // The maximum number of times to try and send a constructor response back on a TeeTree controller pipe.
    const CONSTRUCTOR_MAX_RETRY = 5;

    // The delay in microseconds to wait before retrying a constructor call
    const CONSTRUCTOR_RETRY_DELAY = 500;

    // The minimum time before a TeeTree remote service instance may be re-used
    // Note: this value must be at least 1 to prevent object collisions.
    // A large value would give a larger pool of process connections ( be careful of memory usage if increased )
    const OBJECT_REUSE_DELAY = 7;

    // enable tracing of messages read and written by the service server call handler. Useful for debugging service classes
    const ENABLE_CALL_LOGGING = false;

    // the service class directory for the test scripts
    const TEETREE_SERVICE_CLASS_PATH = "/home/webapps/TeeTree/testServices";

    // the server error log
    const TEETREE_ERROR_LOG = "/var/log/TeeTree/testError.log";

    // the server message log
    const TEETREE_SERVER_LOG = "/var/log/TeeTree/testServer.log";

    // the path to the call tracing log file
    const TEETREE_CALL_LOG = "/var/log/TeeTree/testCall.log";

    // PLACE USER DEFINED VALUE PAIRS BELOW THIS LINE //

    // This is parameter for the use of the test scripts only it will have no meaning for nor effect upon the operation of the TeeTree RSI
    const DEFAULT_TEST_LOG = "/var/log/TeeTree/TeeTreeTest.log";        // the test service output log

    // This is parameter for the use of this example project only it will have no meaning for nor effect upon the operation of the TeeTree RSI
    const EXAMPLE_DEBUG_LOG = "/var/log/TeeTree/exampleServiceClass.log";       // the path to the Example debugging log
}
?>
