<?php

require_once('/opt/kwynn/kwutils.php');

if (didCLICallMe(__FILE__)) {

    $a = getValidIPOrFalsey('2001:4860:4806:c::');
    kwnull();

}
