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
		$svs = sem_get($key, 1, 0600);  kwas($svs, 'bad sem_get - sem_lock'); 
		$this->key = $key; unset($key);
		$this->svs = $svs; unset($svs); 
	}
	public function __destruct() { if (isset($this->svs)) sem_remove($this->svs); }
    public function   lock()	 { kwas(sem_acquire($this->svs), 'sem_acq failed - sem_lock'); }
    public function unlock()	 { kwas(sem_release($this->svs), 'sem_rel failed - sem_lock'); }
    public function getKey() { return $this->key; }
}
