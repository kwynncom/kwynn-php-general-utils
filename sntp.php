<?php

class sntpSanity {
	
	const tln   = 4;
	const ipi   = self::tln;
	const tolns = M_BILLION;
	
	public static function ck(string $t, bool $contiff = false) {
		$o = new self($t, $contiff);
		return $o->getRes();
		
	}
	
	private function __construct(string $t, bool $contiff) {
		$this->contif  = $contiff;
		$this->oret = false;
		$this->oret = $this->ttoa($t);
	}
	
	public function getRes() {
		return $this->oret;
	}
	
	private function ass(bool $tock, string $msg) {
		if ($tock) return true;
		if ($this->contif) echo("**FAIL: " . $msg . "\n");
		else throw new Exception($msg);
		
	}
	
	private function ttoa(string $t) {
		
		static $failv = [];

		try {
			
			$o = $this;
			
			if (!$o->ass($t, 'blank string')) return $failv;
			
			$a  = explode("\n", trim($t)); unset($t); $o->ass($a && is_array($a) && count($a) >= self::tln, 'wrong lines sntp sanity check'); 
			$ip = getValidIPOrFalsey(kwifs($a, self::ipi)); 
			$a = array_slice($a, 0, self::tln);

			$n = self::tln;
			$o->ass(count($a) === $n, 'bad tline count sntp sanity 2');
			for ($i=0; $i < $n; $i++) {
				
				$a[$i] = intval($a[$i]);
			}
			$o->ass(count($a) >= 4, 'fail - for immediate tline sntp sanity purposes');

			$min = min($a);
			$max = max($a);
			$o->ass($max - $min < self::tolns, 'time sanity check fails');
			$ds = abs(nanotime() - $max);
			$o->ass($ds < self::tolns, 'time sanity check fail 2 - perhaps quota fail');
			$o->ass($a[1] <= $a[2], 'server time sanity check fail between in and out');
			$o->ass($a[0] <  $a[3], 'server time sanity check internal out and in');

			$ret['ip'  ] = $ip;
			$ret['Uns4'] = $a;

			return $ret;
		} catch (Exception $ex) {

		}
		
		return $failv;
	} // func

} // class