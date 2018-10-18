<?php
require('vendor/autoload.php');
require('app/functions.php');

$f3 = Base::instance();										

$f3->config('app/config.ini');
if (file_exists('app/server.ini')) 
	$f3->config('app/server.ini');
$f3->config('app/routes.ini');

$f3->run();
