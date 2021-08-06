<?php

/**
 * Сбор данных о заказах с доставкой на "сегодня" и отправка на E-Mail курьерам
 * Заказы с доставкой на сегодня
 * Упаковочный лист
 * Конверт
 */
# error_reporting(E_ALL);
# ini_set("display_errors", 1);

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header('Location: login.html');
    die;
}

header('Content-Type: text/html; charset=utf-8');
//die('pizda');
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
require_once dirname(__FILE__) . '/excelwriter.inc.php';
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
        'success' => false,
        'data' => array('Empty set of IDs')
    )));
}

$num_ar = $configs['courier_numbers'];

//
// Start baribarda selection
//
$sql = "SELECT
            `id`,
            `uae_id`,
            `ext_id`,
             CONCAT('bar',id) as bid,
            `fio`,
            IF(country != 'kzg',`phone`,'') AS phone,
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
            (DATE_FORMAT(`date_delivery`,'%Y-%m-%d') = CURDATE() + INTERVAL 1 DAY OR DATE_FORMAT(`date_delivery`,'%Y-%m-%d') = CURDATE()) AND id IN (" . implode(',', $ids) . ")
	ORDER BY offer, `kz_delivery`, uuid";

//die($sql);
$arr_b = DB::query($sql);

if (count($arr_b) == 0) {
    die(json_encode(array(
        'success' => false,
        'data' => array('Not found any orders')
    )));
}

// Courier selection
$couriers = DB::query("SELECT `id`, `Email` AS `email`, `City` AS `city` FROM `Staff` WHERE `City` != '' AND `Email` != ''");

$redis = RedisManager::getInstance()->getRedis();
$curKGZ = $redis->hGetAll('CurrierKGZ');

$counter = 1;
$orders_b = array();
$packing_b = array();
$envelope_b = array();
$dopdelivery_b = array();

foreach ($arr_b AS $val) {
    // первый файл / заказы
    $addItem = array(
        'counter' => $counter,
        'id' => $val['id'],
        'uae_id' => $val['uae_id'],
        'staff_id' => $val['staff_id'],
        'ext_id' => $val['ext_id'],
        'fio' => $val['fio'],
        'price' => $val['price'],
        'addr' => $val['addr'],
        'city_region' => $val['city_region'],
        'offer' => $val['offer'],
        'description' => $val['description'],
        'phone' => $val['phone'],
        'package' => $val['package'],
        'other_data' => $val['other_data'],
        'dop_tovar' => $val['dop_tovar'],
        'add_podarok' => $val['add_podarok'],
        'kz_curier' => $val['kz_curier'],
        'deliv_desc' => $val['deliv_desc']
    );
    if (in_array($val['kz_delivery'], $curKGZ)) {
        unset($addItem['add_podarok'], $addItem['phone']);
    } else {
        unset($addItem['description'], $addItem['phone']);
    }
    $orders_b[md5($val['kz_delivery'])][] = $addItem;

    // второй файл / упаковочный лист
    $packing_b[md5($val['kz_delivery'])][] = array(
        'id' => $val['id'],
        'offer' => $val['offer'],
        'other_data' => $val['other_data'],
        'dop_tovar' => $val['dop_tovar'],
        'kz_delivery' => $val['kz_delivery'],
        'add_podarok' => $val['add_podarok'],
        'package' => $val['package']
    );

    // третий файл / конверт
    $envelope_b[md5($val['kz_delivery'])][] = array(
        'id' => $val['id'],
        'bid' => $val['bid'],
        'fio' => $val['fio'],
        'staff_id' => $val['staff_id'],
        'ext_id' => $val['ext_id'],
        'package' => $val['package'],
        'other_data' => $val['other_data'],
        'dop_tovar' => $val['dop_tovar'],
        'country' => $val['country'],
        'kz_delivery' => $val['kz_delivery'],
        'add_podarok' => $val['add_podarok'],
        'price' => $val['price'],
        'offer' => $val['offer']
    );

    // четвертый файл / дополнительный реестр
    $dopdelivery_b[md5($val['kz_delivery'])][] = array(
        'id' => $val['id'],
        'phone' => $val['phone'],
        'addr' => $val['addr'],
        'courier' => $val['courier']
    );

    $counter++;
}

$result = array(
    'success' => TRUE,
    'data' => array()
);

