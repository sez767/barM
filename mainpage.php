<?php

header("Content-Type: text/html; charset=utf-8");

session_set_cookie_params(10800);
session_start();
var_dump($_SESSION);
if (!isset($_SESSION['Logged_StaffId'])) {
    header('location: /login.html');
    die();
}
require_once dirname(__FILE__) . '/lib/db.php';

$ukr_ar = array('11111111','77777777', '26823714', '37239410', '30224289', '30356360', '76488859', '79319411', '47671128', '72480483', '80911164', '21630264', '44440873', '62443980', '77767205', '10578031');
if (in_array($_SESSION['Logged_StaffId'], $ukr_ar)) {
    $xoxol = PHP_EOL . 'var xoxol = 1;';
} else {
    $xoxol = PHP_EOL . 'var xoxol = 0;';
    $xoxol = PHP_EOL . 'var xoxol = 0;';
}

$redis = RedisManager::getInstance()->getRedis(); // ketkz
$country_regions = "'" . implode("','", $redis->hGetAll('country_regions')) . "'";
$status_mail_reset = "'" . IMPLODE("','", $redis->hGetAll('status_mail_reset')) . "'";

//$new_sim = array('31901396', '29205777', '74409173', '15339842');
$new_sim = array();

$status_check = "'" . implode("','", (array) $redis->hGetAll('status_check')) . "'";
$send_status = $send_status_no_send = $redis->hGetAll('send_statuses');
$send_status = "'" . implode("','", $send_status) . "'";
foreach ($send_status_no_send as $kk => $val) {
    if ($val == 'На отправку') {
        unset($send_status_no_send[$kk]);
    }
    if ($val == 'Отказ' && !in_array($_SESSION['Logged_StaffId'], array(11111111, 88189675 , 25937686, 63077972 , 70623931 , 63279961 , 51651814, 12019085, 64395288))) {
        unset($send_status_no_send[$kk]);
    }
}
$send_status_no_send = "'" . implode("','", $send_status_no_send) . "'";

// фильтры "Район города"
$delivery_currier_regions = array();
$redis_delivery_currier_regions = $redis->hGetAll('CurrierRegions');
foreach ($redis_delivery_currier_regions AS $key => $value) {
    $delivery_currier_regions[] = array(
        $value,
        $value
    );
}

$js_arr_cur_kz = PHP_EOL . "var cur_kz = ['Почта',";
$js_arr_cur_am = PHP_EOL . "var cur_am = ['Почта',";
$js_arr_cur_az = PHP_EOL . "var cur_az = ['Почта',";
$js_arr_cur_md = PHP_EOL . "var cur_md = [";
$js_arr_cur_uz = PHP_EOL . "var cur_uz = [";
$js_arr_cur_ae = PHP_EOL . "var cur_ae = [";
$js_arr_cur_kg = PHP_EOL . "var cur_kg = [";
$js_arr_cur_ru = PHP_EOL . "var cur_ru = ['Почта',";

// список для Курьеров
$currier_kz = $redis->hGetAll('Currier');
$currier_kg = $redis->hGetAll('CurrierKGZ');
$currier_am = $redis->hGetAll('CurrierAM');
$currier_az = $redis->hGetAll('CurrierAZ');
$currier_md = $redis->hGetAll('CurrierMD');
$currier_uz = $redis->hGetAll('CurrierUZ');
$currier_ae = $redis->hGetAll('CurrierAE');
$currier_ru = $redis->hGetAll('CurrierRus');

asort($currier_kz);
asort($currier_kg);
asort($currier_am);
asort($currier_az);
asort($currier_md);
asort($currier_uz);
asort($currier_ae);
foreach ($currier_kg as $ck => $cv) {
    $js_arr_cur_kg .= "'" . $cv . "',";
}

foreach ($currier_ru as $ck => $cv) {
    $js_arr_cur_ru .= "'" . $cv . "',";
}

foreach ($currier_am as $ck => $cv) {
    $js_arr_cur_am .= "'" . $cv . "',";
}
foreach ($currier_az as $ck => $cv) {
    $js_arr_cur_az .= "'" . $cv . "',";
}
foreach ($currier_md as $ck => $cv) {
    $js_arr_cur_md .= "'" . $cv . "',";
}
foreach ($currier_uz as $ck => $cv) {
    $js_arr_cur_uz .= "'" . $cv . "',";
}
foreach ($currier_ae as $ck => $cv) {
    $js_arr_cur_ae .= "'" . $cv . "',";
}
foreach ($currier_kz as $ck => $cv) {
    $js_arr_cur_kz .= "'" . $cv . "',";
}

$js_arr_cur_kz .= ' ];';
$js_arr_cur_kg .= ' ];';
$js_arr_cur_am .= ' ];';
$js_arr_cur_az .= ' ];';
$js_arr_cur_md .= ' ];';
$js_arr_cur_uz .= ' ];';
$js_arr_cur_ae .= ' ];';
$js_arr_cur_ru .= ' ];';

$offered = PHP_EOL . 'var offered = new Object();';
$offer_name = PHP_EOL . 'var offer_name = new Object();';

$prop_q = mysql_query("SELECT * FROM  offer_property WHERE property_name = 'deliv_price'");
$proper_deliv = array();
while ($rowp = mysql_fetch_array($prop_q)) {
    $proper_deliv[$rowp['property_offer']][$rowp['property_location']] = $rowp['property_value'];
}

