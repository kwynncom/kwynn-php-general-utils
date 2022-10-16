<?php

class kwynn_email_default extends kwynn_email {
	
	private function common() {	

		$this->omo->Host = 'email-smtp.us-east-1.amazonaws.com';
		
		$this->omo->SMTPDebug  = 0;  // 1 = errors and messages; 2 = messages only
		$this->omo->SMTPAuth   = true;                  
		$this->omo->SMTPSecure = 'tls';                 
		$this->omo->Port       = 587;  
		$this->omo->IsSMTP(); // telling the class to use SMTP

	}
	
	
	public function __construct() {
		parent::__construct();
		$this->omo = new PHPMailer\PHPMailer\PHPMailer();
		$this->personlDefaultFromDB();
		$this->common();
		
	}
	
	public function getMailO() { return $this->omo; } 
	
	public static function get() {
		$o = new self();
		return $o->getMailO();
	}
	
    private function personlDefaultFromDB() {
		
		if (!(isAWS() || ispkwd())) return;
		
		$dao = new kwynn_creds('creds');
		$c = $dao->getType('email'); kwas($c && is_array($c) && count($c) >0, 'bad creds array');
		$t = ['uname', 'pwd', 'from', 'from_name'];
		foreach($t as $i) {
			kwas(isset($c[$i]),"bad $i in kwynn email");
			$s = $c[$i];
			kwas(	 isset    ($s)  
				&& 	 is_string($s)
				&&   strlen(   $s) > 10, "bad $i in kwynn email");
		}

		$this->omo->Username = $c['uname'];
		$this->omo->Password = $c['pwd'];
		$this->omo->SetFrom( $c['from'], $c['from_name']);
		$this->omo->AddAddress($c['default_to'], $c['default_to_name']);
    }

	
}