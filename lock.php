<?php

require_once('/opt/kwynn/kwutils.php');

class sem_lock {
    
	const minFileLen = 1;
	const pbase = '/var/kwynn/lockfiles/lock_kw_';
	private readonly mixed $osre;
		
    public function __construct(string $pathIN, string $projectID = 'a') {
		$p = $pathIN;
		kwas($p && is_string($p) && strlen(trim($p)) > self::minFileLen, 'bad path for sem_lock' );
		$p = str_replace('/', '_', $p);
		$p .= ($projectID ? '_' . $projectID : '');
		$b = self::pbase;
		$p = $b . $p;
		
		if (!file_exists($p)) kwas(kwtouch($p, '', 0660), 'mkfifo failed - sem_lock');
		
		$re = fopen($p, 'w'); kwas($re, 'file open failed - kw lock');
		$this->osre = $re;
	}
		
	public function __destruct() {
		if (!fstat($this->osre)) return;
		$clr = fclose($this->osre);
		return;
	}
	
    public function   lock(bool $nonBlocking = false) : bool {
		$fl = LOCK_EX | ($nonBlocking ? LOCK_NB : 0);
		$r = flock($this->osre, $fl, $wouldBlock);
		if ($nonBlocking) return $wouldBlock === 1;
		kwas($r, 'lock failed - kw lock');
		return $r;
	}
	
    public function unlock() : bool	| null { 
		if (!fstat($this->osre)) return null;
		return flock($this->osre, LOCK_UN);
	}
}
