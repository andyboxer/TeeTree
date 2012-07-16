## Run php distributed service server and execute test script
##

base_path=/home/webapps/baseObjects
php=/usr/local/zend/bin/php

export SERVICE_PORT=10700
export SERVICE_CLASS_PATH=$base_path/testServices

$php $base_path/services/serviceLauncher.php &

## wait a moment to let the server spin up
sleep 2
$php $base_path/testServices/runServiceTest.php

## now step it up a bit run service multi will do the same as above but with 100 threads in paralell

$php $base_path/testServices/runServiceMultiTest.php

