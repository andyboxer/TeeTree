# TeeTree Remote Service invocation mechanism

## Introduction

The TeeTree remote service invocation mechanism has been implemented entirely in php to provide asynchronous remote procedure calls.

### Pre-requisites

TeeTree is built using PHP CE 5.3 and replies upon the libevent module - <http://php.net/manual/en/book.libevent.php>
* <b>Note: You may need to install the libevent libraries onto the deployment machine ( for ubuntu "apt_get install libevent-dev" ).</b>

### Installation

In order to install and run TeeTree you will need to clone this repo to \<\<somewhere\>\> on each of your client and server machines.

The configuration is kept in a static const model for speed of execution. You should edit the TeeTreeConfiguration to suit your environment.

Paths and timeout limits should be set in the config/TeeTreeConfiguration.php file for the deployment environment. 

If your TeeTree installation is at \<\<somewhere\>\> and your php executable at \<\<php\>\> then in order to launch the TeeTreeController process one should execute the following:

    <<php>> <<somewhere>>/server/TeeTreeLauncher.php 11311 <<somewhere>>/testServices start &

This will start the TeeTree controller running on port 11311 and it will expect to find it's service classes defined in the \<\<somewhere\>\>/testServices directory.

Both of these parameters may bet set to suit your needs/environments, please NOTE: The correct port number used for the TeeTree controller must be set in the \<\<somewhere\>\>/config/TeeTreeConfiguration.php file.

### Tests

Before running the test scripts you should check the following:

* The path to the php executable is set in the TeeTreeConfiguration.php file
* The directory /var/log/TeeTree exists and is writeable for all 
* The base and php variables in the tests/runTests.sh file are correctly set for your environment.

From a command line execute the runTests.sh script, test results will be printed to stdout and diagnostic data will be written to the logs in the /var/log/TeeTree directory.

All tests will run using hostname localhost and port 11311, unless configured otherwise.

### Quick Start

An example with full instructions to get you off the ground quickly with TeeTree may be found at <https://github.com/andyboxer/TeeTreeExample>