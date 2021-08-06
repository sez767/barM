<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
include_once ("excel.inc.php");
$start_date = $_REQUEST['from'] . ' 00:00:00';
$end_date = $_REQUEST['to'] . ' 23:59:59';
$fields = " * ";
if (strlen($_GET['id_str'])) {
    $add = ' AND id IN (' . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ') ';
}
if ($_SESSION['Logged_StaffId'] == '66629642') {
    $fields = " ext_id, '' as q, '' as w, CONCAT(offer, ' ', '(',package,'шт.)'), '1', 'шт.', price, '' as e, fio, '' as r, '' as t, addr, '' as phone, `index`, description, kz_delivery";
}
$query = "  SELECT $fields FROM staff_order
            WHERE   status = 'Подтвержден'
                    AND fill_date > '" . $start_date . "'
                    AND fill_date < '" . $end_date . "' " . $add . " AND staff_id = " . (int) $_SESSION['Logged_StaffId'];
//echo $query; die;
$rs = mysql_query($query);

$excel = new ExcelWriter("dostavka_" . $_REQUEST['from'] . '_' . $_REQUEST['to'] . '.xls');

$excel->writeLine(array("<h2>Отчет по доставке c " . $_REQUEST['from'] . " по " . $_REQUEST['to'] . "</h2>"));
if ($_SESSION['Logged_StaffId'] == '66629642') {
    $excel->writeLine(array('Номер', '', '', 'Товар и колличество', 'колличество', 'ед. измерения', 'Цена', '', 'ФИО', '', '', 'Адрес', 'Телефон', 'Индекс', 'Комментарий', 'Способ доставки'));
}
if (mysql_num_rows($rs)) {
    while ($obj = mysql_fetch_assoc($rs)) {
        $excel->writeLine($obj);
    }
}
$excel->close();
?>
