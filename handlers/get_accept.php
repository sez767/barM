<?php

header('Content-Type: text/html; charset=utf-8', true);

session_start();
//echo date('Y-m-d H:i:s');
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$staffId = (int) $_REQUEST['staff_id'];

if ($staffId) {
    if (empty($_SESSION['admin'])) {
        $and = " staff_id = $staffId AND web_id = {$_SESSION['Logged_StaffId']} AND `date` > CURDATE() ";
        $uslLogist = " AND staff_id = $staffId ";
    } else {
        $and = " staff_id = $staffId AND `date` > CURDATE() ";
        $uslLogist = " AND staff_id = $staffId ";
    }
} else {
    if (empty($_SESSION['admin'])) {
        $and = " last_edit = '{$_SESSION['Logged_StaffId']}' ";
        $uslLogist = " AND last_edit_kz = '{$_SESSION['Logged_StaffId']}' ";
    } else {
        $and = '0 = 0';
        $uslLogist = ' ';
    }
}
//if((int)$_GET['id']) $and = " staff_id = '".(int)$_GET['id']."' ";
$query = "SELECT    id,
                    DATE_FORMAT(fill_date,'%Y-%m-%d') AS times,
                    COUNT(*) AS count,
                    SUM(IF(status = 'Подтвержден' OR status = 'Предварительно подтвержден', 1, 0)) AS podtv, offer,
                    SUM(IF(status = 'новая', 1, 0)) AS not_obr
            FROM staff_order
            WHERE $and AND status <> '' AND fill_date > '" . date('Y-m-d') . "'
            GROUP BY DATE_FORMAT(fill_date, '%Y-%m-%d'), offer";
//echo $query;
$rs = mysql_query($query);
$arr = array();
$sum_arr = 0;
$html_str = '';
if (mysql_num_rows($rs)) {

    while ($obj = mysql_fetch_object($rs)) {
        $arr[$obj->offer][$obj->times] = $obj->podtv . ' (' . round(($obj->podtv / ($obj->count - $obj->not_obr)) * 100) . '%) ' . $obj->not_obr;
    }
    ksort($arr);
    foreach ($arr as $k => $v) {
        krsort($v);
        foreach ($v as $sk => $sv) {
            $html_str .= ' <b>' . $k . '</b> - ' . $v[$sk] . ' шт.<br>';
            $sum_arr += $v[$sk];
        }
    }
    $html_str .= '<br><b>Итого : ' . $sum_arr . ' шт.';
}

$queryLogist = "SELECT SUM(if(`status_kz` IN ('На доставку', 'Вручить подарок') $uslLogist, 1, 0)) AS dost
            FROM staff_order
            WHERE  status <> '' AND fill_date_log > '" . date('Y-m-d') . " 00:00:00' AND fill_date_log < '" . date('Y-m-d') . " 23:59:59' ";
$dost = DB::queryFirstField($queryLogist);

$redis = RedisManager::getInstance()->getRedis();

$pred_str = $redis->get('PREDICTIVE');

echo '<div style="text-align:center;">' . $html_str . '<br>К-во логистика - ' . $dost . '<br><br>' . $pred_str . '</div>';
