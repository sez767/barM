<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
$result = '{"success":false}';
$where1 = "";
if (!isset($_REQUEST['p2'])) {
    $where1 .= " AND fill_date > NOW() - interval 10 day ";
}
foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1': if (strlen($v)) {
                $where1 .= " AND country = '" . $v . "' ";
                $moneyWhere .= " AND country = '" . $v . "' ";
            } break;
        case 'p2': if (strlen($v)) {
                $where1 .= " AND fill_date > '" . $v . " 00:00:00' ";
                $moneyWhere .= " AND return_date > '" . $v . " 00:00:00' ";
            } break;
        case 'p3': if (strlen($v)) {
                $where1 .= " AND fill_date < '" . $v . " 23:59:59' ";
                $moneyWhere .= " AND return_date < '" . $v . " 23:59:59' ";
            } break;
        case 'p4': if (strlen($v)) {
                $where1 .= " AND offer = '" . $v . "' ";
                $moneyWhere .= " AND offer = '" . $v . "' ";
            } break;
        case 'p5': if (strlen($v)) {
                $where1 .= " AND staff_id = '" . $v . "' ";
                $moneyWhere .= " AND staff_id = '" . $v . "' ";
            } break;
    }
}
$query = "SELECT id, DATE_FORMAT(fill_date,'%Y-%m-%d') as times,
    SUM(price) as allMoney,
	SUM(pay_order) as allPay,
	SUM(IF(send_status='Оплачен',price,0)) as ourMoney
	FROM staff_order
            WHERE status = 'Подтвержден' " . $where1 . "
			GROUP BY DATE_FORMAT(fill_date,'%Y-%m-%d') ORDER BY DATE_FORMAT(fill_date,'%Y-%m-%d')";
//echo $query;
$rs = mysql_query($query);
$arr = array();
if (mysql_num_rows($rs)) {
    $i = 0;
    while ($obj = mysql_fetch_object($rs)) {
        //$arr[$i]['id'] = $i;
        $arr[$i]['time'] = $obj->times . ' 00:00:00';
        $arr[$i]['cancelAVG'] = round((int) $obj->ourMoney * -0.03);
        $arr[$i]['allAVG'] = (int) $obj->ourMoney;
        $arr[$i]['payAVG'] = (int) $obj->allPay * -1 * 5.52;
        $arr[$i]['siteAVG'] = $arr[$i]['allAVG'] + $arr[$i]['payAVG'] + $arr[$i]['cancelAVG'];
        $i++;
    }
    echo '{"total":"' . mysql_num_rows($rs) . '","data":' . json_encode($arr) . '}';
}
?>
