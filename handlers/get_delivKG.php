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

$fields = "id, fio,phone, addr,price, price as k, '' as w, '' as g, kz_code, description";
$query = "SELECT $fields FROM staff_order WHERE status = 'Подтвержден' AND country='kzg' " . $add . " ORDER BY id, offer, package";
$rs = mysql_query($query);

$excel = new ExcelWriter("dostavka_" . $_REQUEST['from'] . '_' . $_REQUEST['to'] . '.xls');

if ((int) $_GET['otpr'] == 1) {
    $chp = 'Мамытов К.А.';
    $addr = ' ул. Омуралиева, д. 6';
} elseif ((int) $_GET['otpr'] == 2) {
    $chp = 'Мамытов К.А.';
    $addr = ' ул. Омуралиева, д. 6';
}

$excel->writeLine(array(''));
$excel->writeLine(array('', '', '<b>Список</b>', '', '', '', 'ф.103'));
$excel->writeLine(array(''));
$excel->writeLine(array('Отправитель', '', 'ИП «' . $chp . '»', '', '', '', date('d.m.Y')));
$excel->writeLine(array('Чуйская область, Кеминский район, село Кичи-Кемин, ' . $addr));

$excel->writeLine(array('№ п.п', 'id', 'ФИО', 'Телефон', 'Адрес', 'Сумма оценки', 'Сумма нал. Платежа', 'Вес', 'Плата за пересылку', '№ ц/б по ф.103', 'Описание'));

if (mysql_num_rows($rs)) {
    $i = 1;
    while ($obj = mysql_fetch_assoc($rs)) {
        array_unshift($obj, $i);
        $excel->writeLine($obj);
        $i++;
    }
}
$excel->close();
