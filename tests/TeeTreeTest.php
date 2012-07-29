<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

set_include_path(get_include_path(). PATH_SEPARATOR. realpath(__DIR__. "/../client"). PATH_SEPARATOR. realpath(__DIR__. "/../shared"). PATH_SEPARATOR. realpath(__DIR__. "/../server"));
require_once 'TeeTreeController.php';
require_once 'TeeTreeClient.php';

// set a port for the service connection
$testPort = 10700;

// test for server heartbeat
if(TeeTreeController::pingServer("localhost", $testPort))
{
    echo " --- server heartbeat received\n";

    try
    {
        // define TeeTreeClients for each service class we wish to use
        // note here we define both host and port for the object proxy to contact the service broker
        class testServiceRepeater extends TeeTreeClient{protected $serviceHost = 'localhost'; protected $serviceControllerPort = 10700;}

        // define service client using parameterised host and port
        class testServiceHello extends TeeTreeClient{}

        // create an instance of the service class
        $service = new testServiceRepeater(array("construct"=>"params"));

        // create an instance using paramters for host and port
        $helloo = new testServiceHello("host:localhost", "port:". $testPort, array("contructor_gets_these" => "data"));

        // create another instance of the same service class
        $service2 = new testServiceRepeater();

        // call a method which we can come back to later to get it's results
        $service2->doLongRunning(TeeTreeServiceMessage::TEETREE_CALL_NOWAIT);

        // call a method which we can come back to later to get it's results again ( this will run after the above call has completed )
        $service2->doLongRunning(TeeTreeServiceMessage::TEETREE_CALL_NOWAIT);

        // ordinary blocking call on a remote object
        echo $service->getStuff("this is some different data"). "\n";

        // call a non-blocking fire and forget method several times, each method call will occur sequentially
        for($loop = 0; $loop < 5; $loop++)
        {
            $service->dontWait("not waiting for this", TeeTreeServiceMessage::TEETREE_CALL_NORETURN);
        }

        // method call returning an object
        $response = $helloo->sayHello("arg2", "arg1", array());
        print($response->test1. "\n");

        // The following call will throw a service side exception which should be passed back to the client and re-thrown at the client end
        try
        {
            $this_fails = $helloo->brokenCall();
        }
        catch(Exception $ex)
        {
            // we should have the message from the client in the exception
            print($ex->getMessage(). "\n");
        }

        // Try calling a non existant mehthod
        try
        {
            $this_fails = $helloo->nonExistantCall();
        }
        catch(Exception $ex)
        {
            print($ex->getMessage(). "\n");
        }

        // that done we can now go back to our long running method call from above and fetch the results
        $result = $service2->getLastResponse();
        print_r($result);

        // that done we can now go back to our long running method call from above and fetch the results again
        $result = $service2->getLastResponse();
        print_r($result);

        // now we call the same logging method as above but this time we wait for completion,
        // NOTE: this request is queued behind the calls made to the same instance of this object above
        print("\nWaiting for all queued logging to complete\n");
        $result = $service->dontWait("gonna wait for this one");
        print_r($result);

        // now for some parallel processing
        // create and call the same object and method several times, each object instantiated will represent a different remote object
        // and each call will execute consecutively
        //       for($loop = 0; $loop < 5; $loop++)
        //       {
        //           $services[] = $service = new testServiceRepeater($loop);
        //           $service->callNoWait("doLongRunning", "consecutive:". $loop);
        //       }

        // now gather the responses from the above calls, the reads here will block until all threads have returned
        //      for($loop = 0; $loop < 5; $loop++)
        //      {
        //          $results[] = $services[$loop]->getLastResponse();
        //      }
        //      print_r($results);

    }
    catch(Exception $ex)
    {
        print('ERROR:'. $ex->getMessage());
    }
}

?>

