<?php

require_once dirname(__FILE__) . "/../lib/db.php";

if (!isset($_SESSION['Logged_StaffId'])) {
    header("Location: /login.html");
    die();
}

header('Content-Type: text/html; charset=utf-8', true);

include_once dirname(__FILE__) . "/excelwriter.inc.php";
require_once dirname(__FILE__) . '/../ini/php_mailer_inited.php';


$ids = array();

ApiLogger::addLogVarExport($_REQUEST);

if (!empty($_REQUEST['ids_data'])) {
    $ids = json_decode($_REQUEST['ids_data'], true);
}
foreach ($ids as $key => $id) {
    if (strlen($id) == 0 || $id <= 0) {
        unset($ids[$key]);
    }
}
if (count($ids) == 0) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array("Empty set of IDs")
    )));
}

$redis = RedisManager::getInstance()->getRedis();

$kg_cur = $redis->hGetAll("CurrierKGZ");

$couriers = array();
$sql = "SELECT `id` AS `id`, `Email` AS `email`, `City` AS `city`
	FROM `Staff`
	WHERE `City` != '' AND `Email` != ''";

$query = mysql_query($sql);
while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    $couriers[] = $row;
}

$query = mysql_query("
	SELECT
		`id`,
		`fio`,
		`addr`,
		`offer`,
		`other_data`,
		`price`,
		`package`,
		`kz_delivery`,
		`dop_tovar`
	FROM
		`coffee`.`staff_order`
	WHERE id IN (" . implode(',', $ids) . ")
	ORDER BY
		`id`,
		`offer`,
		`package`
");


$all_ar = array();

while ($obj = mysql_fetch_assoc($query)) {
    if (strlen($obj['kz_delivery']) < 2) {
        continue;
    }

    // обработка атрибутов товара
    $other_data = json_decode($obj['other_data'], true);

    if (json_last_error() == JSON_ERROR_NONE) {
        krsort($other_data);
        $offer = $GLOBAL_OFFER_DESC[$obj['offer']] . " " . implode(" ", $other_data);
    } else {
        $offer = $GLOBAL_OFFER_DESC[$obj['offer']];
    }

    // товар в массив
    $all_ar[$obj['kz_delivery']][$offer][] = array(
        'offer' => $offer,
        'price' => $obj['price'],
        'package' => $obj['package'],
    );

    // обработка дополнительного товара
    $dop_tovar = json_decode($obj['dop_tovar'], true);

    if (
            json_last_error() == JSON_ERROR_NONE &&
            isset($dop_tovar['dop_tovar']) &&
            is_array($dop_tovar['dop_tovar'])
    ) {
        foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {
            $properties = array();
            $properties_key = array_keys($dop_tovar);

            foreach ($properties_key AS $property_key) {
                if (
                        !in_array($property_key, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) &&
                        isset($dop_tovar[$property_key][$ke]) &&
                        !empty($dop_tovar[$property_key][$ke])
                ) {
                    $properties[] = $dop_tovar[$property_key][$ke];
                }
            }

            krsort($properties);
            $offer = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . " " . implode(" ", $properties);

            $all_ar[$obj['kz_delivery']][$offer][] = array(
                'offer' => $offer,
                'price' => $dop_tovar['dop_tovar_price'][$ke],
                'package' => $dop_tovar['dop_tovar_count'][$ke],
            );
        }
    }
}

//var_dump($all_ar); die;
$currDateTime = date('Y-m-d H:i');
foreach ($all_ar as $oks => $all_arr) {
    ksort($all_arr);
    $all_c = 0;
    $excel = new ExcelWriter(dirname(__FILE__) . "/../tmp/send/delivlist_V2_" . str_replace(" ", "", $oks) . '_' . $currDateTime . '.xls');
    $excel->writeLine(array(''));
    $excel->writeLine(array('Дата скачивания', date('d-m-Y')));
    $excel->writeLine(array('', '', '', '<b>Упаковочный лист</b>', '', '', ''));
    $excel->writeLine(array('<b>Почта</b>'));
    $excel->writeLine(array('<b>' . $oks . '</b>'));

    foreach ($all_arr as $ok => $ov) {
        $sh = 0;

        foreach ($ov as $kv => $obj) {
            $sh += $obj['package'];
        }

        $excel->writeLine(array($ok, '-', $sh));

        $all_c += $sh;
    }

    $excel->writeLine(array('<b>Итого:</b>', '<b>' . $all_c . '</b>'));
    $excel->writeLine(array(''));
    $excel->close();
}

$actionHistoryObj = new ActionHistoryObj();
foreach ($couriers as $courier) {
    if (!file_exists(dirname(__FILE__) . "/../tmp/send/delivlist_V2_" . str_replace(" ", "", $courier['city']) . '_' . $currDateTime . '.xls')) {
        continue;
    }
    $newSubject = 'Документы НЕВЫКУП УПЛ2 ' . $courier['city'] . ' за ' . date('Y-m-d');
    $newBody = "Отчет УПЛ2";
    $newAttachmentArr[] = dirname(__FILE__) . "/../tmp/send/delivlist_V2_" . str_replace(" ", "", $courier['city']) . '_' . $currDateTime . '.xls';

    if (in_array($courier['city'], $kg_cur)) {
//        $newAddressArr[] = 'asik_as@mail.ru';
    }
    $newAddressArr[] = $courier['email'];
//    $newAddressArr = array();
//    $newAddressArr[] = 'sdobrovol@gmail.com';
    $se = DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName);
//    ApiLogger::addLogVarDump($se);die;
    //var_dump($courier['email'],dirname(__FILE__) . "/../tmp/send/delivlist_V2_".str_replace(" ","",$courier['city'])."_" . $currDateTime . '.xls'); die;

    if ($se) {
        $result['data'][] = "Письмо для курьера " . $courier['city'] . " отправлено на E-Mail " . $courier['email'];
    } else {
        $result['data'][] = "Ошибка отправки письма для курьера " . $courier['city'] . " на E-Mail " . $courier['email'];
    }
    $actionHistoryObj->save('EmailObj', $_SESSION['Logged_StaffId'], 'insert', 'sendmail', '', $courier['email']);
}

print json_encode($result);

