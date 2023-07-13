<?php

class sntpSanity { // called from chm/nist/callSNTP.php
	
	const tols  = 1;
	const tolgs = self::tols + 1; // gross tolerance in seconds
	const ssVersion = '2023/01/20 19:26 - printing PHP hutime';
	
	public static function ck(string $t, bool $contiff = false) {
		$o = new self($t, $contiff);
		return $o->getRes();
		
	}
	
	private function __construct(string $t, bool $contiff) {
		$this->contif  = $contiff;
		$this->oret = false;
		$this->sanFail = false;
		$this->oret = $this->ttoa($t);
	}
	
	public function getRes() {
		return $this->oret;
	}
	
	private function ass(bool $tock, string $msg, bool $failAnyhow = false) {
		if ($tock) return true;
		$this->sanFail = true;
		if ($this->contif && !$failAnyhow) echo("**FAIL: " . $msg . "\n");
		else throw new Exception($msg);
		
	}
	
	private function ttoa(string $t) {
		
		static $failv = [];

		try {
			
			$o = $this;
			
			if (!$o->ass($t, 'blank string')) return $failv;
			
			$a  = json_decode($t, true);
			$o->ass($a && is_array($a), 'sntp result not array - out: ' . "$t");  unset($t); 
			$ip = getValidIPOrFalsey(kwifs($a, 'ip')); kwas($ip, 'sntp invalid ip');
			
			kwas(is_numeric($a['offset']) && abs($a['offset']) <= self::tols, 'non-numeric sntp 2038');

			$nowus = microtime(true);
			$polus = self::stt($a['time']);
			$dus = abs($polus - $nowus);

			$o->ass($dus <= self::tols, 
					'time sanity check fail 2 - perhaps quota fail; now ns PHP = ' . date('H:i:s', intval($polus)));

			$ret = $a;
			$ret['sane'] = !$this->sanFail;
			$ret['U'] = intval(floor($polus));
			$ret['Uus'] = $polus;

			return $ret;
		} catch (Exception $ex) {	}
		
		return $failv;
	} // func
	
	public static function stt(string $s) : int | float {
		$nows = time();
		$t10 = strtotime($s); kwas(is_numeric($t10) && abs($nows - $t10) <= self::tolgs, 'bad time tolerence gross - sntp');
		$fsa = preg_match('/\.(\d+)/', $s, $m); kwas(isset($m[1]), 'no microseconds found in string');
		$uso = floatval('0' . $m[0]);
		$nowus = microtime(true);
		$dus = abs($nowus - $t10);
		if ($dus < 0.002) return $t10;
		
		$t20 = $t10 + $uso; 
		$d20 = abs($t20 - $nowus); 
		kwas($d20 <= self::tols, 'bad time tolerence us');

		return $t20;
		
	}
	
	public static function SNTPOffset($T) {	return ((($T[1] - $T[0]) + ($T[2] - $T[3]))) >> 1;	}

} // class