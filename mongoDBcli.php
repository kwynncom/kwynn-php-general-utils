<?php

class dbqcl {
	
	public static function addToA($q, $k) {
		$rq = strrev($q);
		$pp = strpos($rq, ')');
		$ns  = '';
		$ns .= substr($rq, 0, $pp + 1);
		$ns .= strrev($k);
		$ns .= substr($rq, $pp  + 1);
		$ns = strrev($ns);
		return $ns;
		
		
	}
	
	public static function q($db, $q = false, $exf = false, $cmdPrefix = '', $rawc = false, $csuf = '', $ecc = false, $doit = true) {
		
		if ((strpos($q, 'printjson' ) === false) && !$rawc) $q = 'printjson(' . $q . ')';
		
		$tok = '.toArray()';
		if (strpos($q, $tok) === false && strpos($q, ').count(') === false && !$rawc
				&& strpos($q, 'findOne') === false) $q = self::addToA($q, $tok);

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
		if ($csuf) $cmd .= ' ' . $csuf;
		if ($ecc) echo($cmd . "\n");
		if (!$doit) return;
		$t   = shell_exec($cmd);
		if (!$rawc) {
			$t   = self::processMongoJSON($t);
			$a = json_decode($t, true);
			if (is_array($a) && count($a) === 1)
				if (isset($a[0])) {
					$r = $a[0];
					if (is_array($r) && count($r) === 1) return reset($r);
					return $a[0];
				}
				else return reset($a);
			return $a;
		}
		else return $t;
		
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