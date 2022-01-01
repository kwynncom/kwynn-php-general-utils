<?php
require_once(__DIR__ . '/../' . 'kwutils.php');

class mongocli_tests {
	
	const dbcnm = 'kwcliTest10'; // to use the same collection name, has to agree with queries.js
	const qfile = __DIR__ . '/queries.js';
	
	public static function doit() {
		self::t10();
		self::t20();
	}
	
	private static function t20() {
		$r = self::qf('insert-10');
		
	}
	
	private static function qf($id) {
		echo("file q id === $id\n");
		$r = dbqcl::byFile(self::dbcnm, self::qfile, $id);
		echo("result === " . print_r($r) . "\n");
		return $r;
		
	}
	
	private static function t10() {
		$d = self::dbcnm;
		$q10 = "db.getCollection('$d').drop()";
		$r = self::q($q10);
		kwas($r === false || $r === true);
		return;
	}
	
	private static function q($q) {
		static $dbs = false;
		
		if ($dbs === false) {
			echo("database === " . self::dbcnm . "\n");
			$dbs = true;
		}
		
		echo("q === $q\n");
		$r = dbqcl::q(self::dbcnm, $q);		
		echo("result === $r\n");
		return print_r($r);
	}
	
	
}

if (didCLICallMe(__FILE__)) mongocli_tests::doit();