////////////////////////////////////////////////////////////
// для baribarda
////////////////////////////////////////////////////////////
$currDate = date('Y-m-d H');
$tomorrowDate = date('d.m.Y', strtotime('+1 day'));

foreach ($couriers as $courier) {
    if (!isset($orders_b[md5($courier['city'])])) {
        continue;
    }
    $reg = (int) $redis->get('Registr');
    $reg++;

//////////////////////////////////////////////////////////////////////
// СОЗДАЕМ ПЕРВЫЙ ФАЙЛ
    $excel = new ExcelWriter(dirname(__FILE__) . '/../tmp/send/citydoc_orders_baribarda_' . $currDate . '.xls');
    $excel->writeLine(array('Реестр#: __' . $reg . '__'));
    $redis->set('Registr', $reg);
    $excel->writeLine(array('Отправитель: ТОО "Kazecotransit"'));
    $excel->writeLine(array(''));
    if (in_array($courier['city'], $curKGZ)) {
        $excel->writeLine(array('№ п.п', 'id', 'Ket-id', 'ФИО', 'Сумма', 'Адрес', 'Район города', 'Товар', 'Коммент', 'Доп. Товар', 'Номер курьера', 'Примечание доставки'));
    } else {
        $excel->writeLine(array('№ п.п', 'id', 'Ket-id', 'ФИО', 'Сумма', 'Адрес', 'Район города', 'Товар', 'Доп. Товар', 'Подарок', 'Номер курьера', 'Примечание доставки'));
    }

    foreach ($orders_b[md5($courier['city'])] AS $order) {
        // main goods
        $other_data = json_decode($order['other_data'], true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $offer = $GLOBAL_OFFER_DESC[$order['offer']] . ' ' . implode(' ', $other_data);
        } else {
            $offer = $GLOBAL_OFFER_DESC[$order['offer']];
        }

        $order['offer'] = $offer . ' - ' . $order['package'] . 'шт.';

        // additional goods
        $dop_tovar = json_decode($order['dop_tovar'], true);
        $dop_tovar_all = array();

        if (json_last_error() == JSON_ERROR_NONE && is_array($dop_tovar['dop_tovar'])) {
            foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {
                $properties = array();
                $properties_key = array_keys($dop_tovar);
                foreach ($properties_key AS $property_key) {
                    if (!in_array($property_key, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) && isset($dop_tovar[$property_key][$ke]) && !empty($dop_tovar[$property_key][$ke])) {
                        $properties[] = $dop_tovar[$property_key][$ke];
                    }
                }
                $dop_tovar_all[] = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . ' ' . implode(' ', $properties) . ' - ' . $dop_tovar['dop_tovar_count'][$ke] . 'шт.';
            }
            $order['dop_tovar'] = implode('; ', $dop_tovar_all);
        }

        if ($order['staff_id'] == 11112222 && !empty($order['ext_id'])) {
            $order['id'] .= " ({$order['ext_id']})";
        }

        unset($order['package']);
        unset($order['other_data']);
        unset($order['staff_id']);
        unset($order['ext_id']);

        $excel->writeLine($order);
    }

    $pre_num = explode('^', $num_ar[$courier['city']]);
    $excel->writeLine(array(''));
    $excel->writeLine(array('', '', 'Подпись курьера:'));
    $excel->writeLine(array(''));
//    $excel->writeLine(array('Для продаж курьера:'));
//    $excel->writeLine(array('Люцем 5'));
//    $excel->writeLine(array('Ламинария 5'));
//    $excel->writeLine(array('Сакура женский 5'));
//    $excel->writeLine(array('Капли сакура мен 5'));
//    $excel->writeLine(array(''));
    $excel->writeLine(array(''));
    $excel->writeLine(array('Дата доставки:______________________'));
    $excel->writeLine(array(''));
    if (in_array($courier['city'], $curKGZ)) {
        $excel->writeLine(array('', '', 'Номер Администратора для курьера +77057459935'));
    }
    $excel->close();

//////////////////////////////////////////////////////////////////////
// СОЗДАЕМ ВТОРОЙ ФАЙЛ / УПАКОВОЧНЫЙ ЛИСТ
    $excel = new ExcelWriter(dirname(__FILE__) . '/../tmp/send/citydoc_packing_baribarda_' . $currDate . '.xls');
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

                $offer = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . ' ' . implode(' ', $properties);

                $all_ar[$obj['kz_delivery']][$offer][] = array(
                    'offer' => $dop_tovar['dop_tovar'][$ke] . ' ' . implode(' ', $properties),
                    'price' => $dop_tovar['dop_tovar_price'][$ke],
                    'package' => $dop_tovar['dop_tovar_count'][$ke],
                );
            }
        }

        unset($obj['dop_tovar']);

        // main goods
        $other_data = json_decode($obj['other_data'], true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $offer = $GLOBAL_OFFER_DESC[$obj['offer']] . ' ' . implode(' ', $other_data);
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
            $podar = 0;
            foreach ($ov as $kv => $obj) {
                $sh += $obj['package'];
                $podar += $obj['add_podarok'];
            }
            if ($podar > 0) {
                $excel->writeLine(array('Подарок', '-', $podar));
            }
            $excel->writeLine(array($ok, '-', $sh));
            $all_c += $sh;
        }

        $excel->writeLine(array('<b>Итого:</b>', '<b>' . $all_c . '</b>'));
        $excel->writeLine(array(''));
    }

    $excel->close();

