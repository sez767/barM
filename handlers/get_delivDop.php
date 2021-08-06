<?php

require_once dirname(__FILE__) . '/../lib/db.php';
include_once 'excel.inc.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

if (strlen($_GET['id_str'])) {
    $add = ' AND id IN (' . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ') ';
}

$fields = "id, if (country='kzg', phone, '') as phone, if (country='kzg', addr, addr) as addr, `kz_curier` AS `courier`, `kz_operator` AS `operator`";

$query = "SELECT $fields FROM staff_order WHERE status IN ('Подтвержден','Предварительно подтвержден') " . $add . " ORDER BY `id`";

$rs = mysql_query($query, $db_link_ref);

$excel = new ExcelWriter("dostavka_dop.xls");

$excel->writeLine(array(
    'ID',
    'Телефон',
    'Номер курьера',
    'Адрес',
    'Оператор',
    'Результат',
    'Результат',
    'Результат',
    'Результат',
    'Результат'
));

// ket_asterisk_base();

if (mysql_num_rows($rs)) {
    while ($obj = mysql_fetch_assoc($rs)) {
        // $courier_phone = "";
        // if ($obj['courier'] > 0) {
        // $query = mysql_query("SELECT `courier_phone` FROM `coffee`.`Staff` WHERE `is_courier` = 1 AND `courier_id` = '" . (int) $obj['courier'] . "' LIMIT 1", $ext3_link);
        // $row = mysql_fetch_assoc($query);
        // $courier_phone = $row['courier_phone'];
        // }

        $line = array(
            $obj['id'],
            $obj['phone'],
            $obj['courier'],
            $obj['addr'],
            $obj['operator'],
        );

        $excel->writeLine($line);
    }
}

$excel->close();
