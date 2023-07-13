<?php // test sntpw / s.php / sntpSanity from chm


class ips_kw {

	const v41core = '((\d+){1,3}\.){3}(\d+){1,3}';
	const v61core = '[0-9A-Fa-f:]{2,38}[0-9A-Fa-f]';
	const v41re = '/^'  . self::v41core . '$/';
	const v61re = '/^'  . self::v61core . '$/';
	const noncs = '[^0-9A-Fa-f:\.\b]{1}';
	const v4allre = '/' . ''  . self::noncs . '(' . self::v41core . ')' . self::noncs .   '/';
	const v6allre = '/' .   self::v61core .  '/';

public static function getAllIPs(string $t) : array {
	$v4 = self::getAllIPv4($t);
	$v6 = self::getAllIPv6($t);
	$ret = kwam($v4, $v6);
	return $ret;
}

public static function getAllIPv6(string $tin) : array {
	
	$ret = [];

	preg_match_all(self::v6allre, $tin, $a10);	unset($tin);
	if (!isset($a10[0])) return $ret;
	

	foreach($a10[0] as $t) {
		preg_match_all('/:/', $t, $cs);
		$t10 = kwifs($cs, 0);
		if ($t10) { 
			if (preg_match(self::v61re, $t)) $ret[] = $t;
		}
		continue;
	}
	
	return $ret;
	
}

public static function getAllIPv4(string $t) : array {
	$ret = [];
	preg_match_all(self::v4allre, $t, $v4);	
	$ta = kwifs($v4, 1);
	if (is_array($ta)) $ret = $ta;
	return $ret;
}

// used by ntpc/php/s.php aka sntpw
public static function ipOrBlankStr(bool | string $ip, bool $orDie = false) : string {

	
	try {
		kwas($ip, 'ip is falsey'); kwas(is_string($ip), 'ip not string');
		$sl = strlen($ip); kwas($sl >= 3 && $sl <= 39, 'need an IP arg - 2');


		kwas(	 ($ip4m = preg_match(self::v41re, $ip))
			  || ($ip6m = preg_match(self::v61re, $ip))		, 'bad IP preg'	);

		if ($ip4m) {
			kwas($sl <= 15, 'ipv4 too big');
			$ip4 = ip2long($ip);
			kwas($ip4 && $ip4 > 0, 'ipv4 failed'); 
		}

		kwas(inet_pton($ip), 'inet_pton failed'); 
		
		return $ip;
		
	} catch (Exception $ex) { 
		if ($orDie) throw $ex;
	}
	
	return '';
}

}

function getValidIPOrFalsey(bool | string $ip, bool $orDie = false) : string {
	return ips_kw::ipOrBlankStr($ip, $orDie);
}