//////////////////////////////////////////////////////////////////////
// СОЗДАЕМ ТРЕТИЙ ФАЙЛ / КОНВЕРТ
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
            $offer_property = implode(', ', (array) $other_data);
        }

                $pdf->AddPage();

        // Нижние разделители (отрезные линии)
        $pdf->SetLineStyle(array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,5,2,5', 'phase' => 10, 'color' => array(0, 0, 0)));

        // Стандартное оформление линий
        $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

        // Товарная накладная
        $pdf->SetFont('arial', 'B', 14, '', true);
        $pdf->Text($x, $y, "Товарная накладная № {$row['id']}");
        if ($row['staff_id'] == 11112222 && !empty($row['ext_id'])) {
            $pdf->SetFont('arial', 'B', 16, '', true);
            $pdf->Text($x + 230, $y, " ({$row['ext_id']})");
        }
        $pdf->Line($x, $y + $h + 6, $x + 550, $y + $h + 6);

        if (!file_exists(__DIR__ . '/../tmp/barcodes/' . $row['id'] . '_barcode.png')) {
            $cs = curl_init();
            curl_setopt($cs, CURLOPT_URL, 'http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=10&rot=0&text=' . "BRD_{$row['id']}" . '&f1=0&f2=10&a1=&a2=&a3=');
            curl_setopt($cs, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($cs, CURLOPT_FAILONERROR, true);
            curl_setopt($cs, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cs, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cs, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($cs, CURLOPT_TIMEOUT, 30);
            curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cs, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cs, CURLOPT_USERAGENT, 'TCPDF');
            $ret = curl_exec($cs);
            curl_close($cs);
            file_put_contents(__DIR__ . '/../tmp/barcodes/' . $row['id'] . '_barcode.png', $ret);
        }
        $pdf->Image(__DIR__ . '/../tmp/barcodes/' . $row['id'] . '_barcode.png', 400 + $x, $y - 5, 140, 20);

        $l = 2;

        // Строка с поставщиком
        $pdf->SetFont('arial', '', 10, '', true);
        $pdf->Text($x, $y + $h * $l, 'Поставщик');

        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->SetXY($x + 200, $y + $h * $l);

        $postavStr = getOtpravitelStr($row);
        $pdf->MultiCell(0, 0, $postavStr, 0, 'L', 0);

        $pdf->SetFont('arial', '', 12, '', true);
        $l += 2;

        // Строка с получателем
        $pdf->SetFont('arial', '', 10, '', true);
        $pdf->Text($x, $y + $h * $l, 'Покупатель');

        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->Text($x + 200, $y + $h * $l, $row['fio']);

        // Таблица с товаром
        $l += 2;
        $pdf->SetXY($x, $y + $h * $l);

        // Заголовки таблицы
        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->setCellPaddings(3, 3, 3, 3);
        $pdf->Cell(100, 0, 'Номер заказа', 1, 0, 'C', 0);

        $tovarStr = 'Товар';
        if ($row['country'] == 'kz' && !in_array($row['staff_id'], array(11111111, 22222222, 33333333, 55555555, 57369831))) {
            $tovarStr .= ' КС-1';
        } else if ($row['country'] == 'kz' && in_array($row['staff_id'], array(11111111, 22222222, 33333333, 55555555, 57369831))) {
            $tovarStr .= ' КС-1';
        }
        $pdf->Cell(350, 0, $tovarStr, 1, 0, 'C', 0);
        $pdf->Cell(100, 0, 'Сумма', 1, 0, 'C', 0);

        $pdf->Ln();

        $tovar = array($GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property && $row['offer'] !== 'pullover' ? ' (' . $offer_property . ')' : '') . ' - ' . $row['package'] . 'шт.' . ($row['add_podarok'] > 0 ? ' +подарок' : ''));

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
                        $properties['gift'] = 'подарок';
                        unset($properties['gift_price']);
                    }

                    $str .= ($va === 'pullover') ? '' : ' (' . implode(', ', $properties) . ')';
                }
                $str .= ' - ' . $dop_tovar['dop_tovar_count'][$ke] . 'шт.';

                $dop_tovar_all[] = $str;
            }

            $tovar[] = implode('; ', $dop_tovar_all);
        }

        // Данные таблицы
        $pdf->Cell(100, (count($tovar) * $h) + 6, $row['id'], 1, 0, 'L', 0);
        $pdf->MultiCell(350, (count($tovar) * $h), implode("\n", $tovar), 1, 'L', 0, 0);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . 'тг.', 1, 0, 'L', 0);
        } elseif ($row['country'] == 'am') {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . 'драм.', 1, 0, 'L', 0);
        } elseif ($row['country'] == 'uz') {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . 'сум.', 1, 0, 'L', 0);
        } else {
            $pdf->Cell(100, (count($tovar) * $h) + 6, $row['price'] . 'сом.', 1, 0, 'L', 0);
        }
        $l = $l + count($tovar) + 3;

        $pdf->SetFont('arial', '', 12, '', true);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x, $y + 100 + $h * $l, num2str($row['price']));
        } elseif ($row['country'] == 'am') {
            $pdf->Text($x, $y + 100 + $h * $l, num2dram($row['price']));
        } else {
            $pdf->Text($x, $y + 100 + $h * $l, num2som($row['price']));
        }

        $l += 2;

        // Отпустил
        $pdf->SetFont('arial', '', 12, '', true);
        $pdf->Text($x, $y + 100 + $h * $l, 'Отпустил');

        $l += 1;

        $pdf->Line($x + 60, $y + 100 + $h * $l, $x + 190, $y + 100 + $h * $l);

        // Получил
        $pdf->Text($x + 200, $y + 100 + $h * ($l - 1), 'Получил');
        $pdf->Line($x + 255, $y + 100 + $h * $l, $x + 500, $y + 100 + $h * $l);

        $pdf->SetFont('arial', '', 14, '', true);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x, $y + 100 + $h * ($l), '* Наш адрес : г.Астана, ул Аманат 2');
        }

        $supportPhoneStr = getSupportPhoneStr($row);
        $pdf->Text($x, $y + 100 + $h * ($l + 1), $supportPhoneStr);

        $l += 1;
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x, $y + 20 + $h * ($l + 0.5), $row['kz_curier']);
        $pdf->Line($x, $y + 330 + $h * ($l + 0.5), $x + 500, $y + 330 + $h * ($l + 0.5));
        if (($row['country'] == 'kz' || $row['country'] == 'KZ') && false) {
            $pdf->SetFont('arial', '', 8, '', true);
            $pdf->Text($x, $y + 142 + $h * $l, 'Поздравляем Вас с переходом на следующий уровень в бонусной программе! Теперь вы участник клуба BRDmarket!');
            $pdf->Text($x, $y + 151 + $h * $l, 'Приглашаем Вас и Ваших близких посетить наш сайт BRDMARKET.COM и ВОСПОЛЬЗОВАТЬСЯ БОНУСНЫМИ СРЕДСТВАМИ в личном кабинете!');
        }

        $y = 50;
        $pdf->StartTransform();
        $pdf->Rotate(90, 250, 590);
        // Левая часть расписки
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->SetXY($x - 10, $y + $h * 22);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->MultiCell(200, 0, "Приложение 1\nк приказу Министра финансов республики Казахстан\nот 20 декабря 2012 года №562\n\nформа КО-1", 0, 'R', 0);
        } elseif ($row['country'] == 'am') {
            $pdf->MultiCell(200, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Республики Армения\n форма КО-1", 0, 'R', 0);
        } else {
            $pdf->MultiCell(200, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Кыргызской Республики\n форма КО-1", 0, 'R', 0);
        }
        $pdf->SetXY($x - 10, $y + $h * 27);
        $pdf->MultiCell(200, 0, 'Организация (ТОО)', 0, 'L', 0);

        $pdf->SetFont('arial', 'U', 8, '', true);
        $pdf->SetXY($x - 10, $y + $h * 28);

        $postavStr = getOtpravitelStr($row);

        $pdf->MultiCell(200, 0, $postavStr, 0, 'C', 0);


        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->SetXY($x - 10, $y + $h * 30);
        $pdf->MultiCell(200, 0, "КВИТАНЦИЯ\nк приходному касовому ордеру", 0, 'C', 0);

        $pdf->Text($x - 10, $y + $h * 32, '№');
        $pdf->Text($x + 10, $y + $h * 32, $row['id']);
        $pdf->Line($x + 5, $y + $h * 33, $x + 190, $y + $h * 33);

        $pdf->Text($x - 10, $y + $h * 33 + 5, 'Принято от');
        $pdf->Text($x - 10, $y + $h * 34 + 5, $row['fio']);
        $pdf->Line($x - 10, $y + $h * 37, $x + 190, $y + $h * 37);

        $pdf->Text($x - 10, $y + $h * 38, 'Сумма');
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x - 10, $y + $h * 39, num2str($row['price']));
        } elseif ($row['country'] == 'am') {
            $pdf->Text($x - 10, $y + $h * 39, num2dram($row['price']));
        } else {
            $pdf->Text($x - 10, $y + $h * 39, num2som($row['price']));
        }
        $pdf->Line($x - 10, $y + $h * 40, $x + 190, $y + $h * 40);

        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Text($x + 10, $y + $h * 40 - 3, 'прописью');

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x - 10, $y + $h * 41, 'М.П.');

        $pdf->Text($x - 10, $y + $h * 43, 'Главный бухгалтер или уполномоченное лицо');
        $pdf->Text($x + 90, $y + $h * 44, 'Не предусмотрен');

        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Line($x + 25, $y + $h * 45, $x + 80, $y + $h * 45);
        $pdf->Text($x + 40, $y + $h * 45, 'подпись');

        $pdf->Line($x + 90, $y + $h * 45, $x + 190, $y + $h * 45);
        $pdf->Text($x + 100, $y + $h * 45, 'расшифровка подписи');

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x - 10, $y + $h * 46, 'Кассир');
        $pdf->Text($x + 90, $y + $h * 46, 'Не предусмотрен');
        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Line($x + 25, $y + $h * 47, $x + 80, $y + $h * 47);
        $pdf->Text($x + 40, $y + $h * 47, 'подпись');

        $pdf->Line($x + 90, $y + $h * 47, $x + 190, $y + $h * 47);
        $pdf->Text($x + 100, $y + $h * 47, 'расшифровка подписи');
    }

    $pdf->Output(dirname(__FILE__) . '/../tmp/send/citydoc_envelope_baribarda_' . $currDate . '.pdf', 'F');

