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
		$svs = sem_get($key);  kwas($svs, 'bad sem_get - sem_lock');
		$this->key = $key; unset($key);
		$this->svs = $svs; unset($svs); 
	}
	public function __destruct() { if (isset($this->svs)) kwas(sem_remove($this->svs), 'sem_rm fail - sem_lock'); }
    public function   lock(bool $nb = false)	 { kwas(sem_acquire($this->svs, $nb), 'sem_acq failed - sem_lock'); }
    public function unlock()	 { 
		if (!isset($this->svs) || !$this->svs) return;
		try { 
			restore_error_handler();
			$r = sem_release($this->svs);
			set_error_handler('kw_error_handler');
			return $r; 
		} catch(Exception $ex) {} 
		
	}
    public function getKey() { return $this->key; }
}
