<?php   // This is NOT included by default with the rest of the library.

require_once('/opt/kwynn/kwutils.php'); // Thus I have to include the rest of the library.
require_once('/var/kwynn/gooauth/qemail/' . /* beware double // */ 'usageLimit/usageLimitDao.php');

class isKwGooCl {

	public readonly string $theores; // public mostly because of kwifs usage
	public readonly string $kwemailHash; 
	
	const emfs = ['/var/kwynn/gooauth/kwem2007h1.txt'];
	const isKwGooTrueRes = 'YouAreKwGoo_2022_start!!!';

	private function __construct() {
		try {
			if (PHP_SAPI === 'cli') {
				$this->theores = self::isKwGooTrueRes;
				return;
			}
			$this->loadMatchingEmail();
			$this->tryMatch();
		} catch(Exception $ex) { }
	}
	
	private function loadMatchingEmail() {
		foreach(self::emfs as $f) {
			try {
				kwas(is_readable($f), "$f not readable isKwGoo email files");
				$t = trim(file_get_contents($f)); kwas($t && is_string($t), 'no valid string isKwGoo ef 2');
				$this->kwemailHash = $t;
				return;
			} catch (Exception $ex) { }
		}
		
		kwas(false, 'no email to try to match found isKwGoo');
	}
	
	private function tryMatch() {
		try {
			if (daoUsage::hasEmailSid(kwifs($this, 'kwemailHash', ['kwiff' => '']))) {
				$this->theores = self::isKwGooTrueRes;
			}
		} catch(Exception $ex) { }	
	}
	
	private function isKwGooRes() : string { 
		$raw = kwifs($this, 'theores', ['kwiff' => '']);
		return $raw;
	}
	
	public static function isKwGoo() : bool {
		$o = new self();
		$raw = $o->isKwGooRes();
		return $raw === self::isKwGooTrueRes;

	} // func
} // class

function isKwGoo() { return isKwGooCl::isKwGoo(); }

function kwGooOrDie() { kwas(isKwGoo(), 'not auth - iskgoo 0334'); }
