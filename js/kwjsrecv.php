<?php

function kwjssrp	($k = false, $ifns = null) {
    $v10 = kwjssrp2025  ($k,	     $ifns);
    if ($v10 !== $ifns) return $v10; unset($v10);

    $rawBody = file_get_contents('php://input');
    $a = json_decode($rawBody, true);
    return $a[$k] ?? $ifns;
}

// 2026/01 - I'm pretty sure some of my code assumes false for the if not set.  
function kwjssrp2025($k = false, $ifns = FALSE) {
    try {

	if      (isset($_REQUEST[$k])) 
		return     $_REQUEST[$k];

	kwas(isset($_REQUEST['POSTob']), 'no form object 20 - not set');

	kwas(($j = $_REQUEST['POSTob']), 'no form object - not truthy');
	$a = json_decode($j, 1); kwas($a, 'null form object');
	unset($a['XDEBUG_SESSION_START']);

	$t15 = kwifs($a, 'dataset', $k);
	if ($t15) return $t15;

	if ($k)  return kwifs($a, $k, ['kwiff' => $ifns]);
	return $a;
    } catch(Exception $ex) {}

    return $ifns;
}


function isrv($k, $ifns = null) { return kwjssrp($k, $ifns); }
