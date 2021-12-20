<?php
require_once('/opt/kwynn/kwutils.php');
require_once('aws.php');

class mid_creation_date {
    public static function get($isAWS) {
	if ($isAWS) return AWS_instance_identity::getCreationDate();
	return self::getLocal();
    }
    
    private static function getLocal() {
	$cmd = 'ls -1tr / | head -n 1';
	$f   = trim(shell_exec($cmd));
	return filemtime('/' . $f);
    }
    
    public static function test() {
	echo(self::get(isAWS()));
    }
}

if (didCLICallMe(__FILE__)) mid_creation_date::test();