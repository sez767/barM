<?php

/**
 * Сбор данных о заказах с доставкой на "сегодня" и отправка SMS курьерам
 * Заказы с доставкой на сегодня
 * Упаковочный лист
 * Конверт
 */
# error_reporting(E_ALL);
# ini_set("display_errors", 1);

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("Location: login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
include_once dirname(__FILE__) . "/excelwriter.inc.php";

if (strlen($_POST['id_str'])) {
    $add = '`id` IN (' . substr($_POST['id_str'], 0, strlen($_POST['id_str']) - 1) . ')';
} else {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array("Not found any orders")
    )));
}

$num_ar = $configs["courier_numbers"];

//
// Start baribarda selection
//
$sql = "
	SELECT
		`id`,
		`ext_id`,
		 CONCAT('bar',id) as bid,
		`fio`,
		`phone`,
		`total_price` as price,
		 CONCAT(city,', ',street,' ',building) as addr ,
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
		`kz_curier` AS `courier`
	FROM
		`staff_order`
	WHERE
        `send_status` = 'Отправлен' AND
        `status_kz` IN ('На доставку', 'Вручить подарок') AND
        `date_delivery` = CURDATE() + INTERVAL 1 DAY AND
		" . $add . "
	ORDER BY
		`kz_delivery`
";

$query = mysql_query($sql);

$arr_b = array();

while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    $arr_b[] = $row;
}
//
// End baribarda selection
//

if (count($arr_b) == 0) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array("Not found any orders")
    )));
}

//
// Start courier selection
//
$couriers = array();

$sql = "
	SELECT
		`id` AS `id`,
		`Email` AS `email`,
		`City` AS `city`,
		`Phone` AS `phone`
	FROM
		`Staff`
	WHERE
		`City` != '' AND
		`Email` != ''
";

$query = mysql_query($sql);

while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    $couriers[] = $row;
}
//
// End courier selection
//

$counter = 1;
$orders_b = array();
$packing_b = array();
$envelope_b = array();
$dopdelivery_b = array();

foreach ($arr_b AS $val) {
    // первый файл / заказы
    $orders_b[md5($val['kz_delivery'])][] = array(
        "counter" => $counter,
        "id" => $val['id'],
        "fio" => $val['fio'],
        "price" => $val['price'],
        "addr" => $val['addr'],
        "city_region" => $val['city_region'],
        "offer" => $val['offer'],
        "package" => $val['package'],
        "other_data" => $val['other_data'],
        "dop_tovar" => $val['dop_tovar'],
        "kz_curier" => $val['kz_curier'],
        "deliv_desc" => $val['deliv_desc']
    );

    // второй файл / упаковочный лист
    $packing_b[md5($val['kz_delivery'])][] = array(
        "id" => $val['id'],
        "offer" => $val['offer'],
        "other_data" => $val['other_data'],
        "dop_tovar" => $val['dop_tovar'],
        "kz_delivery" => $val['kz_delivery'],
        "package" => $val['package']
    );

    // третий файл / конверт
    $envelope_b[md5($val['kz_delivery'])][] = array(
        "id" => $val['id'],
        "bid" => $val['bid'],
        "fio" => $val['fio'],
        "staff_id" => $val['staff_id'],
        "package" => $val['package'],
        "other_data" => $val['other_data'],
        "dop_tovar" => $val['dop_tovar'],
        "country" => $val['country'],
        "price" => $val['price'],
        "offer" => $val['offer']
    );

    // четвертый файл / дополнительный реестр
    $dopdelivery_b[md5($val['kz_delivery'])][] = array(
        "id" => $val['id'],
        "phone" => $val['phone'],
        "addr" => $val['addr'],
        "courier" => $val['courier']
    );

    $counter++;
}

$result = array(
    "success" => TRUE,
    "data" => array()
);

$redis = RedisManager::getInstance()->getRedis();

////////////////////////////////////////////////////////////
// для baribarda
////////////////////////////////////////////////////////////

