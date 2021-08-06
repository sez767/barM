<?php

/**
 * Сбор данных о заказах с доставкой на "сегодня" и отправка на E-Mail курьерам
 * Заказы с доставкой на сегодня
 * Упаковочный лист
 * Конверт
 */
# error_reporting(E_ALL);
# ini_set("display_errors", 1);

header("Content-Type: text/html; charset=utf-8");

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("Location: login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
include_once dirname(__FILE__) . '/excelwriter.inc.php';
require_once dirname(__FILE__) . '/../ini/php_mailer_inited.php';



$ids = array();
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

$num_ar = $configs["courier_numbers"];

//
// Start baribarda selection
//
$sql = "SELECT
		`id`,
		`ext_id`,
		 CONCAT('bar',id) as bid,
		`fio`,
		if(country != 'kzg',`phone`,'') as phone,
		`total_price` as price,
		CONCAT(city,', ',street,' ',building) as addr,
		`city_region`,
		`offer`,
		DATE_FORMAT(`date_delivery`, '%d-%m') AS `date_delivery`,
		`kz_curier`,
		`country`,
		`other_data`,
		`deliv_desc`,
		`description`,
		`kz_delivery`,
		`staff_id`,
		`dop_tovar`,
		`package`,
		`kz_curier` AS `courier`,
        `date_delivery_first`,
        IF (staff_id NOT IN (22222222, 33333333) AND country = 'false' AND CURDATE() + INTERVAL 1 DAY <= `date_delivery_first`, 1, 0) AS add_podarok
	FROM `staff_order`
	WHERE
		`send_status` = 'Отправлен' AND
        `status_kz` IN ('На доставку', 'Вручить подарок') AND
        (DATE_FORMAT(`date_delivery`,'%Y-%m-%d') = CURDATE() + INTERVAL 1 DAY OR DATE_FORMAT(`date_delivery`,'%Y-%m-%d') = CURDATE()) AND
		id IN (" . implode(',', $ids) . ")
		#id IN (1579423, 1579468)
	ORDER BY `kz_delivery`";

//var_dump($sql); die;
$arr_b = DB::query($sql);
//print_r($arr_b);die;


if (count($arr_b) == 0) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array("Not found any orders")
    )));
}

$counter = 1;
$envelope_b = array();

foreach ($arr_b AS $val) {
    // третий файл / конверт
    $envelope_b[md5($val['kz_delivery'])][] = array(
        'id' => $val['id'],
        'bid' => $val['bid'],
        'fio' => $val['fio'],
        'staff_id' => $val['staff_id'],
        'package' => $val['package'],
        'other_data' => $val['other_data'],
        'dop_tovar' => $val['dop_tovar'],
        'country' => $val['country'],
        'add_podarok' => $val['add_podarok'],
        'price' => $val['price'],
        'offer' => $val['offer']
    );

    $counter++;
}

//print_r($envelope_b);
//die;

////////////////////////////////////////////////////////////
// для baribarda
////////////////////////////////////////////////////////////
$currDateTime = date('Y-m-d H:i');
$tomorrowDate = date('d.m.Y', strtotime('+1 day'));
$filename = dirname(__FILE__) . '/../tmp/send/citydoc_zprint_baribarda_' . $currDateTime . '.txt';

$fp = fopen($filename, 'w');

$iter = 0;
foreach ($arr_b AS $row) {

    $other_data = json_decode($row['other_data'], true);
    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }
    $offer_property = NULL;
    // $offer_property_another = NULL;
    if (count($other_data) > 0) {
        $offer_property = implode(', ', (array) $other_data);
    }

    $tovar = array(
        $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property && $row['offer'] !== 'pullover' ? " (" . $offer_property . ")" : "") . " - " . $row['package'] . "шт." . ($row['add_podarok'] > 0 ? ' +подарок' : ''),
    );

    // additional goods
    $dop_tovar = json_decode($row['dop_tovar'], true);
    $dop_tovar_all = array();

    if (json_last_error() == JSON_ERROR_NONE && is_array($dop_tovar['dop_tovar'])) {

        foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {
            $properties = array();
            $properties_key = array_keys($dop_tovar);

            foreach ($properties_key AS $property_key) {
                if (!in_array($property_key, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) && isset($dop_tovar[$property_key][$ke]) && !empty($dop_tovar[$property_key][$ke])) {
                    $properties[$property_key] = $dop_tovar[$property_key][$ke];
                }
            }

            $str = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]];
            if (count($properties) > 0) {
                if (isset($properties['gift'])) {
                    $properties['gift'] = "подарок";
                    unset($properties['gift_price']);
                }
                $str .= ($va === 'pullover') ? '' : " (" . implode(', ', $properties) . ")";
            }
            $str .= " - " . $dop_tovar['dop_tovar_count'][$ke] . "шт.";
            $tovar[] = $str;
        }
    }

    if ($iter) {
        fwrite($fp, PHP_EOL);
        fwrite($fp, '-----------------' . PHP_EOL);
        fwrite($fp, PHP_EOL);
    }

    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
        fwrite($fp, 'ТОО «KBTGROUP»' . PHP_EOL);
        fwrite($fp, 'БИН 180340028283' . PHP_EOL);
    } elseif ($row['country'] == 'am') {
        fwrite($fp, 'ЧП «Саргсян»' . PHP_EOL);
    } else {
        fwrite($fp, 'ОСОО КБТ' . PHP_EOL);
    }

    fwrite($fp, "Дата: $tomorrowDate" . PHP_EOL);
    fwrite($fp, "Продажа #{$row['id']}" . PHP_EOL);
    fwrite($fp, 'Менеджер: Baribarda' . PHP_EOL);
    fwrite($fp, $row['fio'] . PHP_EOL);
    fwrite($fp, implode("\n", $tovar) . PHP_EOL);
    $sumStr = "Сумма: {$row['price']} ";
    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
        $sumStr .= 'тг.';
    } elseif ($row['country'] == 'am') {
        $sumStr .= 'драм.';
    } else {
        $sumStr .= 'сом.';
    }
    fwrite($fp, $sumStr . PHP_EOL);
    fwrite($fp, '*****************' . PHP_EOL);
    fwrite($fp, "Адрес. Республика Казахстан," . PHP_EOL);
    if (($ourAddressStr = getOurAddressStr($row))) {
        fwrite($fp, $ourAddressStr . PHP_EOL);
    }

    $tehStr = "Номер поддержки: ";
    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
//            $tehStr .= "+7(705)924 03 70";
        $tehStr .= "2442";
    } elseif ($row['country'] == 'kzg' or $row['country'] == 'KZG') {
        $tehStr .= "+996770008168, +996770008162, +996770008160";
    }
    fwrite($fp, $tehStr . PHP_EOL);
    $iter++;
}

fclose($fp);
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($filename));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
ob_clean();
flush();
readfile($filename);
