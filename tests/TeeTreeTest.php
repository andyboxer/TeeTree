<?php
/**
 * @package TeeTree
 * @author Andrew Boxer
 * @copyright Andrew Boxer 2012
 * @license Released under version 3 of the GNU public license - pls see http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

require_once __DIR__. "/../testServices/TeeTreeConfiguration.php";
require_once __DIR__ . "/../config/TeeTreeBootStrap.php";

// test for server heartbeat
if(TeeTreeController::pingServer("localhost", TeeTreeConfiguration::TEETREE_SERVER_PORT))
{
    echo " --- server heartbeat received\n";

    try
    {
        // define TeeTreeClients for each service class we wish to use

        // define service client using parameterised host and port
        class TeeTreeServiceHello extends TeeTreeClient{}

        // define a service where we define both host and port for the object proxy to contact the service broker
        class TeeTreeServiceRepeatToMe extends TeeTreeClient{protected $serviceHost = 'localhost'; protected $serviceControllerPort = TeeTreeConfiguration::TEETREE_SERVER_PORT;}

        // define a class for testing long running calls and paralell processing
        class TeeTreeServiceLongRunning extends TeeTreeClient{protected $serviceHost = 'localhost'; protected $serviceControllerPort = TeeTreeConfiguration::TEETREE_SERVER_PORT;}

        // create an instance using paramters for host and port
        $helloService = new TeeTreeServiceHello("host:localhost", "port:". TeeTreeConfiguration::TEETREE_SERVER_PORT, array("contructor_gets_these" => "data"));

        print("\nWe now call back the constructor params we instantiated this object with\n");
        // check we have correctly set the construcion parameters
        print(print_r($helloService->getConstructorParams(), true). "\n");

        // create an instance of the Repeter Test class
        $repeatService = new TeeTreeServiceRepeatToMe("param1", "param2", "param3");

        print("\nStarting long running background calls\n");
        // create an instance of the long running test class
        $longRunnerOne = new TeeTreeServiceLongRunning("Long running service no.1");

        // create another instance of the same service class
        $longRunnerTwo = new TeeTreeServiceLongRunning("Long running service no.2");

        // call a method which we can come back to later to get it's results
        $longRunnerOne->doLongRunning("no wait call no.1", TeeTreeServiceMessage::TEETREE_CALL_NOWAIT);

        // call a method which we can come back to later to get it's results again ( this will run after the above call has completed )
        $longRunnerOne->doLongRunning("no wait call no.2", TeeTreeServiceMessage::TEETREE_CALL_NOWAIT);

        print("\nWhile they run we'll call a different object method\n");
        // ordinary blocking call on a remote object
        echo $repeatService->repeatToMe("this is some different data"). "\n";

        // call a non-blocking fire and forget method several times, each method call will occur sequentially
        // Note: execution will not commence until the call to doLongRunning above has returned
        // as it will be queued behind the executing thread on $longRunnerOne
        print("\nStarting twenty fire and forget method calls\n");
        for($loop = 0; $loop < 10; $loop++)
        {
            $longRunnerTwo->doLongRunning("not waiting for this no.". $loop, TeeTreeServiceMessage::TEETREE_CALL_NORETURN);
        }

        print("That done we'll call another method on the other object\n");
        // method call returning an object
        $helloResponse = $helloService->sayHello("arg2", "arg1", array());
        print(print_r($helloResponse, true). "\n");

        print("\nNow call a few broken method calls\n");
        // The following call will throw a service side exception which should be passed back to the client and re-thrown at the client end
        try
        {
            $this_fails = $helloService->brokenCall();
        }
        catch(Exception $ex)
        {
            // we should have the message from the client in the exception
            print($ex->getMessage(). "\n");
        }

        // Try calling a non existant mehthod
        try
        {
            $this_fails = $helloService->nonExistantCall();
        }
        catch(Exception $ex)
        {
            print($ex->getMessage(). "\n");
        }

        print("\nNow we can wait for the responses from our earlier long running calls\n");
        // that done we can now go back to our long running method call from above and fetch the results
        $result = $longRunnerOne->getLastResponse();
        print_r($result);

        // that done we can now go back to our long running method call from above and fetch the results again
        $result = $longRunnerOne->getLastResponse();
        print_r($result);

        // now for some parallel processing
        // create and call the same object and method several times, each object instantiated will represent a different remote object
        // and each call will execute consecutively
        for($loop = 0; $loop < 10; $loop++)
        {
            $services[] = $service = new TeeTreeServiceLongRunning($loop);
            $service->doLongRunning( "consecutive:". $loop, TeeTreeServiceMessage::TEETREE_CALL_NOWAIT);
        }

        // now we call the same logging method as above but this time we wait for completion,
        // NOTE: this request is queued behind the calls made to the same instance of this object above
        print("\nWaiting for all queued logging to complete\n");
        $result = $longRunnerTwo->doLongRunning("but we are waiting for this");
        print_r($result);

        // now gather the responses from the above consecutive calls, the reads here will block until all threads have returned
        for($loop = 0; $loop < 10; $loop++)
        {
            $results[] = $services[$loop]->getLastResponse();
        }
        print_r($results);

    }
    catch(Exception $ex)
    {
        print('ERROR:'. $ex->getMessage());
    }
}

?>