//////////////////////////////////////////////////////////////////////
// СОЗДАЕМ ЧЕТВЕРТЫЙ ФАЙЛ / ZPRINT
    $xmargin = 2;

    // create new PDF document
    $pageLayout = array(58, 73); //  or array($height, $width)
    $pdfZprint = new TCPDF('P', PDF_UNIT, $pageLayout, true, 'UTF-8', false);

    // set document information
    $pdfZprint->SetCreator(PDF_CREATOR);
    $pdfZprint->SetAuthor('Dob');
    $pdfZprint->SetTitle('Baribarda');

    // set default header data
    $pdfZprint->setPrintHeader(false);
    $pdfZprint->setPrintFooter(false);

    // set margins
    $pdfZprint->SetMargins($xmargin, 3, $xmargin);
    $pdfZprint->setFontSubsetting(true);

    // set auto page breaks
    $pdfZprint->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);

    $iter = 1;
    foreach ($envelope_b[md5($courier['city'])] AS $row) {

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
            $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property && $row['offer'] !== 'pullover' ? ' (' . $offer_property . ')' : '') . ' - ' . $row['package'] . 'шт.' . ($row['add_podarok'] > 0 ? ' +подарок' : ''),
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
                        $properties['gift'] = 'подарок';
                        unset($properties['gift_price']);
                    }
                    $str .= ($va === 'pullover') ? '' : ' (' . implode(', ', $properties) . ')';
                }
                $str .= ' - ' . $dop_tovar['dop_tovar_count'][$ke] . 'шт.';
                $tovar[] = $str;
            }
        }

        // add a page
        if ($iter % 2) {
            $pdfZprint->AddPage();
        } else {
            $pdfZprint->Ln(1);
            $pdfZprint->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(204, 204, 204)));
            $pdfZprint->Line($xmargin + 1, $pdfZprint->GetY() + 1, $xmargin + 52, $pdfZprint->GetY() + 1);
            $pdfZprint->Ln(2);
        }
        $pdfZprint->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));


        // set font
        $pdfZprint->SetFont('arial', '', 6);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdfZprint->writeHTMLCell(0, 0, '', '', '<span style="font-weight: bold;">ТОО «KAZECOTRANSIT»</span>', 0, 1, false, true, 'J', false);
            $pdfZprint->writeHTMLCell(0, 0, '', '', '<span style="font-weight: bold;">БИН</span> 180340028283', 0, 1, false, true, 'J', false);
        } elseif ($row['country'] == 'am') {
            $pdfZprint->writeHTMLCell(0, 0, '', '', '<span style="font-weight: bold;">ЧП «Саргсян»</span>', 0, 1, false, true, 'J', false);
        } elseif ($row['country'] == 'kzg') {
            $pdfZprint->writeHTMLCell(0, 0, '', '', '<span style="font-weight: bold;">ИП Дайрбекова Д.</span>', 0, 1, false, true, 'J', false);
        } elseif ($row['country'] == 'uz') {
            $pdfZprint->writeHTMLCell(0, 0, '', '', '<span style="font-weight: bold;">ИП ТОКТАССЫНОВ И.</span>', 0, 1, false, true, 'J', false);
            // сум
        } else {
            $pdfZprint->writeHTMLCell(0, 0, '', '', '<span style="font-weight: bold;">ИП Станбек уулу Б</span>', 0, 1, false, true, 'J', false);
        }
