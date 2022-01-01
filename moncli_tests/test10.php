<?php
require_once(__DIR__ . '/../' . 'kwutils.php');

class mongocli_tests {
	
	const dbcnm = 'kwcliTest10'; // assuming in queries.js and below that coll name is also this
	const qfile = __DIR__ . '/queries.js';
	
	public static function doit() {
		self::t10();
		self::t20();
		self::t30();
	}
	
	private static function t30() {
		
	}
	
	private static function t20() {
		$r = self::qf('insert-10');
		kwas($r['nInserted'] === 3, 'bad result t20');
		$q = "db.getCollection('kwcliTest10').find({}).toArray()";
		$r = self::q($q);
		kwas($r && is_array($r) && count($r) === 3, "bad test t20 2 moncli");
		$r = self::qf('sum-10');
		kwas(intval(round($r['sumn'])) === 6, 'bad result t20 sum');
		
		$r = self::q(false, __DIR__ . '/q10.js');
		kwas($r['v'] === 'standalone', 'sa file failed'); 
		
		kwas(self::qf('drop-final-coll') === true, 'coll final drop fail');
		$rdd = self::qf('drop-final-db');
		kwas($rdd['ok'] === 1, 'drop final db fail');
		echo("TESTS OK!\n");
		return;
		
	}
	
	private static function qf($id) {
		echo("file q id === $id\n");
		$r = dbqcl::inFile(self::dbcnm, self::qfile, $id);
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
	
	private static function q($q, $file = false) {
		static $dbs = false;
		
		if ($dbs === false) {
			echo("database === " . self::dbcnm . "\n");
			$dbs = true;
		}
		
		echo("q === $q\n");
		$r = dbqcl::q(self::dbcnm, $q, $file);		
		echo("result === " . print_r($r) . "\n");
		return $r;
	}
	
	
}

if (didCLICallMe(__FILE__)) mongocli_tests::doit();