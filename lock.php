<?php

require_once('/opt/kwynn/kwutils.php');

class sem_lock {
    
	const minFileLen = 1;
	
    private $svs;
    private $key;
    
    public function __construct($path, $projectID = 'a') {
		$p = $path;
		kwas($p && is_string($p) && strlen(trim($p)) > self::minFileLen, 'bad path for sem_lock' ); unset($p);
		$key = ftok($path, $projectID); kwas($key !== -1, 'ftok failed - sem_lock');
		$svs = sem_get($key, 1, 0666);  kwas($svs, 'bad sem_get - sem_lock'); // note below
		$this->key = $key; unset($key);
		$this->svs = $svs; unset($svs); 
	}
	public function __destruct() { if (isset($this->svs)) kwas(sem_remove($this->svs), 'sem_rm fail - sem_lock'); }
    public function   lock()	 { kwas(sem_acquire($this->svs), 'sem_acq failed - sem_lock'); }
    public function unlock()	 { 
		if (!isset($this->svs) || !$this_svs) return;
		kwas($r = sem_release($this->svs), 'sem_rel failed - sem_lock'); 
		$this->svs = false;
		return $r; 
		
	}
    public function getKey() { return $this->key; }
}

/* Regarding permissions, one could spend a long time resolving a conflict between CLI and the www-data user / group.  
 * Versus that, the potential problem with giving world / other permission seems very theoretical. */