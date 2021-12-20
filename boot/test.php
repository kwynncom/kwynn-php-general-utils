<?php

require_once('mid.php');


for ($i=0; $i < 30; $i++) {
    $b = microtime(1);
    $mo = machine_id::get();
    $uboot = $mo['Uboot'];
    $e = microtime(1);
    echo(($e - $b) . "\n");
    if (isset($uboot)) echo($uboot . "\n");
}
