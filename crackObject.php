<?php
class crackObject {
	public function __construct($oin) {
		$this->oin = (array)$oin;
		$this->init10($oin);
	
	}
	
	public function getp(...$pp) {
		for ($i=0; $i < count($pp); $i++) {
			$ta = array_slice($i, 0, 1);
			$t = kwifs($this->oin, $ta);
			// $this->
			
		}
	}
	
	private function init10($oin) {
		new ReflectionClass($oin);
		$this->o = new stdClass();

		

	}
	
	private function get20($pp) {
		$propertyLength = strlen($pp);
		foreach ($this->oin as $key => $value) {
			if (substr($key, -$propertyLength) === $pp) {
				return $value;
			}
		}
	} // https://www.lambda-out-loud.com/posts/accessing-private-properties-php/

}