//        print_r($tovar);

        $pdfZprint->writeHTMLCell(0, 0, '', '', "Дата: $tomorrowDate", 0, 1, false, true, 'J', false);
        $pdfZprint->writeHTMLCell(0, 0, '', '', "Продажа #{$row['id']}", 0, 1, false, true, 'J', false);
        $pdfZprint->writeHTMLCell(0, 0, '', '', 'Менеджер: Baribarda', 0, 1, false, true, 'J', false);
        $pdfZprint->writeHTMLCell(0, 0, '', '', $row['fio'], 0, 1, false, true, 'J', false);
        $pdfZprint->writeHTMLCell(0, 0, '', '', implode("\n", $tovar), 0, 1, false, true, 'J', false);
        $sumStr = "Сумма: {$row['price']} ";
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $sumStr .= 'тг.';
        } elseif ($row['country'] == 'am') {
            $sumStr .= 'драм.';
        } elseif ($row['country'] == 'uz') {
            $sumStr .= 'сум.';
        } else {
            $sumStr .= 'сом.';
        }
        $pdfZprint->writeHTMLCell(0, 0, '', '', $sumStr, 0, 1, false, true, 'J', false);
        $pdfZprint->Ln(2);
        $pdfZprint->Line($xmargin + 1, $pdfZprint->GetY() + 1, $xmargin + 52, $pdfZprint->GetY() + 1);
        $pdfZprint->Ln(1);
        $pdfZprint->SetFont('arial', '', 5);
        $pdfZprint->writeHTMLCell(0, 0, '', '', '***************************************************************************', 0, 1, false, true, 'C', false);
        $pdfZprint->writeHTMLCell(0, 0, '', '', 'Адрес. Республика Казахстан, г. Астана ул. Аманат 2', 0, 1, false, true, 'J', false);

        $tehStr = 'Номер поддержки: ';
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $tehStr .= '+7(705)924 03 70';
        } elseif ($row['country'] == 'kzg' or $row['country'] == 'KZG') {
            $tehStr .= '+996770008168, +996770008162, +996770008160';
        }
        $pdfZprint->writeHTMLCell(0, 0, '', '', $tehStr, 0, 1, false, true, 'J', false);
        $iter++;
    }
    $pdfZprint->Output(dirname(__FILE__) . '/../tmp/send/citydoc_zprint_baribarda_' . $currDate . '.pdf', 'F');

