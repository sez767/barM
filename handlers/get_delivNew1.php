<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

include_once ("excel.inc.php");
if (strlen($_GET['id_str'])) {
    $add = ' AND id IN (' . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ') ';
}

$print_id = array('90316241', '40361883', '80057503');

if (in_array($_SESSION['Logged_StaffId'], $print_id)) {
    $fields = "id, fio, kz_delivery";
} else {
    $fields = "id, fio, price, city_region, offer, if(country IN ('kzg','am'),addr,addr) as addr, if(country IN ('kzg','am'),phone,'') as phone, kz_curier, status_cur, DATE_FORMAT(date_delivery,'%d-%m'), '' as tt, if(country IN ('kzg','am'),description,''), deliv_desc, other_data,if(country IN ('am'),package,'')";
}

if (isset($_GET['type'])) {
    $fields = "kz_code";
}

if (isset($_GET['type']) && (int) $_SESSION['admin']) {
    $fields = "kz_code, phone";
}

$query = "SELECT $fields FROM staff_order WHERE status IN ('Подтвержден','Предварительно подтвержден') " . $add . " ORDER BY FIELD(id, " . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ")";
//echo $query; die;
$rs = mysql_query($query);

$excel = new ExcelWriter("dostavka_" . $_REQUEST['from'] . '_' . $_REQUEST['to'] . '.xls');

//$excel->writeLine(array("<h2>Отчет по доставке </h2>"));
if (!in_array($_SESSION['Logged_StaffId'], $print_id)) {
    /* $excel->writeLine(array('Направление','1'));
      $excel->writeLine(array('Вид РПО','3'));
      $excel->writeLine(array('Категория РПО','4'));
      $excel->writeLine(array('Отправитель',' ИП Кумаров'));
      $excel->writeLine(array('Регион назначения','1'));
      $excel->writeLine(array('Индекс ОПС места приема','010000'));
      $excel->writeLine(array('Всего РПО',(string)mysql_num_rows($rs))); */
}

if (isset($_GET['type'])) {
    if ((int) $_SESSION['admin']) {
        $excel->writeLine(array('№ п.п', 'штрих-код', 'Телефон'));
    } else {
        $excel->writeLine(array('№ п.п', 'штрих-код'));
    }
} else {
    if (in_array($_SESSION['Logged_StaffId'], $print_id)) {
        $excel->writeLine(array('№ п.п', 'id', 'ФИО', 'Тип доставки'));
    } else {
        $excel->writeLine(array('№ п.п', 'id', 'ФИО', 'Сумма', 'Район города', 'Товар', 'Адрес', 'Телефон', 'Номер курьера', 'Статус курьера', 'Дата', 'Результат доставки', 'Описание', 'Примечание доставки'));
    }
}

if (mysql_num_rows($rs)) {
    $i = 1;
    while ($obj = mysql_fetch_assoc($rs)) {
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
}

$excel->close();
