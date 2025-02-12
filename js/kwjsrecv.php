<?php

function kwjssrp($k = false, $ifns = null) {
try {

    if      (isset($_REQUEST[$k])) 
	    return     $_REQUEST[$k];

    kwas(isset($_REQUEST['POSTob']), 'no form object 20 - not set');

    kwas(($j = $_REQUEST['POSTob']), 'no form object - not truthy');
    $a = json_decode($j, 1); kwas($a, 'null form object');
    unset($a['XDEBUG_SESSION_START']);

    if ($k)  return kwifs($a, $k, ['kwiff' => $ifns]);
    return $a;
} catch(Exception $ex) {}

    return FALSE;
}


function isrv($k, $ifns = null) { return kwjssrp($k, $ifns); }
