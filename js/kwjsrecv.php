<?php

// 2026/01 - I'm pretty sure some of my code assumes false for the if not set.

function kwjssrp ($k = false,	$ifns = false)		      { return kwGetHTTPValsCl::kwjssrp($k, $ifns);	    }
function isrv	 ($k,	$ifns = false, string $allow = 'all') { return kwGetHTTPValsCl::kwjssrp($k, $ifns, $allow); }


class kwGetHTTPValsCl {

    private static function getRawPOST() {
	static $s;

	if (!isset($s)) { $s = file_get_contents('php://input');		}

	return $s;
    }


    private static function getRawPOSTAsArr() {
	static $a;

	if (!isset($a)) { 
	    $rawBody = self::getRawPOST();
	    $a = json_decode($rawBody, true);	
	}

	return $a;
    }

    private static function fromRawPOST($k, $ifns) {
	static $a = [];
	if (!$a) { $a = self::getRawPOSTAsArr(); }
	if ($k) { return $a[$k] ?? $ifns; }
	else { return $a; }
    }

    public static function kwjssrp($k, $ifns, $allow = 'all') {
	$v10 = self::kwjssrp2025	  ($k, $ifns, $allow);
	if ($v10 !== $ifns) return $v10; unset($v10);
	return self::fromRawPOST($k, $ifns);

    }
  
    private static function kwjssrp2025($k, $ifns, string $allow) {
	try {


	    $t10 = self::getFromSuperGlobals($k, $allow);
	    if (isset($t10)) return $t10; unset($t10);

	    $j = self::getFromSuperGlobals('POSTob', $allow);
	    kwas($j, 'no form object 20 (err # 121625 ) - not truthy');

	    $a = json_decode($j, 1); kwas($a, 'null form object');
	    unset($a['XDEBUG_SESSION_START']);

	    $t15 = kwifs($a, 'dataset', $k);
	    if ($t15) return $t15;

	    if ($k)  return kwifs($a, $k, ['kwiff' => $ifns]);
	    return $a;
	} catch(Exception $ex) {}

	return $ifns;
    }

    private static function getFromSuperGlobals($k, string $allowIN) {
	$allow = strtoupper($allowIN);		unset($allowIN);

	switch($allow) { 
	    case 'ALL' : $a = $_REQUEST; break;
	    case 'POST': $a = $_POST   ; break;
	    case 'GET' : $a = $_GET    ; break;
	    default: kwas(false, 'un-implemented param to isrv() or related (err # 121645 )'); break;
	}

	if ($k) { return $a[$k] ?? null; }
	else    { return $a; }
    }

}





