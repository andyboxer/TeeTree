# TeeTree Remote Service invocation mechanism

## Introduction

The TeeTree remote service invocation mechanism has been built using pure php to provide asynchronous remote procedure calls.

### Pre-requisites

TeeTree is built using PHP CE 5.3 and replies upon the libevent module - <http://php.net/manual/en/book.libevent.php>

### Installation

In order to install and run TeeTree you will need to clone this repo to <<somewhere>> on each of your client and server machines.

The configuration is kept in a static const model for speed of execution. Paths and timeout limits should be set in the config/TeeTreeConfiguration.php file for the given environment.

If your TeeTree installation is at <<somewhere>> and your php executable at <<php>> then in order to launch the TeeTreeController process one should execute the following:

    <<php>> <<somewhere>>/server/TeeTreeLauncher.php 11311 <<somewhere>>/testServices start &

This will start the TeeTree controller running on port 11311 and it will expect to find it's service classes defined in the <<somewhere>>/testServices directory.

Both of these parameters may bet set to suit your needs/environments, please NOTE: The correct port number used for the TeeTree controller must be set in the <<somewhere>>/config/TeeTreeConfiguration.php file.

