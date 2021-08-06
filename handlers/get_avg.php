<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';


$and = '0 = 0';

if ((int) $_GET['id']) {
    $and .= " AND staff_id = '" . (int) $_GET['id'] . "' ";
    if (empty($_SESSION['admin'])) {
        $and .= " AND web_id = {$_SESSION['Logged_StaffId']} ";
    }
} else {
    if (empty($_SESSION['admin'])) {
        $and .= " AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' ";
    }
}

$query = "SELECT id, DATE_FORMAT(date,'%Y-%m') as times,
	COUNT(*) as count, SUM(price) as prs, MAX(price) as prsm, offer
            FROM staff_order
            WHERE " . $and . " AND status = 'Подтвержден'
			AND fill_date > '" . date('Y-m-d') . " 00:00:00'
            GROUP BY DATE_FORMAT(fill_date,'%Y-%m'), offer";

//echo $query;
$rs = mysql_query($query);
$arr = array();
$sum_arr = array();
if (mysql_num_rows($rs)) {
    $html_str = '';
    while ($obj = mysql_fetch_object($rs)) {
        $arr[$obj->offer][$obj->times] = round($obj->prs / $obj->count);
        @$sum_arr['count'] += $obj->count;
        @$sum_arr['price'] += $obj->prs;
        if ((int) @$sum_arr['pricem'] < $obj->prsm)
            @$sum_arr['pricem'] = $obj->prsm;
    }
    ksort($arr);
    foreach ($arr as $k => $v) {
        ksort($v);
        foreach ($v as $sk => $sv) {
            $html_str .= ' <b>' . $k . '</b> - ' . $v[$sk] . ' тг.<br>';
            $sum_arr[] = $v[$sk];
        }
    }
    $html_str .= '<br><b>Итого : ' . round($sum_arr['price'] / $sum_arr['count']);
    $html_str .= '<br><b>Итого max : ' . round($sum_arr['pricem']);
    echo '<div style="text-align:center;">' . $html_str . '</div>';
}
?>
