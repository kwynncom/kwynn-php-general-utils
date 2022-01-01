<?php

class dbqcl {

	public static function q($db, $q) {
		
		if (strpos($q, 'printjson') === false) $q = 'printjson(' . $q . ')';

		$p = '/tmp/kwqeq10_2021_' . md5($q) . '_' . get_current_user() . '.js';

		if (!file_exists($p)) {
			file_put_contents($p, '');
			chmod($p, 0600);
			file_put_contents($p, $q);
		}

		$cmd = "mongo $db --quiet $p";
		$t   = shell_exec($cmd);
		$a = json_decode($t, true);
		if (is_array($a) && count($a) === 1) return $a[0];
		return $a;
	}
	
	static function byFile($db, $fin, $qid) { 
		$allt = file_get_contents($fin); kwas($allt && is_string($allt), "from $fin bad query\n $allt"); unset($fin);
		$sp = strpos($allt, '// QID-' . $qid); kwas($sp !== false, "query $qid not found");
		$t = substr($allt, $sp); unset($allt, $sp, $qid);
		$sp = strpos($t, ';');
		if ($sp) $t = substr($t, 0, $sp);
		return self::q($db, $t);
	}
}