<?php

class machine_id_specific {

public static function set($rin, &$toset) {
    kwas(isset($rin['isAWS']), 'isAWS not set - mid eval');
    if (!$rin['isAWS']) {
	kwas(isset($rin['private_field_count']), 'private count unset machine id eval');
	kwas(      $rin['private_field_count'] >= 2, 'insufficent private count machine id eval');
	$permHash = '$argon2id$v=19$m=65536,t=5,p=12$a1EyZnJGcDc1L0dFMWRUUg$HQgUjPzPt3Yt16eU/RWGe4ARHfgpzCIa32OlesPm70g';
	if (password_verify($rin['private_string'], $permHash)) {
	    $mid = 'kwmid20mid';
	    self::testOut($mid);
	    $toset['myname'] = $mid;
	    $toset['perm_hash'] = $permHash;
	    return;
	}
	kwas(false, 'cannot ID machine - 554');
    }
    
    $permHash = '$argon2id$v=19$m=16384,t=8,p=2$aXV6ZEIueGtGV1ZOcUg0VQ$pnAPYy1PKrG7V5uQI3rtLgELWwXkuz1aXfSW7f6no08';
    
    if (password_verify($rin['private_string'], $permHash)) {
	$mid = 't3-1';
	self::testOut($mid, $rin);
	$toset['myname'] = $mid;
	$toset['perm_hash'] = $permHash;
	return $mid;
    }
    
    kwas(false, 'not supporting arbitrary AWS instances yet');
}

public static function verifyWithSigsOrDie($a) {
    static $fixedHash  = '$argon2id$v=19$m=8192,t=40,p=2$RkpTOW8zbU1oZS9GSXptUw$Ewd2N7n5ucjTsDoXKeDn17f53QbfwqmVHL2FcMEc7UI';
    static $fixedSize = 2718;
    kwas(strlen($a) === $fixedSize, 'verify sig failed');
    kwas(password_verify($a, $fixedHash), 'verify sig password fail');
    return 'verified 3 AWS EC2 ID docs by size and hash';
}

public static function hash($din) {
    if (!isAWS()) $ps = ['memory_cost' => 2 << 15, 'time_cost' => 5, 'threads' => 12];
    else          $ps = ['memory_cost' => 2 << 13, 'time_cost' => 8, 'threads' => 2];
    return password_hash($din, PASSWORD_ARGON2ID, $ps);  
}

private static function testOut($mid, $rin = false) {
    echo($mid . " = assigned MID\n");
    if ($rin) self::altFormAWS($rin);
    
}

private static function altFormAWS($rin) {
    $alt = substr($rin['board_asset_tag'], 0, 7);
    return $alt;
    
}

}