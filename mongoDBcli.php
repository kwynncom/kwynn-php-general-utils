<?php

class dbqcl {

	public static function q($db, $q = false, $exf = false, $cmdPrefix = '') {
		
		if (strpos($q, 'printjson') === false) $q = 'printjson(' . $q . ')';

		if ($exf) {
			$p = $exf;
			if ($q === false) $q = file_get_contents($p);
		}
		else {
			kwas($q, "either send a query or a file containing a query");
			$p = tuf_once($q, 'kwqeq10_2021_' . md5($q), 'js');
  	    }
		
		if ($cmdPrefix) $cmdPrefix = $cmdPrefix . ' ';

		$cmd = $cmdPrefix . "mongo $db --quiet $p";
		$t   = shell_exec($cmd);
		$t   = self::processMongoJSON($t);
		$a = json_decode($t, true);
		if (is_array($a) && count($a) === 1) return $a[0];
		return $a;
	}
	
	public static function processMongoJSON($jin) { return preg_replace('/NumberLong\(["\']?(\d+)["\']?\)/', '$1' , $jin); }
	
	static function inFile($db, $fin, $qid) { 
		$allt = file_get_contents($fin); kwas($allt && is_string($allt), "from $fin bad query\n $allt"); unset($fin);
		$sp = strpos($allt, '// QID-' . $qid); kwas($sp !== false, "query $qid not found");
		$t = substr($allt, $sp); unset($allt, $sp, $qid);
		$sp = strpos($t, ';');
		if ($sp) $t = substr($t, 0, $sp);
		return self::q($db, $t);
	}
}