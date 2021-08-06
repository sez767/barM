<?php

header("Content-Type: text/html; charset=utf-8");

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once (dirname(__FILE__) . "/../lib/db.php");
include_once ("excel.inc.php");


if (!empty($_REQUEST['dobrik']) && $_REQUEST['dobrik'] == 'pfgbplfnj') {
    $_REQUEST['ids_data'] = json_encode(DB::queryFirstColumn("SELECT id FROM staff_order WHERE `status` = 'Подтвержден' AND fill_date <= '2019-06-30 23:59:59' AND fill_date >= '2019-06-01 00:00:00' AND `country` = 'kz'"));
}

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
    die('Нет заказов для печати');
}

$fileNameArr = array();
if (!empty($_REQUEST['from'])) {
    $fileNameArr[] = $_REQUEST['from'];
}
if (!empty($_REQUEST['to'])) {
    $fileNameArr[] = $_REQUEST['to'];
}
$fileNameArr[] = date('Y-m-d H:i');
$excel = new ExcelWriter('dostavka_' . implode('_', $fileNameArr) . '.xls');

$add = ' AND `id` IN (' . implode(',', $ids) . ') ';

$print_id = array('90316241', '40361883', '80057503');

$columnLabels = array(
    '@incr:=@incr + 1 AS pp' => '№ п.п',
    'id' => 'id',
    'fio' => 'ФИО',
    'total_price' => 'Сумма',
    'city_region' => 'Район города',
    'offer' => 'Товар',
    'dop_tovar' => 'Доп. Товар',
    'addr' => 'Адрес',
//    "IF (country = 'uz', `phone`, phone) AS phone" => 'Телефон',
    'kz_curier' => 'Номер курьера',
//    'status_cur' => 'Статус курьера',
    "DATE_FORMAT(date_delivery,'%d/%m') AS date_delivery" => 'Дата доставки',
    "''" => 'Подпись клиента',
//    "DATE_FORMAT(fill_date,'%d/%m') AS fill_date" => 'Дата заполнения',
    "IF (country = 'uz', '', description) AS description" => 'Описание',
//    '"" AS delivery_result' => 'Результат доставки',
    'deliv_desc' => 'Примечание доставки',
    'other_data' => '',
    'register_number' => '',
    'package' => '',
    'kz_delivery' => 'Тип доставки'
);

// 'Результат доставки','Описание','Примечание доставки','Тип доставки'
array(
    $obj['description'],
    $obj['deliv_desc'],
    $obj['kz_delivery']
);

$allowedFields = array();
if (isset($_GET['type'])) {
    if ((int) $_SESSION['admin']) {
        $allowedFields = array(
            'pp',
            'kz_code',
            'phone'
        );
    } else {
        $allowedFields = array(
            'pp',
            'kz_code'
        );
    }
} else {
    if (in_array($_SESSION['Logged_StaffId'], $print_id)) {
        $allowedFields = array(
            'pp',
            'id',
            'fio',
            'kz_delivery'
        );
    }
}

$query = '  SELECT ' . (implode(', ', array_keys($columnLabels))) . "
            FROM
                staff_order,
                (SELECT @incr:=0) AS vars
            WHERE 1=1 $add
            ORDER BY FIELD(id, " . implode(',', $ids) . ')';

$data = DB::query($query);

$registerNumberArr = array();
$lastRegisterNumber = '';
foreach ($data AS $row) {
    $registerNumberArr[$row['register_number']][] = $lastRegisterNumber = $row['register_number'];
    if (empty($lastRegisterNumber) || count($registerNumberArr) > 1) {
        $lastRegisterNumber = false;
        break;
    }
}
unset($columnLabels['register_number']);

if (!empty($lastRegisterNumber)) {
    $excel->writeLine(array('Реестр#: ' . $lastRegisterNumber));
}
$excel->writeLine(array(''));
$excel->writeLine(array('Отправитель: ТОО "Kazecotransit"'));
$excel->writeLine(array(''));


$first = true;
foreach ($data as $dataItem) {


    if ($_SESSION['admincity']) {
        $dataItem['addr'] = mb_substr($dataItem['addr'], 0, -5);
    }

    if ($first) {
        if (!empty($allowedFields)) {
            $columnLabels = array_intersect_key($columnLabels, array_flip($allowedFields));
        }
        unset($columnLabels['other_data']);
        unset($columnLabels['package']);
        $excel->writeLine($columnLabels);
        $first = false;
    }

    $other_data = json_decode($dataItem['other_data'], true);
    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }

    $proper = is_array($other_data) ? $other_data : array();

    if (json_last_error() == JSON_ERROR_NONE) {
        $offer = (!isset($GLOBAL_OFFER_DESC[$dataItem['offer']]) ? $dataItem['offer'] : $GLOBAL_OFFER_DESC[$dataItem['offer']]) . " " . implode(" ", $other_data);
    } else {
        $offer = (!isset($GLOBAL_OFFER_DESC[$dataItem['offer']]) ? $dataItem['offer'] : $GLOBAL_OFFER_DESC[$dataItem['offer']]);
    }

    $dataItem['offer'] = $offer . " - " . $dataItem['package'] . "шт.";

    // additional goods
    $dop_tovar = json_decode($dataItem['dop_tovar'], true);
    $dop_tovar_all = array();

    if (
            json_last_error() == JSON_ERROR_NONE &&
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

            $dop_tovar_all[] = (!isset($GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]]) ? $dop_tovar['dop_tovar'][$ke] : $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]]) . " " . implode(" ", $properties) . " - " . $dop_tovar['dop_tovar_count'][$ke] . "шт.";
        }

        $dataItem['dop_tovar'] = implode("; ", $dop_tovar_all);
    }

    unset($dataItem['package']);
    unset($dataItem['other_data']);
    unset($dataItem['register_number']);

    if (!empty($allowedFields)) {
        $dataItem = array_intersect_key($dataItem, array_flip($allowedFields));
    }
    $excel->writeLine($dataItem);
}

$excel->writeLine(array(''));
$excel->writeLine(array('', '', 'Подпись курьера:'));
$excel->writeLine(array(''));
$excel->writeLine(array(''));
$excel->writeLine(array('Дата доставки:______________________'));
$excel->writeLine(array(''));

$excel->close();
