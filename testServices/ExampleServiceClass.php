<?php
/**
 * @package TeeTreeExample
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

// This is an endpoint for service method calls.
// Resources created here may persist for the lifetime of each service instance
// You may create as many classes as you wish, they must all however be accessable via
// the single service class directory configured upon starting the TeeTree Controller
// Note: The TeeTree framework provides a base environment for all
// serialisation and communication between client and server.

class ExampleServiceClass
{
    // When the service instance is instantiated the parameters passed to the proxy constructor
    // are passed to the service instance save them in order that your class and supporting code may reference them.

    private $constructParams = array();

    public function __construct($args)
    {
        $this->constructParams = $args;
    }

    public function getConstructorParams()
    {
        return $this->constructParams;
    }

    // All service method calls must be public and non-static
    // The methods will receive the same parameters that the client side proxy is called with as and args array
    // In this case a single string and returns the same string shuffled.
    // Return parameters are received by the client as returned from the method calls.

    public function shuffle($message)
    {
        usleep(333333);
        if(!isset($message[0])) return "?";
        // If this method call does not seem to be working check the debug trace at /tmp/debug.log
        // Remove this line if you wish to benchmark the system
        // file_put_contents(TeeTreeConfiguration::EXAMPLE_DEBUG_LOG, $message[0], FILE_APPEND);
        $retval = str_shuffle($message[0]);
        return "bakatcha: {$retval}";
    }
}