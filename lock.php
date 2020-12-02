<?php

class sem_lock {
    
    private $svs;
    private $key;
    
    public function __construct($path, $projectID = 'a') {
	$key = ftok($path, $projectID); kwas($key !== -1, 'ftok failed - sem_lock');
	$svs = sem_get($key, 1, 0600);  kwas($svs, 'bad sem_get - sem_lock'); 
	$this->key = $key; unset($key);
	$this->svs = $svs; unset($svs); }
    public function   lock() { 
	kwas(sem_acquire($this->svs), 'sem_acq failed - sem_lock'); }
    public function unlock() { 
	kwas(sem_release($this->svs), 'sem_rel failed - sem_lock'); }
    public function __destruct() {
	kwas(sem_remove($this->svs) , 'sem_rem failed - sem_lock'); 
    }
    
    public function getKey() { return $this->key; }
	
    
}
