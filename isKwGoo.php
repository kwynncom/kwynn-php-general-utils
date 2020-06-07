<?php 

require_once('/opt/kwynn/kwutils.php');

function isGoo2($rin) {
    if (!$rin) return false;
    $fs = [  'type' => 'Kwynn_own_addr_hash',
	     'rand' => 'KxkhCOkkq0RaE5BNBZvjyvSPI',
	     'myr'  => '2019-09-07 21:17'    ];

    foreach ($fs as $f => $v) {
	if (!isset($rin[$f]))       return false;
	if (       $rin[$f] !== $v) return false;
    }
    
    return TRUE;
}

function isKwGoo() {
try {
    $sid = startSSLSession(); // Kwynn 2020/06/06 10:42pm
    $hsid = hash('sha256', $sid);
    $dao = new kwmoncli();
    $scoll = $dao->selectCollection('qemail', 'sessions');
    $res = $scoll->findOne(['sid' => $hsid]);
    kwas(isset($res['addr']) && $res['addr'], 'no valid addr hash');
    $hash = hash('sha256', $res['addr']);
    $ccoll = $dao->selectCollection('creds', 'creds');
    $res = $ccoll->findOne(['addr_hash' => $hash]);
    $bigres = isGoo2($res);
    if ($bigres) {
	file_put_contents('/tmp/iskgoo.txt', date('r') . "\n", FILE_APPEND);
	return TRUE;
    }
} catch(Exception $ex) { }

return false;

}

/* HISTORY
 * // Kwynn 2020/06/06 10:42pm - don't require sessions.php and use newer startSSLSession()
 *  // 09/07 9:19pm - getting rid of open hash
// 09/03 12:33AM added file_put 
// 08/30 3:23pm - safer hash of hash
// 2019/08/26 4:22pm - probably moving to /opt/kwynn
 * 
 */