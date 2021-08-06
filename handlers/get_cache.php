<?php

session_start();
$ukr_ar = array('17729178', '26823714', '37239410', '30224289', '30356360', '76488859', '79319411', '47671128', '72480483', '80911164', '21630264', '44440873', '62443980', '77767205', '10578031');
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$usl = '';
$usl2 = '';
$redis = RedisManager::getInstance()->getRedis();

$Percents = $redis->hGetAll('OperPays');
$Stats = $redis->hGetAll('OperStat');

$PercentsKG = $redis->hGetAll('OperPaysKG');
$StatsKG = $redis->hGetAll('OperStatKG');

$AllPercent = $redis->hGetAll('AVGP');

$PercentAKz = round($AllPercent[0] * 100);
$PercentAKg = round($AllPercent[1] * 100);

//if ((int) $_SESSION['Logged_StaffId'] != 66629642 AND (int) $_SESSION['Logged_StaffId'] != 77777777) {
if (1) {
    $umova = "  IF(send_status IN ('Оплачен') AND kz_delivery <>'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ( package > 1 OR  LENGTH(dop_tovar) > 10), total_price*0.036,if(send_status IN ('Оплачен'),total_price*0.005,0)) AS bablo,
                IF(send_status IN ('Оплачен') AND kz_delivery <>'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ((package > 1 AND LENGTH(dop_tovar) > 10) OR package>2),total_price*if(country='kzg',0.054,0.036),0) AS upbablo,
                IF(send_status IN ('Оплачен') AND kz_delivery <>'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ((package = 0 AND LENGTH(dop_tovar) > 10) OR (package=1 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.0105,0.007),0) AS onebablo,
                IF(send_status IN ('Оплачен') AND kz_delivery <>'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ((package = 1 AND LENGTH(dop_tovar) > 10) OR (package=2 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.042,0.028),0) AS twobablo,
                IF(send_status IN ('Оплачен') AND kz_delivery = 'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ( package > 1 OR  LENGTH(dop_tovar) > 10), total_price*0.036,if(send_status IN ('Оплачен'),total_price*0.005,0)) AS bablop,
                IF(send_status IN ('Оплачен') AND kz_delivery = 'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ((package > 1 AND LENGTH(dop_tovar) > 10) OR package>2),total_price*if(country='kzg',0.054,0.036),0) AS upbablop,
                IF(send_status IN ('Оплачен') AND kz_delivery = 'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ((package = 0 AND LENGTH(dop_tovar) > 10) OR (package=1 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.0105,0.007),0) AS onebablop,
                IF(send_status IN ('Оплачен') AND kz_delivery = 'Почта' AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND ((package = 1 AND LENGTH(dop_tovar) > 10) OR (package=2 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.042,0.028),0) AS twobablop,
    if(send_status IN ('Оплачен') AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND (total_price<=9999),total_price * 0.01,
        if(send_status IN ('Оплачен') AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND total_price>9999 AND total_price<=19999,total_price * 0.03,
            if(send_status IN ('Оплачен') AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND total_price>19999 AND total_price<=27999,total_price * 0.06,
                if(send_status IN ('Оплачен') AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' AND total_price>27999,total_price * 0.09,0)))) AS s_bablo,
		";
    $usl2 = " AND last_edit_kz = '" . (int) $_SESSION['Logged_StaffId'] . "' ";
    $and = " (last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' OR last_edit_kz = '" . (int) $_SESSION['Logged_StaffId'] . "')";
    $usl = " AND last_edit = '" . (int) $_SESSION['Logged_StaffId'] . "' ";
    if (isset($Percents[$_SESSION['Logged_StaffId']])) {
        $Percent = round(($Percents[$_SESSION['Logged_StaffId']]) * 100);
    } else {
        $Percent = $PercentAKz;
    }

    if (isset($PercentsKG[$_SESSION['Logged_StaffId']])) {
        $PercentKG = round(($PercentsKG[$_SESSION['Logged_StaffId']]) * 100);
    } else {
        $PercentKG = $PercentAKg;
    }

    if (isset($Stats[$_SESSION['Logged_StaffId']])) {
        $Stat = $Stats[$_SESSION['Logged_StaffId']];
    } else {
        $Stat = 1000;
    }

    if (isset($StatsKG[$_SESSION['Logged_StaffId']])) {
        $StatKG = $StatsKG[$_SESSION['Logged_StaffId']];
    } else {
        $StatKG = 3000;
    }
} else {
    $umova = "
                IF(send_status IN ('Оплачен') AND kz_delivery<>'Почта' AND (package>1 OR LENGTH(dop_tovar)>10),total_price*0.036,if(send_status IN ('Оплачен'),total_price*0.005,0)) AS bablo,
                IF(send_status IN ('Оплачен') AND kz_delivery<>'Почта' AND ((package>1 AND LENGTH(dop_tovar)>10) OR package>2),total_price*if(country='kzg',0.054,0.036),0) AS upbablo,
                IF(send_status IN ('Оплачен') AND kz_delivery<>'Почта' AND ((package=0 AND LENGTH(dop_tovar)>10) OR (package=1 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.0105,0.007),0) AS onebablo,
                IF(send_status IN ('Оплачен') AND kz_delivery<>'Почта' AND ((package=1 AND LENGTH(dop_tovar)>10) OR (package=2 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.042,0.028),0) AS twobablo,
                IF(send_status IN ('Оплачен') AND kz_delivery='Почта' AND (package>1 OR LENGTH(dop_tovar)>10),total_price*0.036,if(send_status IN ('Оплачен'),total_price*0.005,0)) AS bablop,
                IF(send_status IN ('Оплачен') AND kz_delivery='Почта' AND ((package>1 AND LENGTH(dop_tovar)>10) OR package>2),total_price*if(country='kzg',0.054,0.036),0) AS upbablop,
                IF(send_status IN ('Оплачен') AND kz_delivery='Почта' AND ((package=0 AND LENGTH(dop_tovar)>10) OR (package=1 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.0105,0.007),0) AS onebablop,
                IF(send_status IN ('Оплачен') AND kz_delivery='Почта' AND ((package=1 AND LENGTH(dop_tovar)>10) OR (package=2 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.042,0.028),0) AS twobablop,
	";
    $and = '0 = 0';
    //var_dump($Percent); die;
    $Percent = round((array_sum($Percents) / count($Percents)) * 100);
    $Stat = round((array_sum($Stats) / count($Stats)));
    $PercentKG = round((array_sum($PercentsKG) / count($PercentsKG)) * 100);
    $StatKG = round((array_sum($StatsKG) / count($StatsKG)));
}
if ($Percent > $PercentAKz) {
    $koef = 1 + ((($Percent - $PercentAKz) * 2) / 100);
} else {
    $koef = 1 + ((($Percent - $PercentAKz) * 2) / 100);
}

if ($PercentKG > $PercentAKg) {
    $koefKG = 1 + ((($PercentKG - $PercentAKg) * 2) / 100);
} else {
    $koefKG = 1 + ((($PercentKG - $PercentAKg) * 2) / 100);
}

$curr = getCurrency(date('Y-m-d'), 'Currencys');
$is_kg = 0;
$currency['kzg'] = (array) $curr['KGS'];
$currency['am'] = (array) $curr['AMD'];
$currency['ru'] = (array) $curr['RUB'];
//var_dump($curr); die;
//if((int)$_GET['id']) $and = " staff_id = '".(int)$_GET['id']."' ";
$query = "SELECT id, package, dop_tovar,return_date, date, country,
			" . $umova . "
			if(1=1 " . $usl2 . "  ,1,0) AS log_cou,
			if(send_status IN ('Оплачен') " . $usl2 . "  ,1,0) AS dost
            FROM staff_order
            WHERE " . $and . " AND status = 'Подтвержден'
			AND return_date > '" . date('Y-m') . "-01 00:00:00' ";
//echo $query; die;
$rs = mysql_query($query);
//$obj = mysql_fetch_assoc($rs);
$bablo = array();
$dost = array();
while ($obj1 = mysql_fetch_assoc($rs)) {
    if (isset($currency[$obj1['country']]))
        $obj1['bablo'] = (($obj1['upbablo'] * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant']) + (($obj1['onebablo'] * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant']) + (($obj1['twobablo'] * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant']);
    @$bablo[strtolower($obj1['country'])] += round($obj1['bablo']);
    if ($obj1['country'] == 'kz') {
        $up += round($obj1['upbablo']);
        $one += round(@$obj1['onebablo']);
        $two += round(@$obj1['twobablo']);
        $upp += round($obj1['upbablop']);
        $onep += round(@$obj1['onebablop']);
        $twop += round(@$obj1['twobablop']);
        $nzp += round(@$obj1['s_bablo']);
    }
    if ($obj1['country'] == 'kzg') {
        $is_kg = 1;
    }
    @$dost[strtolower($obj1['country'])] += $obj1['dost'];
    @$dost_c += $obj1['log_cou'];
}
//$bablo = round($obj['upsale_bablo']+$obj['one_bablo']);
//if(in_array($_SESSION['Logged_StaffId'],$ukr_ar)) $dost = round($dost*0.55);
$itog_p = '';
$itog_d = '';
foreach ($bablo AS $country => $babos) {
    if (in_array($_SESSION['Logged_StaffId'], $ukr_ar))
        $babos = round($babos * 0.65);
    //if($_SESSION['Logged_StaffId']=='10578031') $babos = 41267;
    if ($country == 'kz')
        continue;
    $itog_p .= $babos . ' ' . $country . '<br>';
}
foreach ($dost as $country => $babos) {
    if (in_array($_SESSION['Logged_StaffId'], $ukr_ar))
        $babos = round($babos * 0.65);
    //if($_SESSION['Logged_StaffId']=='10578031') $babos = 1267;
    $itog_d .= //round(($babos/$dost_c)*100).'%
            '(' . $babos . 'шт.) ' . (($babos * 50) + 80000) . ' ' . $country . '<br>';
}
if (in_array($_SESSION['Logged_StaffId'], $ukr_ar)) {
    $one = round($one * 0.7);
    $two = round($two * 0.7);
    $up = round($up * 0.7);
    $onep = round($onep * 0.7);
    $twop = round($twop * 0.7);
    $upp = round($upp * 0.7);
}
if ($is_kg) {
    $Percent = $PercentKG;
    $Stat = $StatKG;
    $koef = $koefKG;
}

$html_str = '<div style="font-size:14px; color:blue;">Процент выкупа - ' . $Percent . ' % (за прошлый месяц от текущей даты)</div>
	<div style="font-size:14px; color:blue;">Коефициент эффективности - ' . $Stat . ' тг. на заказ</div>
	<div style="font-size:14px; color:green;">Заработок в разрезе прайсов</div>
	<table border=1>
	<tr><td style="padding:1px">1 (зп 0,' . ((in_array($_SESSION['Logged_StaffId'], $ukr_ar)) ? '5' : '7') . '%)</td>
	<td style="padding:1px">2 или 1 + (зп 2,' . ((in_array($_SESSION['Logged_StaffId'], $ukr_ar)) ? '4' : '8') . '%)</td>
	<td style="padding:1px">больше 2 (зп 3,' . ((in_array($_SESSION['Logged_StaffId'], $ukr_ar)) ? '2' : '6') . '%)</td><td style="padding:1px">Итог ЗП при<br> выкупе 59%</td></tr>
	<tr><td style="padding:1px">' . $one . ' ( ' . round($one * $koef) . ' )</td><td style="padding:1px">' . $two . ' ( ' . round($two * $koef) . ' )</td><td style="padding:1px">' . $up . ' ( ' . round($up * $koef) . ' )</td><td style="padding:1px">' . ($one + $two + $up) . '</td></tr>
	<tr><td style="padding:1px">' . $onep . ' ( ' . round($onep * $koef) . ' )</td><td style="padding:1px">' . $twop . ' ( ' . round($twop * $koef) . ' )</td><td style="padding:1px">' . $upp . ' ( ' . round($upp * $koef) . ' )</td><td style="padding:1px">' . ($onep + $twop + $upp) . '</td></tr>
	</table>
	<br><b>ЗП с учетом выкупа: ' . ($one + $two + $up + $onep + $twop + $upp) * $koef . ' kz<br>' . $itog_p . '
	<br><b>ЗП с учетом выкупа новая: ' . ($nzp) * $koef . ' kz<br>' . $itog_p . '
	<br>Логистика: <br>' . $itog_d . '';
echo '<div style="text-align:left;">' . $html_str . '</div>';
?>
