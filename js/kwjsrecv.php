<?php


function kwjssrp() {
	try {
		kwas(($j = isrv('POSTob')), 'no form object');
		$a = json_decode($j, 1); kwas($a, 'null form object');
		return $a;
	} catch(Exception $ex) {}
	
	return FALSE;
}