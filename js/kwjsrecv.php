<?php


function kwjssrp($k = false) {
	try {
		
		kwas(($j = isrv('POSTob')), 'no form object');
		$a = json_decode($j, 1); kwas($a, 'null form object');
		unset($a['XDEBUG_SESSION_START']);
		
		if ($k)  return kwifs($a, $k);
		return $a;
	} catch(Exception $ex) {}
	
	return FALSE;
}