<?php   // This is NOT included by default with the rest of the library.

require_once('/opt/kwynn/kwutils.php'); // Thus I have to include the rest of the library.

class isKwGooCl extends dao_generic_3 {
	
	const dbname = 'qemail'; // using the same as my GMail unread count checker
	const dbcoll = 'sessions'; // same
	const emfs = ['/var/kwynn/myemail.txt', '/var/kwynn/kwEmail_1_2007.txt'];
	const isKwGooTrueRes = 'YouAreKwGoo_2022_start!!!';

	private function __construct() {
		$this->theores = false;
		try {
			parent::__construct(self::dbname);
			$this->creTabs	   (self::dbcoll);
			$this->loadMatchingEmail();
			$this->tryMatch();
		} catch(Exception $ex) { }
	}
	
	private function loadMatchingEmail() {
		foreach(self::emfs as $f) {
			try {
				kwas(is_readable($f), "$f not readable isKwGoo email files");
				$t = strtolower(trim(file_get_contents($f))); kwas($t && is_string($t), 'no valid string isKwGoo ef 2');
				$this->myemail = $t;
				return;
			} catch (Exception $ex) { }
		}
		
		kwas(false, 'no email to try to match found isKwGoo');
	}
	
	private function tryMatch() {
		try {
			$sid = startSSLSession();
			$hsid = hash('sha256', $sid);
			$emh  = hash('sha256', $this->myemail);
			$cnt = $this->scoll->count(['sid' => $hsid, 'addr' => $emh]);
			kwas($cnt >= 1, 'iskwgoo count fail');
			$this->theores = self::isKwGooTrueRes;
		} catch(Exception $ex) { }	
	}
	
	public function isKwGooRes() { return $this->theores; }
	
	public static function isKwGoo() {

		$o = new self();
		return $o->isKwGooRes();

	} // func
} // class

function isKwGoo() { return isKwGooCl::isKwGoo() === isKwGooCl::isKwGooTrueRes; }

function kwGooOrDie() { kwas(isKwGoo(), 'not auth - iskgoo 0334'); }
