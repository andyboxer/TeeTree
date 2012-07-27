## Run php distributed service server and execute test script
##

base_path=/home/webapps/TeeTree
php=/usr/local/zend/bin/php

$php $base_path/server/TeeTreeLauncher.php 10700 $base_path/tests start &

## wait a moment to let the server spin up
sleep 2
$php $base_path/tests/TeeTreeTest.php

## multiple simultaneous processes test

##$php $base_path/tests/TeeTreeMultiTest.php

$php $base_path/server/TeeTreeLauncher.php 10700 $base_path/tests stop

