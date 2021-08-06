<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
include_once "excelwriter.inc.php";
require_once dirname(__FILE__) . '/../ini/php_mailer_inited.php';


if (strlen($_GET['id_str']) > 10) {
    $add = ' AND id IN (' . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ') ';
//echo $add; die;
    $fields = " * ";
    if ((int) $_GET['otpr'] == 3) {
        $otprav = 'ИП "BRDmarket"';
    }
    if ((int) $_GET['otpr'] == 4) {
        $otprav = 'ИП  «Нурлыханов» ';
    }
    if ((int) $_GET['otpr'] == 5) {
        $otprav = 'ИП «Садыков Д. Ш.»';
    }
    if ((int) $_GET['otpr'] == 6) {
        $otprav = 'ИП «Садыкова  Д. О.»';
    }
//if($_SESSION['Logged_StaffId'] == '66629642')
//$fields = " fio,  CONCAT(SUBSTRING(`index`,1,4),'00'), addr, offer, kz_code, '' as w, price, total_price as k,package, CONCAT('+',phone) as phone";
    $fields = " fio, `index`, addr, offer, kz_code, '' as w, price, total_price as k,package, CONCAT('+',phone) as phone";

    $query = "SELECT $fields FROM staff_order
				WHERE status = 'Подтвержден' AND kz_delivery='Почта' " . $add . " ORDER BY offer,package";
    //echo $query; die;
    $rs = mysql_query($query);
    $dost_arr = array();
    if (mysql_num_rows($rs)) {
        while ($obj = mysql_fetch_assoc($rs)) {
            $dost_arr[$obj['offer']][$obj['package']][$obj['price']][] = $obj;
        }
    }
//    $newAddressArr[] = 'your__hero@mail.ru';
//    $newAddressArr[] = 'igor-viznovich@mail.ru';
    $newSubject = 'Доставка за ' . date('Y-m-d');
    $newBody = 'Отчет';

    $all_doc = array();
    $count_doc = 0;
    foreach ($dost_arr as $offer => $off_ar) {
        foreach ($off_ar as $package => $sum_pr) {
            foreach ($sum_pr as $price => $sum_a) {
                if (count($sum_a) < 6) {
                    $all_doc[] = $sum_a;
                    $count_doc += count($sum_a);
                    continue;
                }
                //var_dump((string)count($sum_a)); var_dump($offer); var_dump($package); var_dump($sum_a);
                $excel = new ExcelWriter(dirname(__FILE__) . "/../tmp/send/dostavka_{$_REQUEST['from']}_{$_REQUEST['to']}_{$offer}_{$package}_{$price}.xls");
                $excel->writeLine(array('Направление', '1'));
                $excel->writeLine(array('Вид РПО', '3'));
                $excel->writeLine(array('Категория РПО', '4'));
                $excel->writeLine(array('Отправитель', ' ' . $otprav));
                $excel->writeLine(array('Регион назначения', '1'));
                $excel->writeLine(array('Индекс ОПС места приема', '010000'));
                $excel->writeLine(array('Всего РПО', (string) count($sum_a)));
                $excel->writeLine(array('№ п.п', 'ФИО', 'Индекс', 'Адрес', 'ШПИ', 'Вес', 'Сумма объявленной ценности', 'Сумма нал. Платежа', '', 'Номер тел.'));
                $i = 1;
                foreach ($sum_a as $obj) {
                    unset($obj['offer']);
                    array_unshift($obj, $i);
                    $excel->writeLine($obj);
                    $i++;
                }
                $excel->close();
                $newAttachmentArr[] = dirname(__FILE__) . "/../tmp/send/dostavka_{$_REQUEST['from']}_{$_REQUEST['to']}_{$offer}_{$package}_{$price}.xls";     // optional name
            }
        }
    }
    $excel = new ExcelWriter(dirname(__FILE__) . "/../tmp/send/dostavka_{$_REQUEST['from']}_{$_REQUEST['to']}_ALL.xls");
    $excel->writeLine(array('Направление', '1'));
    $excel->writeLine(array('Вид РПО', '3'));
    $excel->writeLine(array('Категория РПО', '4'));
    $excel->writeLine(array('Отправитель', ' ' . $otprav));
    $excel->writeLine(array('Регион назначения', '1'));
    $excel->writeLine(array('Индекс ОПС места приема', '010000'));
    $excel->writeLine(array('Всего РПО', (string) $count_doc));
    $excel->writeLine(array('№ п.п', 'ФИО', 'Индекс', 'Адрес', 'ШПИ', 'Вес', 'Сумма объявленной ценности', 'Сумма нал. Платежа'));
    $i = 1;
    //var_dump((string)$count_doc);
    foreach ($all_doc as $sum_a) {
        foreach ($sum_a as $obj) {
            unset($obj['offer']);

            array_unshift($obj, $i);
            $excel->writeLine($obj);
            $i++;
        }
    }
    //die;
    $excel->close();
    $newAttachmentArr[] = dirname(__FILE__) . "/../tmp/send/dostavka_{$_REQUEST['from']}_{$_REQUEST['to']}_ALL.xls";     // optional name
}
$result = DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName);
//var_dump($result);