//////////////////////////////////////////////////////////////////////
// СОЗДАЕМ ПЯТЫЙ ФАЙЛ / ZPRINT

    $fp = fopen(dirname(__FILE__) . '/../tmp/send/citydoc_zprint_baribarda_' . $currDate . '.txt', 'w');

    $iter -= $iter;
    foreach ($envelope_b[md5($courier['city'])] AS $row) {

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
            $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property && $row['offer'] !== 'pullover' ? ' (' . $offer_property . ')' : '') . ' - ' . $row['package'] . 'шт.' . ($row['add_podarok'] > 0 ? ' +подарок' : ''),
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
                        $properties['gift'] = 'подарок';
                        unset($properties['gift_price']);
                    }
                    $str .= ($va === 'pullover') ? '' : ' (' . implode(', ', $properties) . ')';
                }
                $str .= ' - ' . $dop_tovar['dop_tovar_count'][$ke] . 'шт.';
                $tovar[] = $str;
            }
        }

        // add a page
        if ($iter) {
            fwrite($fp, PHP_EOL);
            fwrite($fp, '--------------------' . PHP_EOL);
            fwrite($fp, PHP_EOL);
        }

        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            fwrite($fp, 'ТОО «KAZECOTRANSIT»' . PHP_EOL);
            fwrite($fp, 'БИН 180340028283' . PHP_EOL);
        } elseif ($row['country'] == 'am') {
            fwrite($fp, 'ЧП «Саргсян»' . PHP_EOL);
        } elseif ($row['country'] == 'kzg') {
            fwrite($fp, 'ИП Дайрбекова Д.' . PHP_EOL);
        } elseif ($row['country'] == 'uz') {
            fwrite($fp, 'ИП ТОКТАССЫНОВ И.' . PHP_EOL);
        } else {
            fwrite($fp, 'ИП Станбек уулу Б' . PHP_EOL);
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
        } elseif ($row['country'] == 'uz') {
            $sumStr .= 'сум.';
        } else {
            $sumStr .= 'сом.';
        }
        fwrite($fp, $sumStr . PHP_EOL);
        fwrite($fp, '********************' . PHP_EOL);
        fwrite($fp, 'Адрес. Республика Казахстан,' . PHP_EOL);
        fwrite($fp, 'г. Астана ул. Аманат 2' . PHP_EOL);

        $tehStr = 'Номер поддержки: ';
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $tehStr .= '+7(705)924 03 70';
        } elseif ($row['country'] == 'kzg' or $row['country'] == 'KZG') {
            $tehStr .= '+996770008168, +996770008162, +996770008160';
        }
        fwrite($fp, $tehStr . PHP_EOL);
        $iter++;
    }
    fclose($fp);

