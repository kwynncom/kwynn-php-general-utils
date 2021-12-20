<?php

require_once('/opt/kwynn/kwutils.php');

class AWS_instance_identity {
    
    const url = 'http://169.254.169.254/latest/dynamic/instance-identity/document';
    
    public static function get($stdout = false) {
	$j = file_get_contents(self::url);
	$a = json_decode($j, 1);
	if ($stdout) var_dump($a);
	return $a;
    }
    
    public static function getCreationDate() {
	$a = self::get();
	return strtotime($a['pendingTime']);
    }
    
    public static function test() {
	$fs = ['get', 'getCreationDate'];
	foreach($fs as $f) {
	    $r = call_user_func(['self', $f]);
	    var_dump($r);
	}
    }
}

if (didCLICallMe(__FILE__)) AWS_instance_identity::test();
