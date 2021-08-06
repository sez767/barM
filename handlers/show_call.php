<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");

mysql_query("SET SESSION group_concat_max_len = 1000000;");

if (($_GET['id'] = (int) $_GET['id'])) {
    $query = "
SELECT GROUP_CONCAT(CONCAT(callid, '^', date, '^', source, '^', sip_id, '^', system)) AS callsh
FROM call_staff_order
WHERE order_id = {$_GET['id']}
GROUP BY order_id
UNION
SELECT GROUP_CONCAT(CONCAT(callid, '^', date, '^', source, '^', sip_id, '^', system)) AS callsh
FROM call_staff_order_old
WHERE order_id = {$_GET['id']}
GROUP BY order_id";
//    echo $query . PHP_EOL;

    $orderData = DB::queryOneRow('SELECT * FROM staff_order WHERE id = %i', $_GET['id']);
    $clientId = DB::queryFirstField('SELECT uuid FROM staff_order WHERE uuid = %s', $orderData['uuid']);
//    print_r($clientData);die;

    ApiLogger::addLogVarExport($query);
    $tmp_call = array();

    $rs = mysql_query($query);
    $obj = mysql_fetch_object($rs);
    $pre_ar = explode(',', $obj->callsh);
    $allCallids = array();
    foreach ($pre_ar AS &$callItem) {
        $callItem = array(
            'raw' => $callItem,
            'exploded' => explode('^', $callItem)
        );
        $allCallids[] = $callItem['exploded'][0];
    }

    if ($allCallids) {
        asterisk_base();
        $completeData = DB::queryAssData('callid', 'event', "SELECT * FROM asterisk.queue_log WHERE callid IN %ls AND `event` IN ('COMPLETEAGENT' , 'COMPLETECALLER')", $allCallids);
        $durationData = DB::queryAssData('uniqueid', 'duration', "SELECT * FROM asterisk.cdr WHERE uniqueid IN %ls", $allCallids);
        bari_base();
    }
    $currDate = false;
    foreach ($pre_ar AS $calls) {
        $pr_call = $calls['exploded'];

        $newDate = date('Y-m-d', strtotime($pr_call[1]));
        $add = '';
        if ($newDate != $currDate) {
            $currDate = $newDate;
            $add = '<div style="color:crimson; margin: 5px 0px 0px 0px; font-weight: bold; border-bottom-style: groove;">' . $currDate . '</div>';
        }

        ApiLogger::addLogVarExport('$pr_call');
        ApiLogger::addLogVarExport($pr_call);

        if ((int) $pr_call[4]) {
            if ($pr_call[4] == 2) {
                $url = 'http://45.8.116.20/call.php?file=' . (empty($pr_call[2]) ? 'modems_all' : $pr_call[2]) . '/' . date("Ymd", strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
            } else if ($pr_call[2] == 'toabonentkgz' || $pr_call[2] == 'tocourierkgz') {
                $url = 'http://176.126.167.66/call.php?file=modems_all/' . date("Ymd", strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
            } else {
                $url = "http://call.baribarda.com/call.php?file=modems_all/" . date('Ymd', strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
            }
//            $headers = get_headers($url);
//            $headers = json_encode($headers);
//            $pre_he = explode("Content-Length: ", $headers);
//            $pre_h = explode('"', $pre_he[1]);


            if ($orderData['country'] == 'uz') {
                $url = 'http://80.80.218.161/call.php?file=/' . date('Ymd', strtotime($pr_call[1])) . '/' . $pr_call[0];
                $tmp_call[] = $add . '<a target="_blank" href="' . $url . '">' . $pr_call[1] . ' ' . $pr_call[2] . ' | ' . $pr_call[3] . ' - ' . (empty($durationData[$pr_call[0]]) ? '0' : $durationData[$pr_call[0]]) . '  &#9742</a>' . (empty($completeData[$pr_call[0]]) ? '' : ($completeData[$pr_call[0]] == 'COMPLETEAGENT' ? ' Оператор завершил' : ' Клиент завершил'));
            } else {
                $tmp_call[] = $add . '<a target="_blank" href="' . $url . '">' . $pr_call[1] . ' ' . $pr_call[2] . ' | ' . $pr_call[3] . ' - ' . (empty($durationData[$pr_call[0]]) ? '0' : $durationData[$pr_call[0]]) . '  &#9742</a>' . (empty($completeData[$pr_call[0]]) ? '' : ($completeData[$pr_call[0]] == 'COMPLETEAGENT' ? ' Оператор завершил' : ' Клиент завершил'));
            }
        } else {
            if (strlen($pr_call[2]) > 3 && $pr_call[2] != 'tocourier' && $pr_call[2] != 'toabonent' && $pr_call[2] != 'toabonentkgz' && $pr_call[2] != 'tocourierkgz') {
                $url = 'http://call.baribarda.com/call.php?file=' . $pr_call[2] . '/' . date('Ymd', strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
//                $headers = get_headers($url);
//                $headers = json_encode($headers);
//                $pre_he = explode("Content-Length: ", $headers);
//                $pre_h = explode('"', $pre_he[1]);
                $tmp_call[] = $add . '<a target="_blank" href="' . $url . '">' . $pr_call[1] . ' | ' . $pr_call[2] . ' | ' . $pr_call[3] . ' - ' . (empty($durationData[$pr_call[0]]) ? '0' : $durationData[$pr_call[0]]) . '  &#9742</a>' . (empty($completeData[$pr_call[0]]) ? '' : ($completeData[$pr_call[0]] == 'COMPLETEAGENT' ? ' Оператор завершил' : ' Клиент завершил'));
            } else {
                if ($pr_call[2] == 'toabonentkgz' || $pr_call[2] == 'tocourierkgz') {
                    $url = 'http://176.126.167.66/call.php?file=modems_all/' . date("Ymd", strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
                } else {
                    $url = 'http://call.baribarda.com/call.php?file=modems_all/' . date('Ymd', strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
                }
//                $headers = get_headers($url);
//                $headers = json_encode($headers);
//                $pre_he = explode("Content-Length: ", $headers);
//                $pre_h = explode('"', $pre_he[1]);

                if ($pre_h[0] < 1) {
                    $url = 'http://call.baribarda.com/call.php?file=modems_all/' . date('Ymd', strtotime($pr_call[1])) . '/' . $pr_call[0] . '&type=2';
//                    $headers = get_headers($url);
//                    $headers = json_encode($headers);
//                    $pre_he = explode("Content-Length: ", $headers);
//                    $pre_h = explode('"', $pre_he[1]);
                }

                $tmp_call[] = $add . '<a target="_blank" href="' . $url . '">' . $pr_call[1] . ' | ' . $pr_call[2] . ' ' . $pr_call[3] . ' - ' . (empty($durationData[$pr_call[0]]) ? '0' : $durationData[$pr_call[0]]) . '  &#9742</a>' . (empty($completeData[$pr_call[0]]) ? '' : ($completeData[$pr_call[0]] == 'COMPLETEAGENT' ? ' Оператор завершил' : ' Клиент завершил'));
            }
        }
    }

    $ret = implode("<br>", $tmp_call);

    if (!$only_calls) {
        if (($hData = DB::query("SELECT * FROM coffee.ActionHistory WHERE property = 'status_cur' AND date > CURDATE() AND `to` = %i", $_GET['id']))) {
            $table = '<p style="font-weight: bold;margin: 5px;color:chocolate;">История заказа по статусу курьера</p>
                            <table border="1">
                                <tr style="font-weight: bold;text-align:center;">
                                    <td width="120">Дата</td>
                                    <td width="120">Кто</td>
                                    <td width="120">Было</td>
                                    <td width="120">Стало</td>
                                </tr>';
            foreach ($hData as $hItem) {
                $table .= "<tr>
                            <td width='120'>{$hItem['date']}</td>
                            <td width='120'>" . (array_key_exists($hItem['from'], $GLOBAL_KZ_COURIERS) ? $GLOBAL_KZ_COURIERS[$hItem['from']]['name'] : $GLOBAL_STAFF_FIO[$hItem['from']]) . "</td>
                            <td width='120'>{$hItem['was']}</td>
                            <td width='120'>{$hItem['set']}</td>
                           </tr>";
            }
            $table .= '</table>';
        } else {
            $table = '<p style="font-weight: bold;margin: 5px;color:chocolate;">Нет статусов</p>';
        }
    }


    $other = '';
    if (($otherData = DB::query('SELECT * FROM staff_order WHERE uuid = %s AND id != %i', $clientId, $orderData['uuid']))) {
//        print_r($otherData);
//        die('lll');
        $other = '<p style="font-weight: bold;margin: 5px;color:chocolate;">Другие заказы клиента</p>';

        foreach ($otherData as $otherItem) {
//            $other .= '<br/>' . $otherItem['id'];
            $other .= '<br/><span id="call_' . $otherItem['id'] . '"><a href="#" onclick="showRingsGrid(\'' . $otherItem['id'] . '\',\'' . $otherItem['country'] . '\'); return false;">' . $otherItem['id'] . ' Показать</a></span>';
        }
//        die('ooo');
    }

//    $other = '';

    echo $ret . $table . $other;
} else {
    echo '{"success":false}';
}