//////////////////////////////////////////////////////////////////////
// ОТПРАВКА ПИСЬМА
    try {
        $newSubject = 'Документы админу ' . $courier['city'] . ' за ' . date('Y-m-d');
        $newBody = 'Отчет';

        $newAttachmentArr[] = dirname(__FILE__) . '/../tmp/send/citydoc_orders_baribarda_' . $currDate . '.xls';
        $newAttachmentArr[] = dirname(__FILE__) . '/../tmp/send/citydoc_packing_baribarda_' . $currDate . '.xls';
        $newAttachmentArr[] = dirname(__FILE__) . '/../tmp/send/citydoc_envelope_baribarda_' . $currDate . '.pdf';
        $newAttachmentArr[] = dirname(__FILE__) . '/../tmp/send/citydoc_zprint_baribarda_' . $currDate . '.pdf';
        $newAttachmentArr[] = dirname(__FILE__) . '/../tmp/send/citydoc_zprint_baribarda_' . $currDate . '.txt';

        $newAddressArr[] = $courier['email'];
        $newAddressArr[] = 'tovarykzz@mail.ru';
/////////////////////////////////////////////////////////
//        $newAddressArr = array();
//        $newAddressArr[] = 'sdobrovol@gmail.com';
//        var_dump($newSubject); die;
        $se = DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName);

        if ($se) {
            $result['data'][] = "Письмо для курьера {$courier['city']} отправлено на E-Mail {$courier['email']}";
        } else {
            $result['data'][] = "Ошибка отправки письма для курьера {$courier['city']} на E-Mail {$courier['email']}";
        }
        $actionHistoryObj = new ActionHistoryObj();
        $actionHistoryObj->save('EmailObj', $_SESSION['Logged_StaffId'], 'insert', 'sendmail', '', $courier['email']);
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $result['data'][] = $e->errorMessage();
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $result['data'][] = $e->getMessage();
    }
}

//
// Показать результат
print json_encode($result);

