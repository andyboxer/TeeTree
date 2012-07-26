## Run php distributed service server and execute test script
##

base_path=/home/webapps/TeeTree
php=/usr/local/zend/bin/php

$php $base_path/server/TeeTreeLauncher.php 10700 $base_path/tests start &

## wait a moment to let the server spin up
sleep 2
$php $base_path/tests/runServiceTest.php

## now step it up a bit run service multi will do the same as above but with 100 threads in paralell

##$php $base_path/tests/runServiceMultiTest.php

$php $base_path/server/TeeTreeLauncher.php 10700 $base_path/tests stop

