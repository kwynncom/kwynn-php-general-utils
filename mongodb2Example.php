<?php

require_once('/opt/kwynn/kwutils.php');
require_once('/opt/kwynn/mongodb2.php');

class dao_generic_2_example extends dao_generic_2 {
    const dbName = 'example_kw_dao_2';

    public function __construct() {
	parent::__construct(self::dbName, __FILE__);
	$this->creTabs(['e' => 'example']);
	$testInsertDat = $this->ecoll->getSeq2(true, false, true);
	$this->ecoll->insertOne($testInsertDat);
    }
}

new dao_generic_2_example();