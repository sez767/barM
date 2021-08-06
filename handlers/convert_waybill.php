<?php

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

if (!isset($_SESSION['Logged_StaffId']) && $_REQUEST['s'] != '1q2w3e4r') {
    header("location: /login.html");
    die;
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

$offersPhotoAss = DB::queryAssData('offer_name', 'offer_photo', 'SELECT offer_name, offer_photo FROM offers WHERE char_length(offer_photo) > 0');

$zarodh_ar = array('Араван Почта', 'Баткен курьер', 'Исфана курьер', 'Кадамжай почта', 'Каракуль почта', 'Карасу курьер', 'Кызыл-кия курьер',
    'Ноокат Почта', 'Ош курьер', 'Таш-Кумыр курьер', 'Токтогул почта', 'Узген', 'Джалал-Абад курьер', 'Базаркоргон Почта', 'Массы почта',
    'Кочкор ата Почта', 'Майлуу-Суу почта', 'Сузак почта', 'Сулюкта почта');
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
                o.`offer_photo`,
                IF (staff_id NOT IN (22222222, 33333333) AND country = 'false' AND CURDATE() + INTERVAL 1 DAY <= `date_delivery_first`, 1, 0) AS add_podarok
	FROM
		`staff_order` AS so
            LEFT JOIN offers AS o ON o.offer_name = so.offer
	WHERE
		#`send_status` = 'Отправлен' AND
                #`status_kz` IN ('На доставку', 'Вручить подарок') AND
                #DATE_FORMAT(`date_delivery`,'%Y-%m-%d') = CURDATE() + INTERVAL 1 DAY  AND
		id IN (" . implode(',', $ids) . ")
	ORDER BY FIELD(`id`, " . implode(',', $ids) . ')';

ApiLogger::addLogJson($sql);

$arr_b = DB::query($sql);

if (empty($arr_b)) {
    die;
}

// создаем третий файл / конверт
$x = 25;
$y = 50;
$h = 15;
$l = 1;

$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'px', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins($x, $y, $x);
$pdf->setFontSubsetting(true);

foreach ($arr_b AS $row) {

    $allPotos = array();
    if (array_key_exists($row['offer'], $offersPhotoAss)) {
        $allPotos[] = $offersPhotoAss[$row['offer']];
    }
    $other_data = json_decode($row['other_data'], true);

    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }

    $offer_property = NULL;
    if (count($other_data) > 0) {
        $offer_property = implode(", ", (array) $other_data);
    }

        $pdf->AddPage();

    // Нижние разделители (отрезные линии)
    $pdf->SetLineStyle(array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,5,2,5', 'phase' => 10, 'color' => array(0, 0, 0)));

    // Стандартное оформление линий
    $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

    // Товарная накладная
    $pdf->SetFont('arial', 'B', 14, '', true);
    //$pdf->Text($x, $y, "Товарная накладная № " . $row['id'] . " от " . currentFullDate() . "г.");
    $pdf->Text($x, $y, "Товарная накладная № {$row['id']} ");
    $pdf->Line($x, $y + $h + 5, $x + 550, $y + $h + 5);

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
    $pdf->Text($x, $y + $h * $l, "Поставщик");

    $pdf->SetFont('arial', '', 12, '', true);
    $pdf->SetXY($x + 200, $y + $h * $l);

    // Отправитель
    $otpravitelStr = getOtpravitelStr($row);

    $pdf->MultiCell(0, 0, $otpravitelStr, 0, 'L', 0);


    $pdf->SetFont('arial', '', 12, '', true);
    $l += 2;

    // Строка с получателем
    $pdf->SetFont('arial', '', 10, '', true);
    $pdf->Text($x, $y + $h * $l, "Покупатель");

    $pdf->SetFont('arial', '', 12, '', true);
    $pdf->Text($x + 200, $y + $h * $l, $row['fio']);

    // Таблица с товаром

    $l += 2;
    $pdf->SetXY($x, $y + $h * $l);

    // Заголовки таблицы
    $pdf->SetFont('arial', '', 12, '', true);
    $pdf->setCellPaddings(3, 3, 3, 3);
    $pdf->Cell(100, 0, 'Номер заказа', 1, 0, 'C', 0);
    $pdf->Cell(350, 0, 'Товар', 1, 0, 'C', 0);
    $pdf->Cell(100, 0, 'Сумма', 1, 0, 'C', 0);

    $pdf->Ln();

    $tovar = array(
        $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property && $row['offer'] !== 'pullover' ? " (" . $offer_property . ")" : "") . " - " . $row['package'] . "шт." . ($row['add_podarok'] > 0 ? ' +подарок' : ''),
    );

    // additional goods
    $dop_tovar = json_decode($row['dop_tovar'], true);
    $dop_tovar_all = array();

    if (json_last_error() == JSON_ERROR_NONE && is_array($dop_tovar['dop_tovar'])) {

        foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {

            if (array_key_exists($va, $offersPhotoAss)) {
                $allPotos[] = $offersPhotoAss[$va];
            }

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
                    $properties['gift'] = 'подарок';
                    unset($properties['gift_price']);
                }

                $str .= ($va === 'pullover') ? '' : ' (' . implode(', ', $properties) . ')';
            }
            $str .= " - {$dop_tovar['dop_tovar_count'][$ke]}шт.";

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
    $l += (count($tovar) + 2);

    $pdf->SetFont('arial', '', 12, '', true);
    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
        $pdf->Text($x, $y + 100 + $h * $l, num2str($row['price']));
    } elseif ($row['country'] == 'am') {
        $pdf->Text($x, $y + 100 + $h * $l, num2dram($row['price']));
    } elseif ($row['country'] == 'uz') {
        $pdf->Text($x, $y + 100 + $h * $l, num2sum($row['price']));
    } else {
        $pdf->Text($x, $y + 100 + $h * $l, num2som($row['price']));
    }

    $l += 3;

    // Отпустил
    $pdf->SetFont('arial', '', 12, '', true);
    $pdf->Text($x, $y + 100 + $h * $l, 'Отпустил');
    // Получил
    $pdf->Text($x + 200, $y + 100 + $h * ($l), 'Получил');
    $l += 1;

    $pdf->Line($x + 255, $y + 100 + $h * $l, $x + 500, $y + 100 + $h * $l);
    $pdf->Line($x + 60, $y + 100 + $h * $l, $x + 190, $y + 100 + $h * $l);

    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->Text($x + 80, $y + 100 + $h * ($l), $GLOBAL_KZ_COURIERS_ASS[$row['kz_curier']]['name']);
    $l += 2;

    $pdf->SetFont('arial', '', 14, '', true);
    if (($ourAddressStr = getOurAddressStr($row))) {
        $pdf->Text($x, $y + 100 + $h * ($l), "* Наш адрес : $ourAddressStr");
        $l += 1;
    }
    $supportPhoneStr = getSupportPhoneStr($row);
    $pdf->Text($x, $y + 100 + $h * ($l), $supportPhoneStr);
    $l += 1;

    if (in_array($row['offer'], $GLOBAL_ORIGINAL_PARFUME_ARR)) {
        $l += 1;
        $pdf->SetFont('arial', 'U', 10, '', true);
        $pdf->Text($x, $y + 100 + $h * ($l + 1), 'https://elite-parfume.kz/');
        $l += 1;
    }

    if (($row['country'] == 'kz' or $row['country'] == 'KZ') && false) {
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x, $y + 142 + $h * $l, "Поздравляем Вас с переходом на следующий уровень в бонусной программе! Теперь вы участник клуба BRDmarket!");
        $pdf->Text($x, $y + 151 + $h * $l, "Приглашаем Вас и Ваших близких посетить наш сайт BRDMARKET.COM и ВОСПОЛЬЗОВАТЬСЯ БОНУСНЫМИ СРЕДСТВАМИ в личном кабинете!");
        $l += 1;
    }
    $y = 50;

    $pdf->Line($x, $y + 200 + $h * ($l), $x + 500, $y + 200 + $h * ($l));

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
    $pdf->MultiCell(200, 0, "Организация (ТОО)", 0, 'L', 0);

    $pdf->SetFont('arial', 'U', 8, '', true);
    $pdf->SetXY($x - 10, $y + $h * 28);

    $pdf->MultiCell(200, 0, $otpravitelStr, 0, 'C', 0);

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
    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
        $pdf->Text($x - 10, $y + $h * 39, num2str($row['price']));
    } elseif ($row['country'] == 'am') {
        $pdf->Text($x - 10, $y + $h * 39, num2dram($row['price']));
    } elseif ($row['country'] == 'uz') {
        $pdf->Text($x - 10, $y + $h * 39, num2sum($row['price']));
    } else {
        $pdf->Text($x - 10, $y + $h * 39, num2som($row['price']));
    }
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

    if (!empty($allPotos) && $row['country'] != 'kzg' && true) {
        $allPotos = array_unique($allPotos);
        foreach ($allPotos as $photoItem) {
                        $pdf->AddPage();
            $pdf->Image(__DIR__ . "/../photos/product/$photoItem", '', '', $pdf->getPageWidth(), 0, '', '', 'M');
        }
    }
}

$pdf->Output(dirname(__FILE__) . '/../tmp/waybill.pdf', 'FI');
/////////////////////////////////////////////////////////////////////
// ОТПРАВКА ПИСЬМА
if (strlen($_REQUEST['email'])) {
    try {
        $newSubject = 'Товарная накладная ' . date('Y-m-d');
        $newBody = 'Товарная накладная';

        $newAttachmentArr[] = dirname(__FILE__) . '/../tmp/waybill.pdf';

        $newAddressArr[] = $_REQUEST['email'];

        $se = DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName);

        $actionHistoryObj = new ActionHistoryObj();
        $actionHistoryObj->save('EmailObj', $_SESSION['Logged_StaffId'], 'insert', 'sendmail', '', $courier['email']);
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $result['data'][] = $e->errorMessage();
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $result['data'][] = $e->getMessage();
    }
}