<?php


function kwjssrp($k = false) {
	try {
		
		kwas(($j = isrv('POSTob')), 'no form object');
		$a = json_decode($j, 1); kwas($a, 'null form object');
		unset($a['XDEBUG_SESSION_START']);
		
		if ($k && ($v = kwifs($a, $k))) return $v;
		return $a;
	} catch(Exception $ex) {}
	
	return FALSE;
}