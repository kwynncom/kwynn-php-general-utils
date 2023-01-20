<?php

class sntpSanity {
	
	const tln   = 4;
	const ipi   = self::tln;
	const tolns = M_BILLION;
	const ssVersion = '2023/01/20 17:51 - printing PHP now';
	
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
			
			$a  = explode("\n", trim($t));
			$o->ass($a && is_array($a) && count($a) >= self::tln, 'wrong lines sntp ck - out: ' . "$t");  unset($t); 
			$ip = getValidIPOrFalsey(kwifs($a, self::ipi)); 
			$a = array_slice($a, 0, self::tln);

			$n = self::tln;
			$o->ass(count($a) === $n, 'bad tline count sntp sanity 2', true);
			for ($i=0; $i < $n; $i++) {
				if (is_numeric($a[$i])) 
					 $a[$i] = intval($a[$i]);
				else kwas(false, 'non-numeric value sent to time array 4 Uns');
			}

			$o->ass(count($a) >= 4, 'fail - for immediate tline sntp sanity purposes', true);

			$min = min($a);
			$max = max($a);
			$o->ass($max - $min < self::tolns, 'time sanity check fails');
			$nowns = nanotime();
			$ds = abs($nowns - $max);
			$o->ass($ds < self::tolns * 1.5 , 'time sanity check fail 2 - perhaps quota fail; now ns PHP = ' . number_format($nowns));
			$o->ass($a[1] <= $a[2], 'server time sanity check fail between in and out');
			$o->ass($a[0] <  $a[3], 'server time sanity check internal out and in');

			$ret['ip'  ] = $ip;
			$ret['Uns4'] = $a;
			$ret['sane'] = !$this->sanFail;

			return $ret;
		} catch (Exception $ex) {	}
		
		return $failv;
	} // func
	
	public static function SNTPOffset($T) {	return ((($T[1] - $T[0]) + ($T[2] - $T[3]))) >> 1;	}

} // class