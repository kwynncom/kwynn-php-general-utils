<?php // running at http://kwynn.com/t/20/06/machineID.php?testisAWS

require_once('/opt/kwynn/kwutils.php');

class kwisAWSCl {
    
    const iddir      = '/sys/devices/virtual/dmi/id/';
    const chv        = self::iddir . 'chassis_vendor';
    const iamfpre    = '/tmp/';
    const kwns       = '_ns_kwynn_com_2020_06';
    const iamawsf    = self::iamfpre . 'iamaws_____' . self::kwns;
    const iamNOTawsf = self::iamfpre . 'iam_NOT_aws' . self::kwns;
    const v          = 2;
    
    public static function is() {
	
	if (self::bymyfile   ())	   return true;
	if (self::bymynotfile() === false) return false; 
	
	
	$iamaws = self::is2();
	if ($iamaws) file_put_contents(self::iamawsf   , '');
	else         file_put_contents(self::iamNOTawsf, '');
	
	return $iamaws;
    }
    
    private static function is2() {
	if ( self::ApacheGetEnv())      return true;
	if ( self::ClientEnvironment()) return true;
	if (!self::BoardAssetTag())     return false;
	return self::ChassisVendor();
    }
    
    public static function ApacheGetEnv()      { return function_exists('apache_getenv') &&  apache_getenv('KWYNN_ID_201701') === 'aws-nano-1';   }
    public static function ClientEnvironment() { return						    getenv('KWYNN_ID_201701') === 'aws-nano-1'; }
    public static function BoardAssetTag()     { return file_exists(self::iddir . 'board_asset_tag');    }
    public static function ChassisVendor()     {
	if   (!file_exists      (self::chv)) return false;
	return trim(file_get_contents(self::chv)) === 'Amazon EC2';
    }
    
    public static function bymyfile   () { if (file_exists		      (self::iamawsf   )) return true; }
    public static function bymynotfile() { if (file_exists		      (self::iamNOTawsf)) return false;}
    
    public static function test() {
	
	global $argv;
	
	if (!iscli()) { if (!isset($_REQUEST['testisAWS'])) return; }
	else if (!(isset($argv[1]) && $argv[1] === 'testisAWS')) return;
	
	if (!iscli()) header('Content-Type: text/plain');
	
	$ms = get_class_methods(get_called_class());
	foreach ($ms as $i => $m) {
	    $nm = $ms[$i];
	    
	    if ($m === 'test') continue;
	    if ($m === 'fileInfo') continue;
	    
	    $ca = [get_called_class(), $m];
	    $res = $ca();
	    $d = 'inconclusive';
	    if ($res === true ) $d = 'T';
	    if ($res === false) $d = 'F';
	    echo $d . ' ' . $nm . "\n";
	}
	
	self::fileInfo();
    }
    
    public static function fileInfo() {
	$ts = filemtime(__FILE__);
	$hash = hash('sha256', file_get_contents(__FILE__));
	
	echo date('r', $ts) . " = this file mod time\n";
	echo $hash . " = this file hash\n";
	echo date('r') . " = as of\n";
	echo self::v . " = my probably temporary version number\n";
    }
}

function isAWS()   { return kwisAWSCl::is(); }
function isKwDev() { return !isAWS();        }

kwisAWSCl::test();