foreach ($couriers as $courier) {

    if (empty($courier['phone'])) {
        continue;
    }

    if (!isset($orders_b[md5($courier['city'])])) {
        continue;
    }

    // Суффикс файла
    $file_suffix = date('Ymd') . '_' . md5(time() . $courier['city']);

    // Номер реестра
    $reg = (int) $redis->get('Registr');
    $reg++;

    //
    // Первый файл / (XLS)
    //
    $excel = new ExcelWriter(dirname(__FILE__) . "/../tmp/send/citydoc_orders_baribarda_" . $file_suffix . '.xls');
    $excel->writeLine(array('Реестр#: __' . $reg . '__'));
    $redis->set('Registr', $reg);
    $excel->writeLine(array('Отправитель: ТОО "Kazecotransit"'));
    $excel->writeLine(array(''));
    $excel->writeLine(array('№ п.п', 'id', 'ФИО', 'Сумма', 'Адрес', 'Район города', 'Товар', 'Дата', 'Номер курьера', 'Примечание доставки', 'результат доставки'));

    foreach ($orders_b[md5($courier['city'])] AS $order) {
        // main goods
        $other_data = json_decode($order['other_data'], true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $offer = $GLOBAL_OFFER_DESC[$order['offer']] . " " . implode(" ", $other_data);
        } else {
            $offer = $GLOBAL_OFFER_DESC[$order['offer']];
        }

        $order['offer'] = $offer . " - " . $order['package'] . "шт.";

        // additional goods
        $dop_tovar = json_decode($order['dop_tovar'], true);
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

                $dop_tovar_all[] = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . " " . implode(" ", $properties) . " - " . $dop_tovar['dop_tovar_count'][$ke] . "шт.";
            }

            $order['dop_tovar'] = implode("; ", $dop_tovar_all);
        }

        unset($order['package']);
        unset($order['other_data']);

        $excel->writeLine($order);
    }

    $pre_num = explode("^", $num_ar[$courier['city']]);
    $excel->writeLine(array(''));
    $excel->writeLine(array("", "", 'Подпись курьера:'));
    $excel->writeLine(array(''));
    $excel->writeLine(array('Дата доставки:______________________'));
    $excel->writeLine(array(''));
    $excel->writeLine(array("", "", 'Номер Администратора для курьера ' . $pre_num[1]));
    $excel->writeLine(array('', 'Номер Менеджера для клиента ' . $pre_num[0]));
    $excel->writeLine(array('', 'Что бы связаться с клиентом наберите номер 8 707 112 18 99, 8 707 112 18 95'));
    $excel->close();

    //
    // Второй файл / Упаковочный лист (XLS)
    //
	$excel = new ExcelWriter(dirname(__FILE__) . "/../tmp/send/citydoc_packing_baribarda_" . $file_suffix . '.xls');
    $excel->writeLine(array(''));
    $excel->writeLine(array('', '', '', '<b>Упаковочный лист</b>', '', '', ''));
    $excel->writeLine(array('<b>Почта</b>'));

    $all_ar = array();

    foreach ($packing_b[md5($courier['city'])] AS $obj) {
        // additional goods
        $dop_tovar = json_decode($obj['dop_tovar'], true);

        if (json_last_error() == JSON_ERROR_NONE) {
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

                $offer = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . " " . implode(" ", $properties);

                $all_ar[$obj['kz_delivery']][$offer][] = array(
                    'offer' => $dop_tovar['dop_tovar'][$ke] . ' ' . implode(" ", $properties),
                    'price' => $dop_tovar['dop_tovar_price'][$ke],
                    'package' => $dop_tovar['dop_tovar_count'][$ke],
                );
            }
        }

        unset($obj['dop_tovar']);

        // main goods
        $other_data = json_decode($obj['other_data'], true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $offer = $GLOBAL_OFFER_DESC[$obj['offer']] . " " . implode(" ", $other_data);
        } else {
            $offer = $GLOBAL_OFFER_DESC[$obj['offer']];
        }

        unset($obj['other_data']);

        $all_ar[$obj['kz_delivery']][$offer][] = $obj;
    }

    foreach ($all_ar AS $oks => $all_arr) {
        ksort($all_arr);
        $all_c = 0;
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
    }

    $excel->close();

    //
    // Третий файл / конверт (PDF)
    //
	$x = 25;
    $y = 50;
    $h = 15;
    $l = 1;

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'px', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins($x, $y, $x);
    $pdf->setFontSubsetting(true);

    foreach ($envelope_b[md5($courier['city'])] AS $row) {
        $other_data = json_decode($row['other_data'], true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $other_data = array();
        }

        $offer_property = NULL;

        if (count($other_data) > 0) {
            $offer_property = implode(', ', $other_data);
        }

        
        $pdf->AddPage();

        // Нижние разделители (отрезные линии)
        $pdf->SetLineStyle(array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,5,2,5', 'phase' => 10, 'color' => array(0, 0, 0)));

        // $pdf->Line($x - 10, $y + $h * 20, $x - 10 + 550, $y + $h * 20);
        //$pdf->Line($x + 200, $y + $h * 20, $x + 200, $y + $h * 52);
        // Стандартное оформление линий
        $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

        // Товарная накладная
        $pdf->SetFont('arial', 'B', 14, '', true);
        //$pdf->Text($x, $y, "Товарная накладная № " . $row['id'] . " от " . currentFullDate() . "г.");
        $pdf->Text($x, $y, "Товарная накладная № " . $row['id'] . " ");
        $pdf->Line($x, $y + $h + 5, $x + 550, $y + $h + 5);

        $l = 2;

        // Строка с поставщиком
        $pdf->SetFont('arial', '', 10, '', true);
        $pdf->Text($x, $y + $h * $l, "Поставщик");

        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->SetXY($x + 200, $y + $h * $l);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ')
            $pdf->MultiCell(0, 0, "Товарищество с ограниченной ответственностью \"KAZECOTRANSIT\"", 0, 'L', 0);
        elseif ($row['country'] == 'am')
            $pdf->MultiCell(0, 0, "ЧП «Саргсян»", 0, 'L', 0);
        else
            $pdf->MultiCell(0, 0, "ЧП «Абдыракманов»", 0, 'L', 0);
        $l = 4;

        // Строка с получателем
        $pdf->SetFont('arial', '', 10, '', true);
        $pdf->Text($x, $y + $h * $l, "Покупатель");

        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->Text($x + 200, $y + $h * $l, $row['fio']);

        //
        // Таблица с товаром
        //

    $l = $l + 2;
        $pdf->SetXY($x, $y + $h * $l);

        // Заголовки таблицы
        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->setCellPaddings(3, 3, 3, 3);
        $pdf->Cell(100, 0, "Номер заказа", 1, 0, 'C', 0);
        $pdf->Cell(350, 0, "Товар", 1, 0, 'C', 0);
        $pdf->Cell(100, 0, "Сумма", 1, 0, 'C', 0);

        $pdf->Ln();

        $tovar = array(
            $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property ? " (" . $offer_property . ")" : "") . " - " . $row['package'] . "шт."
        );

        //    if (is_json($row['dop_tovar'])) {
        // 	$tmp_dop = json_decode($row['dop_tovar'], true);
        // 	foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
        // 		$tovar[] = $GLOBAL_OFFER_DESC[$va] . ' '. (isset($tmp_dop['vendor'][$ke])?$tmp_dop['vendor'][$ke]:'') .' '. (isset($tmp_dop['color'][$ke])?$tmp_dop['color'][$ke]:'') .' '. (isset($tmp_dop['name'][$ke])?$tmp_dop['name'][$ke]:'') .' '. (isset($tmp_dop['type'][$ke])?$tmp_dop['type'][$ke]:'') .' '. (isset($tmp_dop['size'][$ke])?$tmp_dop['size'][$ke]:'') .' - ' . $tmp_dop['dop_tovar_count'][$ke] . 'шт.';
        // 	}
        // }
        // additional goods
        $dop_tovar = json_decode($row['dop_tovar'], true);
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
                        $properties[$property_key] = $dop_tovar[$property_key][$ke];
                    }
                }

                $str = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]];
                if (count($properties) > 0) {
                    if (isset($properties['gift'])) {
                        $properties['gift'] = "подарок";
                        unset($properties['gift_price']);
                    }

                    $str .= " (" . implode(', ', $properties) . ")";
                }
                $str .= " - " . $dop_tovar['dop_tovar_count'][$ke] . "шт.";

                $dop_tovar_all[] = $str;
            }

            $tovar[] = implode("; ", $dop_tovar_all);
        }

        // Данные таблицы
        $pdf->Cell(100, (count($tovar) * $h) + 6, $row['id'], 1, 0, 'L', 0);
        $pdf->MultiCell(350, (count($tovar) * $h), implode("\n", $tovar), 1, 'L', 0, 0);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . "тг.", 1, 0, 'L', 0);
        } elseif ($row['country'] == 'am') {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . "драм.", 1, 0, 'L', 0);
        } else {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . "сом.", 1, 0, 'L', 0);
        }
        $l += (count($tovar) + 3);

        $pdf->SetFont('arial', '', 12, '', true);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x, $y + $h * $l, num2str($row['price']));
        } elseif ($row['country'] == 'am') {
            $pdf->Text($x, $y + $h * $l, num2dram($row['price']));
        } else {
            $pdf->Text($x, $y + $h * $l, num2som($row['price']));
        }

        $l += 2;

        // Отпустил
        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->Text($x, $y + $h * $l, "Отпустил");

        $l += 1;

        $pdf->Line($x + 60, $y + $h * $l, $x + 190, $y + $h * $l);

        // Получил
        $pdf->Text($x + 200, $y + $h * ($l - 1), "Получил");
        $pdf->Line($x + 255, $y + $h * $l, $x + 500, $y + $h * $l);
        $pdf->SetFont('arial', '', 12, '', true);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x, $y + $h * ($l + 0.5), "* Служба заботы о клиентах 87779552697");
        }
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x, $y + 20 + $h * ($l + 0.5), $row['kz_curier']);
        //$pdf->Text($x, $y + 300 + $h * ($l+0.5), "Описание доставки: ".$row['deliv_desc']);
        //$pdf->Line($x, $y + 290 + $h * ($l+0.5),$x+500 ,$y + 290 + $h * ($l+0.5));
        $pdf->Line($x, $y + 330 + $h * ($l + 0.5), $x + 500, $y + 330 + $h * ($l + 0.5));

        /// копия
        /* $pdf->StartTransform();
          $pdf->Rotate(360,200,300);
          $y = 300;
          // Нижние разделители (отрезные линии)
          $pdf->SetLineStyle(array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,5,2,5', 'phase' => 10, 'color' => array(0, 0, 0)));

          //$pdf->Line($x - 10, $y + $h * 20, $x - 10 + 550, $y + $h * 20);
          //$pdf->Line($x + 200, $y + $h * 20, $x + 200, $y + $h * 52);

          // Стандартное оформление линий
          $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

          // Товарная накладная
          $pdf->SetFont('arial', 'B', 14, '', true);
          $pdf->Text($x, $y, "Товарная накладная № " . $row['id'] . " от " . currentFullDate() . "г.");
          $pdf->Line($x, $y + $h + 5, $x + 550, $y + $h + 5);

          $l = 2;

          // Строка с поставщиком
          $pdf->SetFont('arial', '', 10, '', true);
          $pdf->Text($x, $y + $h * $l, "Поставщик");

          $pdf->SetFont('arial', '', 12, '', true);
          $pdf->SetXY($x + 200, $y + $h * $l);
          if($row['country']=='kz' or $row['country']=='KZ') $pdf->MultiCell(0, 0, "Товарищество с ограниченной ответственностью \"KAZECOTRANSIT\"", 0, 'L', 0);
          else $pdf->MultiCell(0, 0, "ЧП «Абдыракманов»", 0, 'L', 0);

          $l = 4;

          // Строка с получателем
          $pdf->SetFont('arial', '', 10, '', true);
          $pdf->Text($x, $y + $h * $l, "Покупатель");

          $pdf->SetFont('arial', '', 12, '', true);
          $pdf->Text($x + 200, $y + $h * $l, $row['fio']);

          //
          // Таблица с товаром
          //

          $l = $l + 2;
          $pdf->SetXY($x, $y + $h * $l);

          // Заголовки таблицы
          $pdf->SetFont('arial', '', 12, '', true);
          $pdf->setCellPaddings(3, 3, 3, 3);
          $pdf->Cell(100, 0, "Номер заказа", 1, 0, 'C', 0);
          $pdf->Cell(350, 0, "Товар", 1, 0, 'C', 0);
          $pdf->Cell(100, 0, "Сумма", 1, 0, 'C', 0);

          $pdf->Ln();

          $tovar = array(
          $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property ? " (" . $offer_property . ")" : ""). " - " . $row['package'] . "шт."
          );

          if (is_json($row['dop_tovar'])) {
          $tmp_dop = json_decode($row['dop_tovar'], true);
          foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
          $tovar[] = $GLOBAL_OFFER_DESC[$va] . ' '. (isset($tmp_dop['vendor'][$ke])?$tmp_dop['vendor'][$ke]:'') .' '. (isset($tmp_dop['color'][$ke])?$tmp_dop['color'][$ke]:'') .' '. (isset($tmp_dop['name'][$ke])?$tmp_dop['name'][$ke]:'') .' - ' . $tmp_dop['dop_tovar_count'][$ke] . 'шт.';
          }
          }

          // Данные таблицы
          $pdf->Cell(100, (count($tovar) * $h) + 6, $row['id'], 1, 0, 'L', 0);
          $pdf->MultiCell(350, (count($tovar) * $h), implode("\n", $tovar), 1, 'L', 0, 0);
          if($row['country']=='kz' or $row['country']=='KZ') $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . "тг.", 1, 0, 'L', 0);
          else $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . "сом.", 1, 0, 'L', 0);

          $l = $l + count($tovar) + 3;

          $pdf->SetFont('arial', '', 12, '', true);
          if($row['country']=='kz' or $row['country']=='KZ') $pdf->Text($x, $y + $h * $l, num2str($row['price']));
          else $pdf->Text($x, $y + $h * $l, num2som($row['price']));

          $l = $l + 2;

          // Отпустил
          $pdf->SetFont('arial', '', 12, '', true);
          $pdf->Text($x, $y + $h * $l, "Отпустил");

          $l = $l + 1;

          $pdf->Line($x + 60, $y + $h * $l, $x + 190, $y + $h * $l);

          // Получил
          $pdf->Text($x + 200, $y + $h * ($l - 1), "Получил");
          $pdf->Line($x + 255, $y + $h * $l, $x + 500, $y + $h * $l);
          $pdf->SetFont('arial', '', 8, '', true);
          if($row['country']=='kz' or $row['country']=='KZ') $pdf->Text($x, $y + $h * ($l+0.5), "* Номер тех.поддержки 87770000363");
         */
        $y = 50;
        $pdf->StartTransform();
        $pdf->Rotate(90, 250, 590);
        // Левая часть расписки
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->SetXY($x - 10, $y + $h * 22);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ')
            $pdf->MultiCell(200, 0, "Приложение 1\nк приказу Министра финансов республики Казахстан\nот 20 декабря 2012 года №562\n\nформа КО-1", 0, 'R', 0);
        elseif ($row['country'] == 'am')
            $pdf->MultiCell(200, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Республики Армения\n форма КО-1", 0, 'R', 0);
        else
            $pdf->MultiCell(200, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Кыргызской Республики\n форма КО-1", 0, 'R', 0);
        $pdf->SetXY($x - 10, $y + $h * 27);
        $pdf->MultiCell(200, 0, "Организация (ТОО)", 0, 'L', 0);

        $pdf->SetFont('arial', 'U', 8, '', true);
        $pdf->SetXY($x - 10, $y + $h * 28);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ')
            $pdf->MultiCell(200, 0, "Товарищество с ограниченной\nответственностью \"KAZECOTRANSIT\"", 0, 'C', 0);
        elseif ($row['country'] == 'am')
            $pdf->MultiCell(200, 0, "ЧП «Саргсян»", 0, 'C', 0);
        else
            $pdf->MultiCell(200, 0, "ЧП «Абдыракманов»", 0, 'C', 0);

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->SetXY($x - 10, $y + $h * 30);
        $pdf->MultiCell(200, 0, "КВИТАНЦИЯ\nк приходному касовому ордеру", 0, 'C', 0);

        $pdf->Text($x - 10, $y + $h * 32, "№");
        $pdf->Text($x + 10, $y + $h * 32, $row['id']);
        $pdf->Line($x + 5, $y + $h * 33, $x + 190, $y + $h * 33);

        $pdf->Text($x - 10, $y + $h * 33 + 5, "Принято от");
        $pdf->Text($x - 10, $y + $h * 34 + 5, $row['fio']);
        $pdf->Line($x - 10, $y + $h * 37, $x + 190, $y + $h * 37);

        $pdf->Text($x - 10, $y + $h * 38, "Сумма");
        if ($row['country'] == 'kz' || $row['country'] == 'KZ')
            $pdf->Text($x - 10, $y + $h * 39, num2str($row['price']));
        elseif ($row['country'] == 'am')
            $pdf->Text($x - 10, $y + $h * 39, num2dram($row['price']));
        else
            $pdf->Text($x - 10, $y + $h * 39, num2som($row['price']));
        $pdf->Line($x - 10, $y + $h * 40, $x + 190, $y + $h * 40);

        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Text($x + 10, $y + $h * 40 - 3, "прописью");

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x - 10, $y + $h * 41, "М.П.");
        //$pdf->Text($x + 30, $y + $h * 41, date('d.m.Y') . " года");

        $pdf->Text($x - 10, $y + $h * 43, "Главный бухгалтер или уполномоченное лицо");
        $pdf->Text($x + 90, $y + $h * 44, "Не предусмотрен");

        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Line($x + 25, $y + $h * 45, $x + 80, $y + $h * 45);
        $pdf->Text($x + 40, $y + $h * 45, "подпись");

        $pdf->Line($x + 90, $y + $h * 45, $x + 190, $y + $h * 45);
        $pdf->Text($x + 100, $y + $h * 45, "расшифровка подписи");

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x - 10, $y + $h * 46, "Кассир");
        $pdf->Text($x + 90, $y + $h * 46, "Не предусмотрен");
        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Line($x + 25, $y + $h * 47, $x + 80, $y + $h * 47);
        $pdf->Text($x + 40, $y + $h * 47, "подпись");

        $pdf->Line($x + 90, $y + $h * 47, $x + 190, $y + $h * 47);
        $pdf->Text($x + 100, $y + $h * 47, "расшифровка подписи");
        /*
          // Правая часть расписки
          $pdf->SetFont('arial', 'U', 10, '', true);
          $pdf->SetXY($x + 210, $y + $h * 21);
          $pdf->MultiCell(300, 0, "Товарищество с ограниченной ответственностью \"KAZECOTRANSIT\"", 0, 'C', 0);

          $pdf->SetFont('arial', 'B', 14, '', true);
          $pdf->SetXY($x + 210, $y + $h * 23);
          $pdf->MultiCell(300, 0, "Расписка", 0, 'C', 0);

          $pdf->SetFont('arial', '', 10, '', true);

          $pdf->Text($x + 210, $y + $h * 24 + 7, "ФИО");
          $pdf->Line($x + 210 + 30, $y + $h * 25 + 7, $x + 500, $y + $h * 25 + 7);

          $pdf->Text($x + 210, $y + $h * 27, "№ документа удостоверяющий личность, ГРНЗТС");
          $pdf->Line($x + 210, $y + $h * 27, $x + 500, $y + $h * 27);

          $pdf->Text($x + 210, $y + $h * 29, "Тел.:");
          $pdf->Line($x + 210 + 30, $y + $h * 30, $x + 500, $y + $h * 30);

          $pdf->SetXY($x + 210, $y + $h * 31);
          $pdf->MultiCell(300, 0, "Получил на руки заказ №" . $row['id'] . " для доставки покупателю (" . $row['fio'] . ")\n\nПосле реализции заказа (получения мной оплаты за товар), полученную сумму в размере " . $row['price'] . " (" . num2str($row['price']) . ") тенге (Сумма может быть изменена в сторону понижения) обязуюсь сдать в кассу в срок до 21:00 " . date('d.m.Y') . "г.", 0, 'L', 0);

          $pdf->Text($x + 210, $y + $h * 40, "Дата");
          $pdf->Line($x + 210 + 50, $y + $h * 41, $x + 500, $y + $h * 41);

          $pdf->Text($x + 210, $y + $h * 42, "Подпись");
          $pdf->Line($x + 210 + 50, $y + $h * 43, $x + 500, $y + $h * 43);
         */
    }

    $pdf->Output(dirname(__FILE__) . "/../tmp/send/citydoc_envelope_baribarda_" . $file_suffix . '.pdf', "F");

    //
    // Генерация кода и отправка SMS
    //
    $links = array(
        "citydoc_orders" => "/tmp/send/citydoc_orders_baribarda_" . $file_suffix . '.xls',
        "citydoc_packing" => "/tmp/send/citydoc_packing_baribarda_" . $file_suffix . '.xls',
        "citydoc_envelope" => "/tmp/send/citydoc_envelope_baribarda_" . $file_suffix . '.pdf',
    );

    $code = generateString();
    $redis->set("ADMIN_REGISTER/" . $code, json_encode($links));
    $redis->expire("ADMIN_REGISTER/" . $code, 86400); // 24 hours
    // sendKZSMS($courier['phone'], "Ваш код для получения файлов реестра: " . $code)

    if (sendKcellSMS($courier['phone'], "Ваш код для получения файлов реестра: " . $code, 0)) {
        $result['data'][] = "SMS для курьера " . $courier['city'] . " отправлена на номер " . $courier['phone'];
    } else {
        $result['data'][] = "Error: Ошибка отправки SMS для курьера " . $courier['city'] . " на номер " . $courier['phone'];
    }
}

//
// Показать результат
//
print json_encode($result);

