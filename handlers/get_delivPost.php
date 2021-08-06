<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
include_once ("excel.inc.php");

if (strlen($_GET['id_str'])) {
    $add = ' AND id IN (' . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ') ';
}

$fields = "`id`, `fio`, `index`, `addr`, `kz_code` as `g`, offer, '' as w, `price`, `price` as `k`, `other_data`";
$query = "SELECT $fields FROM `coffee`.`staff_order` WHERE `status` = 'Подтвержден' " . $add . " ORDER BY offer, package";

$rs = mysql_query($query);
$dost_arr = array();
if (mysql_num_rows($rs)) {
    while ($obj = mysql_fetch_assoc($rs)) {
        $dost_arr[] = $obj;
    }
}

$excel = new ExcelWriter("dostavka_" . $_REQUEST['from'] . '_' . $_REQUEST['to'] . '.xls');
$excel->writeLine(array('Направление', '1'));
$excel->writeLine(array('Вид РПО', '3'));
$excel->writeLine(array('Категория РПО', '4'));
$excel->writeLine(array('Отправитель', ' ИП Мукалиев'));
$excel->writeLine(array('Регион назначения', '1'));
$excel->writeLine(array('Индекс ОПС места приема', '010000'));
$excel->writeLine(array('Всего РПО', (string) count($dost_arr)));
$excel->writeLine(array('№ п.п', 'id', 'ФИО', 'Индекс', 'Адрес', 'ШПИ', 'Товар', 'Вес (кг.)', 'Сумма объявленной ценности', 'Сумма нал. Платежа'));

//var_dump($dost_arr); die;
$i = 1;
foreach ($dost_arr as $obj) {
    $other_data = json_decode($obj['other_data'], true);

    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }

    $offer_property = NULL;
    // $offer_property_another = NULL;

    if (count($other_data) > 0) {
        // if ($obj['offer'] == "luxury-case") {
        // $offer_property = (isset($other_data['color']) ? "Цвет: " . $other_data['color'] : "");
        // $offer_property .= (isset($other_data['vendor']) ? "Модель: " . $other_data['vendor'] : "");
        // $offer_property_another = (isset($other_data['name']) ? "Надпись: " . $other_data['name'] : "");
        // } else {
        // $offer_property = (isset($other_data['attribute']) ? $other_data['attribute'] : "");
        // $offer_property_another = "";
        // }
        // $offer_property = trim($offer_property .  " " . $offer_property_another);

        $offer_property = implode(", ", (array) $other_data);
    }

    unset($obj['other_data']);

    array_unshift($obj, $i);

    $obj['offer'] = $$GLOBAL_OFFER_DESC[$obj['offer']] . ($offer_property ? " (" . $offer_property . ")" : "");

    $excel->writeLine($obj);
    $i++;
}

$excel->close();
