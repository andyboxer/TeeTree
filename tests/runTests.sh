## Run php distributed service server and execute test script
##

base_path=/home/webapps/TeeTree
php=/usr/local/zend/bin/php
servicePort=11311

$php $base_path/server/TeeTreeAdmin.php start $base_path/testServices $servicePort  &

## wait a moment to let the server spin up
sleep 2
$php $base_path/tests/TeeTreeTest.php

## multiple simultaneous processes test

$php $base_path/tests/TeeTreeMultiTest.php


$php $base_path/server/TeeTreeAdmin.php stop $base_path/testServices $servicePort 
