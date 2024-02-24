<?php

function kwifsT($a, ...$ks) {
    return kwifsTCl::orig($a, $ks);

}

class kwifsTCl {

    const defaultKey = 'kwiff';
    const ddResult = false;

    private readonly mixed $defaultResult;

    private function setDefault(array &$a)  {
	if (!$a) return self::ddResult;
	$l = count($a);
	$e = $a[$l - 1];
	if (!is_array($e))	  return $this->sd20(self::ddResult, false, $a);
	$k = key($e);
	if (key($e) !== 'kwiffs') return $this->sd20(self::ddResult, false, $a);
	$this->sd20($e[$k], true, $a);
    }

    private function sd20(mixed $v, bool $wasSet, array $a)  {
	$this->defaultResult = $v;
	if (!$wasSet) return;
	unset($a[count($a) - 1]);
	
    }

    public static function orig($a, $ks) {
	$o = new self();
	return $o->origI($a, $ks);
    }	

    public function origI(mixed $entIN, array $ks) {

	$this->setDefault($ks);
	$n = count($ks);
	$t = $entIN; unset($entIN);
	for ($i=0; $i < $n; $i++) {
	    $tv = $this->checkP($t, $ks[$i]);
	}

	if (!isset($tv)) return $this->defaultResult;
	return $tv;
    }

    private function checkP(mixed $ent, mixed $key) : mixed {
	$ty = gettype($ent);
	if	($ty === 'array' ) $oraw = (object)$ent;
	else if ($ty === 'object') $oraw = $ent;
	else return $this->defaultResult;

	$o = new ReflectionObject($oraw);
	$p = $o->getProperty($key);
	if (!$p->isInitialized($oraw)) return $this->defaultResult;
	return $p->getValue($oraw);
	
	
	
    }
    
}


