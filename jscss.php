<?php

class jscssht {
	
	const jsDefault = '/opt/kwynn/js/utils.js';
	const fileTypes = ['css' => '/^.*\.css$/', 'js' => '/^.*\.js$/'];
	
	private function __construct(string $base = '') {
		$this->url =  $url = dirname($_SERVER['REQUEST_URI']);
		if (!$base) $base = $_SERVER['DOCUMENT_ROOT'] . $url;
		$this->base = $base;
	}
	
	
	public static function echoAll(string $base = '') {
		echo(self::getAll($base));
	}

	public static function getAll(string $base = '') {
		$o = new self($base);
		return $o->getAllI();
	}
	
	
	public function getAllI() {

		$ht = '';

		foreach(self::fileTypes as $ext => $re) 
		{
			$fs = [];
			if ($ext === 'js' && self::jsDefault && is_readable(self::jsDefault)) $fs[] = self::jsDefault;
			$fs = kwam($fs, self::recursiveSearch($this->base, $re ));
			foreach($fs as $f) $ht .= $this->get1HTI($f, $ext);
		}
		
		return $ht;
	}
	
	private function getExt($f) {
		foreach(self::fileTypes as $ext => $re) if (preg_match($re, $f)) return $ext;
	}
	
	public function get1HT($f) { return $this->get1HTI($f);	}
	
	private function get1HTI(string $f, string $ext = '') {
		
		if (!$ext) $ext = $this->getExt($f);
		
		$d = str_replace($this->base, $this->url, $f);
		if      ($ext === 'css') $t = "<link rel='stylesheet' href='$d' />\n";
		else if ($ext === 'js' ) $t = "<script src='$d'></script>\n"; 		
		return $t;
	}

	public static function recursiveSearch($dir, $re) {
		$return = [];
		$iti = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
		while($iti->valid()){
			$p = $iti->key();
			if (preg_match($re, $p, $ms)) {
				$return[] = $ms[0];
			}
			$iti->next();
		}
		return $return;
	}
}
