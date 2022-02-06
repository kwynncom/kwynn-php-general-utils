<?php

class inonebuf extends dao_generic_3 {

	const bufc = 1000;
	
	public function __construct() {
		
	}

public static function inonebuf($d, $c) {
	static $b = [];
	static $i = 0;
	static $t = 0;
	static $bc = self::bufc;

	$isd = is_array($d) || is_object($d);

	if ($isd) { $b[] = $d; $i++; }

	if (($i >= $bc) || (!$isd && $i > 0))
	{ 
		$r = $c->insertMany($b); 
		kwas($r->getInsertedCount() === $i, 'bad bulk insert count kwutils 0240');
		$t += $i; $b = []; $i = 0;
	}	

	return $t;
}
}

function inonebuf($d, $c) {
	inonebuf::inonebuf($d, $c);
}