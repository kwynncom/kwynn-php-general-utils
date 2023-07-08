<?php

$KWYNN_COMPOSER_PATH = '/opt/composer';

if (file_exists($KWYNN_COMPOSER_PATH )) { 
	set_include_path(get_include_path() . PATH_SEPARATOR . $KWYNN_COMPOSER_PATH ); 
	require_once('vendor/autoload.php'); 
	unset($__composer_autoload_files);  // I unset $__composer... because (often) I am trying to keep a very clean set of active variables. 
}

unset($KWYNN_COMPOSER_PATH ); // same - no need for it anymore; keeping clean set of vars
