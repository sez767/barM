<?php

session_start();

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
require_once dirname(__FILE__) . '/../lib/db.php';

$redis = RedisManager::getInstance()->getRedis();

if (!isset($GLOBAL_SIP_STAFF)) {
    initGlobalVars();
}

//var_dump($_GET);
if ($_GET['inOut'] == "false") {
    $rez_red = $redis->get('MONITOR_CHANNELS');
    $queue_ar = $redis->get('MONITOR_CHANNELS_OUT');
    $rez_red1 = $redis->get('MONITOR_QUE');
    $ar1 = unserialize($rez_red1);
    $ar = unserialize($rez_red);
    $queue_ar = unserialize($queue_ar);
    if (count($ar)) {
        $queue_ar = array_merge($queue_ar, $ar);
    }
    $zLeft = 0;
    $zRight = 0;
    $agent_ar = $ar1;
    ksort($agent_ar);
    $leftStore = array();
    $co_temp = 0;
    $co_hold_temp = 0;
    $temp_str = '';

    foreach ($queue_ar as $k => $v) {
        if (empty($queue_ar[$k]['holdtime'])) {
            $queue_ar[$k]['holdtime'] = 0;
        }

        $temp_str = $temp_str . '<row><a>' . $queue_ar[$k]['queue'] . '</a><b>' . (($queue_ar[$k]['caller']) ? $queue_ar[$k]['caller'] : '-') . '</b><c>' . (($queue_ar[$k]['holdtime']) ? $queue_ar[$k]['holdtime'] : '-') . '</c><d>' . (($queue_ar[$k]['time']) ? $queue_ar[$k]['time'] : '-') . '</d><e>' . (($queue_ar[$k]['agent']) ? $queue_ar[$k]['agent'] : '-') . '</e></row>';

        $returnArrayLeft[$zLeft]['que'] = $queue_ar[$k]['queue'];
        $returnArrayLeft[$zLeft]['caller'] = (($queue_ar[$k]['caller']) ? $queue_ar[$k]['caller'] : '-');
        $returnArrayLeft[$zLeft]['waiting'] = (($queue_ar[$k]['holdtime']) ? $queue_ar[$k]['holdtime'] : '-');
        $returnArrayLeft[$zLeft]['time'] = (($queue_ar[$k]['time']) ? $queue_ar[$k]['time'] : '-');
        $returnArrayLeft[$zLeft]['agent'] = (($queue_ar[$k]['agent']) ? $queue_ar[$k]['agent'] : '-');

        if ($queue_ar[$k]['holdtime']) {
            $co_hold_temp++;
        }
        $co_temp++;
        $zLeft++;
    }

    foreach ($returnArrayLeft as $key => $value) {
        $leftStore[] = $value;
    }

    foreach ($agent_ar as $k => $v) {
        $queues = '';
        foreach ($agent_ar[$k]['queues'] as $k2 => $v2) {
            $queues .= ($queues ? ", " : '') . $v2;
        }

        if ($queues) {
            $agent_sip = $k;
            $sipArr = explode('/', $agent_sip);
            $agServerClear = empty($sipArr[0]) ? '' : $sipArr[0];
            $agSipClear = 'SIP/' . (empty($sipArr[1]) ? '' : $sipArr[1]);
        }

        $staffId = $GLOBAL_SIP_STAFF[$agSipClear];

        if (!empty($GLOBAL_STAFF_RESPONSIBLE[$staffId])) {
            $returnArrayRight[$zRight]['responsible'] = $GLOBAL_STAFF_RESPONSIBLE[$staffId];
        }

        $returnArrayRight[$zRight]['agent'] = $agent_sip . (empty($GLOBAL_STAFF_FIO[$staffId]) ? '|' : ' ' . $GLOBAL_STAFF_FIO[$staffId]);
        $returnArrayRight[$zRight]['que'] = $queues;
        $returnArrayRight[$zRight]['penalty'] = '1';
        $returnArrayRight[$zRight]['status'] = $agent_ar[$k]['status'];
        $returnArrayRight[$zRight]['server'] = $agServerClear;

        $zRight++;
    }

    $rightStore = array();
    foreach ($returnArrayRight as $key => $value) {
        $rightStore[] = $value;
    }

    if (!count($leftStore)) {
        $leftStore = array(0 => array('que' => '', 'caller' => '', 'waiting' => '', 'time' => '', 'agent' => ''));
        $co_hold_temp = 0;
        $co_temp = 0;
    }
    $dongle_ar = $redis->get('MONITOR_DONGLE');
    $dongle_ar = unserialize($dongle_ar);
    $d_ar = array();
    if (isset($dongle_ar['dongle'])) {
        foreach ($dongle_ar['dongle'] as $dk => $dv) {
            $d_ar['all'][] = array('device' => $dv[0], 'status' => $dv[1], 'number' => $dv[2]);
            if ($dv[1] == 'Dialing') {
                $d_ar['dial'][] = 1;
            }
        }
    }
    $predStr = $redis->get('PREDICTIVE');

    $predArr = array_chunk(array_diff(explode('<br>', $predStr), array('')), 3);

    foreach ($predArr as &$predValue) {
        $predValue = str_replace(' ', '&nbsp;', implode(' | ', $predValue));
    }

    $d_ar['all'] = empty($d_ar['all']) ? array() : $d_ar['all'];
    $d_ar['dial'] = empty($d_ar['dial']) ? array() : $d_ar['dial'];
    $ret = array(
        'modems' => 'Всего - ' . count($d_ar['all']) . ' / Вызывают - ' . count($d_ar['dial']),
        'pred' => implode('<br/>', $predArr),
        'leftStore' => $leftStore,
        'rightStore' => $rightStore,
        'dongleStore' => empty($d_ar['all']) ? array() : $d_ar['all'],
        'sipsCount' => 0,
        'unavailable' => 0,
        'inuse' => 0,
        'busy' => 0,
        'notinuse' => 0,
        'ringing' => 0,
        'waiting' => $co_hold_temp,
        'taking' => $co_temp
    );

    echo json_encode($ret);
}
