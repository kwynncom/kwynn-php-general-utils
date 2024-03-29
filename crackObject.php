<?php
class crackObject {
	
	public static function crack(&$a) {
		if (is_object($a)) $a = (array)$a;
		
		if (!is_array($a)) return $a;
		foreach($a as $k => $v) {
			$ppn = self::ppn($k);
			if (is_object($v)) $v = (array)$v;
			 $a[$ppn] = self::crack($v); 
			if ($ppn !== $k) unset($a[$k]);			
		}
		
		return $a;
	} // https://www.lambda-out-loud.com/posts/accessing-private-properties-php/

	public static function ppn(string $p) { 
		if (substr($p, 0, 3) === "\x00*\x00") return substr($p, 3);
		else return $p;
	}
	
	public static function crackGoo($a) { 
		if (is_object($a)) $a = (array)$a;
		$j = json_encode($a);
		$j = str_replace("\u0000", '', $j);
		$j = str_replace("\\", '_', $j);		
		$j = str_replace("__", '_', $j);
		$a = json_decode($j, true);
		return $a;
	}
	
}