$offerArr = array();
$st_q = mysql_query('SELECT offer_id, offer_group, offer_name AS st, offer_desc, offer_clientprice AS ofprice FROM offers WHERE offers_active ORDER BY offer_group, offer_name');
while ($row = mysql_fetch_array($st_q)) {
//    $offerArr[] = array($row['st'],$row['offer_desc']);
    $offerArr[] = $row['st'];
    $offered .= PHP_EOL . "offered.o" . str_replace('-', '_', $row['offer_id']) . " = '" . $row['st'] . "';";
    $offer_name .= PHP_EOL . "offer_name." . str_replace('-', '_', $row['st']) . " = '" . $row['offer_desc'] . "';";
    $_SESSION['offer_' . $row['st']] = $row['offer_desc'];
    $_SESSION['ofprice_' . $row['st']] = $row['ofprice'];
    if (isset($proper_deliv[$row['offer_id']])) {
        foreach ($proper_deliv[$row['offer_id']] as $country => $value) {
            $_SESSION['dprice_' . $row['st']][$country] = $value;
        }
    }
}
$staff_ar = PHP_EOL . 'var staff_ar = [';

$sta_q = mysql_query("SELECT id, Sip, CONCAT(FirstName, ' ', LastName) AS fio FROM Staff  WHERE 1 ORDER BY FirstName");
while ($rowa = mysql_fetch_array($sta_q)) {
    $_SESSION['Login' . $rowa['id']] = $rowa['fio'];
    $staff_ar .= '[' . $rowa['id'] . ", '" . $rowa['fio'] . "'],";
    if (strlen($rowa['Sip'])) {
        $_SESSION['SIP/' . $rowa['Sip']] = $rowa['fio'];

    }
}
$staff_ar = substr($staff_ar, 0, strlen($staff_ar) - 1);
$staff_ar .= ' ];';

//////////////////////////////////////
// OFFER PRICES
$pricesArr = array();
$pricesStr = '';
$offer_discount = PHP_EOL . 'var offer_discount = new Object();';
$pricesDBData = DB::query("SELECT * FROM offers LEFT JOIN offer_property ON offer_id = property_offer WHERE offers_active AND property_name LIKE 'price%' AND CHAR_LENGTH(property_name)=6");
foreach ($pricesDBData AS $priceItem) {
    $pricesArr[$priceItem['offer_name']][$priceItem['property_location']][substr($priceItem['property_name'], 5, 1)] = $priceItem['property_value'];
    if ($priceItem['property_name'] == 'action_price') {
        $offer_discount .= PHP_EOL . "offer_discount." . str_replace('-', '_', $priceItem['property_location']) . '___' . str_replace('-', '_', $priceItem['offer_name']) . " = ['" . $priceItem['property_description'] . "','" . $priceItem['property_value'] . "','" . $priceItem['property_apply'] . "'];";
    }
}

foreach ($pricesArr AS $offname => $offv) {
    ksort($offv);
    foreach ($offv AS $kc => $kv) {
        ksort($kv);
        if (count($kv) > 1) {
            foreach ($kv AS &$tmpVal) {
                $tmpVal *= 1;
            }
            unset($tmpVal);
            $pricesStr .= PHP_EOL . 'var ' . str_replace('-', '_', trim($offname)) . '_' . $kc . ' = ' . json_encode(array_values($kv)) . ';';
        }
    }
}

// COLD PRICES
$pricesArr = array();
$pricesColdStr = '';
$pricesDBData = DB::query("SELECT * FROM offers LEFT JOIN offer_property ON offer_id = property_offer WHERE offers_active AND property_name LIKE 'pricecold%' AND CHAR_LENGTH(property_name) = 10");
foreach ($pricesDBData AS $priceItem) {
    $pricesArr[$priceItem['offer_name']][$priceItem['property_location']][substr($priceItem['property_name'], 9, 1)] = $priceItem['property_value'];
}
foreach ($pricesArr AS $offname => $offv) {
    ksort($offv);
    foreach ($offv AS $kc => $kv) {
        ksort($kv);
        if (count($kv) > 1) {
            foreach ($kv AS &$tmpVal) {
                $tmpVal *= 1;
            }
            unset($tmpVal);
            $pricesColdStr .= PHP_EOL . 'var ' . str_replace('-', '_', trim($offname)) . '_' . $kc . '_cold = ' . json_encode(array_values($kv)) . ';';
        }
    }
}
// END OFFER PRICES

$whatsAppDialogs = array();
if ($_SESSION['whatsappoperator'] && isset($_COOKIE['WhatsAppDialogs'])){
    $whatsAppDialogs = json_decode($_COOKIE['WhatsAppDialogs'], true);
}
?>
<html>
    <head>
        <title>BaribardaLite cabinet</title>

        <link rel="stylesheet" type="text/css" href="css/dx.common.css">
        <link rel="stylesheet" type="text/css" href="css/dx.material.teal.light.css">
        <link rel="stylesheet" type="text/css" href="css/index.css">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/dx.all.js"></script>
        <script type="text/javascript" src="js/mainpage.js"></script>
        <script type="text/javascript" src="js/dx.messages.ru.js"></script>
    </head>
    <body class="dx-viewport">
        <div class="demo-container">
        <div id="toolbar"></div>
        <div id="drawer">
            <div id="view" class="dx-theme-background-color"></div>
        </div>
        </div>
    </body>
</html>