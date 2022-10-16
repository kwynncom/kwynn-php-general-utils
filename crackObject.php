<?php
class crackObject {
	public function __construct($oin) {
		$this->do10($oin);
		return;
	
	}
	
	private function do10(&$oin) {
		if (is_object($oin)) $a = (array)$oin;
		else $a = $oin;
		
		foreach($a as $k => $v) {
			$ppn = self::ppn($k);
			if (!is_object($v)) $a[$ppn] = $v; 
			else				$a[$ppn] = $this->do10((array)$v);

			if ($ppn !== $k) unset($a[$k]);			
		}
		
		return $a;
	} // https://www.lambda-out-loud.com/posts/accessing-private-properties-php/

	public static function ppn(string $p) { if (kwifs($p, 0) === '*') return substr($p, 1); 	}
	
}