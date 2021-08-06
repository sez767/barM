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
		`uuid`,
		`ext_id`,
		 CONCAT('bar',id) as bid,
		`fio`,
		`last_edit`,
		if(country != 'kzg',`phone`,'') as phone,
		`total_price` AS price,
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
	WHERE id IN (" . implode(',', $ids) . ")
	GROUP BY id
	ORDER BY `kz_delivery`";
//	ORDER BY FIELD(`id`, " . implode(',', $ids) . ')';

ApiLogger::addLogJson($sql);

$arr_b = DB::query($sql);

if (empty($arr_b)) {
    die;
}


$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'px', 'A4', true, 'UTF-8', false);
//$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);


$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT - 5, PDF_MARGIN_TOP - 5, PDF_MARGIN_RIGHT - 5);

//// set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$currDate = date('Y-m-d');

foreach ($arr_b AS $row) {

    if ($_SESSION['session_admincity'] && $row['send_status'] == 'Оплачен' && $row['status_kz'] == 'Сделать замену' && $row['status_cur'] == 'сделать замену') {
        $row['offer'] = '';
        $row['package'] = 0;
        $row['price'] = 0;
        $row['dop_tovar'] = '[]';
        $row['other_data'] = '[]';
    }


    $pdf->SetFont('arial', '', 11, '', true);

    $allPotos = array();
    if (array_key_exists($row['offer'], $offersPhotoAss)) {
        $allPotos[] = $offersPhotoAss[$row['offer']];
    }

    $postavStr = getOtpravitelStr($row);
    $ourAddressStr = nl2br(getOurAddressStr($row));

    
    $pdf->AddPage();

    $other_data = json_decode($row['other_data'], true);

    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }

    $offer_property = NULL;
    if (count($other_data) > 0) {
        $offer_property = implode(", ", (array) $other_data);
    }



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

    $barcode = '/tmp/barcodes/' . $row['id'] . '_barcode.png';

    $html = <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    </head>
    <body>



        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tbody>
                <tr>
                    <td style="background-color:#fff; text-align:center; vertical-align:middle; width:20%">
                        <p><strong><span style="font-size:16px"><span style="color:#000">Товарная<br />
                                        Накладная</span></span></strong></p>
                    </td>
                    <td rowspan="3" style="width:80%">
                        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                            <tbody>
                                <tr>
                                    <td colspan="1" rowspan="3" style="text-align:center; width:50%"><img alt="" src="http://baribarda.com/images/common/logo-text.png" style="float:left; height:110px; width:100px" /></td>
                                    <td colspan="2" rowspan="2" style="width:25%">&nbsp;</td>
                                    <td style="text-align:right; width:25%">
                                        <div style="border-bottom:2px solid #d6d6d6; text-align:center"><span style="font-size:12px;">$currDate</span></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:right">
                                        <div style="border-bottom:2px solid #d6d6d6; text-align:center"><span style="font-size:12px">Накладная № {$row['id']}</span></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td colspan="3" style="text-align:center"><img alt="" src="$barcode" style="height:42px; width:200px" /></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="border-bottom:2px solid #d6d6d6"><span style="font-size:13px"><strong><span style="color:#387bb6">Покупатель</span></strong></span></div>
                                    </td>
                                    <td colspan="3">
                                        <div style="border-bottom:2px solid #d6d6d6"><span style="font-size:13px"><strong><span style="color:#387bb6">Поставщик</span></strong></span></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{$row['fio']}</td>
                                    <td colspan="3"><span style="font-size:12px">$postavStr</span></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td colspan="3">
                                        <div style="border-bottom:2px solid #d6d6d6"><span style="font-size:13px"><span style="color:#387bb6"><strong>Внутренний номер</strong></span></span></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td colspan="3"><span style="font-size:12px">{$GLOBAL_STAFF_SIP[$row['last_edit']]}</span></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="background-color:#1155cc; border:1px solid #d6d6d6; text-align:center"><span style="font-size:12px"><strong><span style="color:#ffffff">Наименование</span></strong></span></td>
                                    <td colspan="2" style="background-color:#1155cc; border:1px solid #d6d6d6; text-align:center"><span style="font-size:12px"><strong><span style="color:#ffffff">Кол-во</span></strong></span></td>
                                </tr>
EOF;

    $ii = $globalTotal = 0;
    $pricesDataArr = array();
    $onePrice = gerOfferPrice($row);
    $total = $onePrice * $row['package'];
    $globalTotal +=$total;


    $pricesDataArr[$ii] = array(
        'offer' => $GLOBAL_OFFER_DESC[$row['offer']] . ($offer_property && $row['offer'] !== 'pullover' ? " (" . $offer_property . ")" : "") . " - " . $row['package'] . "шт." . ($row['add_podarok'] > 0 ? ' +подарок' : ''),
        'price' => $onePrice,
        'price_format' => number_format($onePrice, 2, '.', ' '),
        'count' => $row['package'],
        'total' => $globalTotal,
        'total_format' => number_format($globalTotal, 2, '.', ' ')
    );

    $dopStyle = 'border:1px solid #d6d6d6;' . ($ii % 2 > 0 ? ' background-color:#f3f3f3;' : '');
    $html .= <<<EOF
                                <tr>
                                    <td colspan="2" style="$dopStyle">{$pricesDataArr[$ii]['offer']}</td>
                                    <td colspan="2" style="$dopStyle text-align:center">{$pricesDataArr[$ii]['count']}</td>
                                </tr>
EOF;

    // additional goods
    $dop_tovar = json_decode($row['dop_tovar'], true);
    $dop_tovar_all = array();

    if (json_last_error() == JSON_ERROR_NONE && is_array($dop_tovar['dop_tovar'])) {

        foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {
            $ii++;

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

            $dop_tovar_all[] = $str;
            $tovar[] = implode('; ', $dop_tovar_all);


            $tmpArr = $row;
            $tmpArr['offer'] = $dop_tovar['dop_tovar'][$ke];
            $onePrice = gerOfferPrice($tmpArr);
            $total = $onePrice * $dop_tovar['dop_tovar_count'][$ke];
            $globalTotal += $total;

            $pricesDataArr[$ii] = array(
                'offer' => $str,
                'price' => $onePrice,
                'price_format' => number_format($onePrice, 2, '.', ' '),
                'count' => $dop_tovar['dop_tovar_count'][$ke],
                'total' => $total,
                'total_format' => number_format($total, 2, '.', ' ')
            );

            $dopStyle = 'border:1px solid #d6d6d6;' . ($ii % 2 > 0 ? ' background-color:#f3f3f3;' : '');
            $html .= <<<EOF
                                <tr>
                                    <td colspan="2" style="$dopStyle">{$pricesDataArr[$ii]['offer']}</td>
                                    <td colspan="2" style="$dopStyle text-align:center">{$pricesDataArr[$ii]['count']}</td>
                                </tr>
EOF;
        }
    }

    $commonTotal = number_format($globalTotal, 2, '.', ' ');
    $commonSkidka = number_format($globalTotal - $row['price'], 2, '.', ' ');
    $commonFinal = number_format($row['price'], 2, '.', ' ');
    $supportPhoneStr = getSupportPhoneStr($row);
    $html .= <<<EOF
                                <tr>
                                    <td colspan="2" style="text-align:right"><span style="font-size:12px"><strong>Итого к оплате</strong></span></td>
                                    <td colspan="2" style="border-top:1px solid #000; border-bottom:1px solid #000; background-color:#cfe2f3; text-align:center"><span style="font-size:12px"><strong>$commonFinal&nbsp;&#8376;</strong></span></td>
                                </tr>
                                <tr>
                                    <td colspan="4">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td colspan="4"><em>$supportPhoneStr</em></td>
                                </tr>
                                <tr>
                                    <td colspan="4">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>Курьер: {$GLOBAL_KZ_COURIERS_ASS[$row['kz_curier']]['id']}</td>
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="border-top:1px solid #000000; font-size:10px; width: 200px;">Отпустил</div>
                                    </td>
                                    <td colspan="3">
                                        <div style="border-top:1px solid #000000; width:80%; font-size:10px;">Получил</div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="4"><p><strong>KBT group!!!</strong><br/>
Өнімді достарыңызға, әріптестеріңізге, туыстарыңызға ұсыныңыз және олардың төленген тапсырысынан, барабанға ай сайынғы автокөлік ұтысына қосымша 3 билет алыңыз.
Қосымша ақпарат алу үшін 2442 қысқа нөміріне хабарласыңыз</p>
<p>Ваш промо-код: <strong>{$row['uuid']}</strong></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="1" style="background-color:#fff; text-align:center; vertical-align:top">
                        <p>&nbsp;</p>

                        <p><span style="font-size:12px">$postavStr<br />
                                $ourAddressStr</span></p>

                        <p><span style="font-size:10px">
                            <a href="https://kbt-store.com/" target="_blank">https://kbt-store.com/</a>
                                </span></p>

                        <p>&nbsp;</p>
                    </td>
                </tr>
                <tr>
                    &nbsp;
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center; vertical-align:top">&nbsp;</td>
                </tr>
            </tbody>
        </table>


    </body>
</html>

EOF;

//    die($html);

    $pdf->writeHTML($html, true, false, false, false, '');
/////////////////////
    $pdf->StartTransform();
    $pdf->Rotate(90, 0, 500);
    $pdf->SetFont('arial', '', 12, '', true);
    $pdf->MultiCell(150, 0, "Промо-код: {$row['uuid']}", 0, 'L', 0);
    $pdf->StopTransform();
/////////////////////
    $l = 20;
    $x = 35;
    $y = 50;
    $h = 15;

    $pdf->Line($x, $y + 220 + $h * $l, $x + 500, $y + 220 + $h * $l);

    $pdf->StartTransform();
    $pdf->Rotate(90, 250, 590);
    // Левая часть расписки
    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->SetXY($x, $y + $h * 22);
    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
        $pdf->MultiCell(200, 0, "Приложение 1\nк приказу Министра финансов республики Казахстан\nот 20 декабря 2012 года № 562\n\nформа КО-1", 0, 'R', 0);
    } elseif ($row['country'] == 'am') {
        $pdf->MultiCell(200, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Республики Армения\n форма КО-1", 0, 'R', 0);
    } else {
        $pdf->MultiCell(200, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Кыргызской Республики\n форма КО-1", 0, 'R', 0);
    }
    $pdf->SetXY($x, $y + $h * 27);
    $pdf->MultiCell(200, 0, "Организация", 0, 'L', 0);

    $pdf->SetFont('arial', 'U', 8, '', true);
    $pdf->SetXY($x, $y + $h * 28);

    $pdf->MultiCell(200, 0, $postavStr, 0, 'C', 0);

    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->SetXY($x, $y + $h * 30);
    $pdf->MultiCell(200, 0, "КВИТАНЦИЯ\nк приходному касовому ордеру", 0, 'C', 0);

    $pdf->SetFont('arial', 'U', 8, '', true);
    $pdf->Text($x, $y + $h * 32, "№ {$row['id']}");
    $pdf->Line($x + 5, $y + $h * 33, $x + 220, $y + $h * 33);

    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->Text($x, $y + $h * 33 + 5, "Принято от");
    $pdf->Text($x, $y + $h * 34 + 5, $row['fio']);
    $pdf->Line($x, $y + $h * 37, $x + 220, $y + $h * 37);

    $pdf->Text($x, $y + $h * 38, "Сумма");
    if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
        $pdf->Text($x, $y + $h * 39, num2str($row['price']));
    } elseif ($row['country'] == 'am') {
        $pdf->Text($x, $y + $h * 39, num2dram($row['price']));
    } elseif ($row['country'] == 'uz') {
        $pdf->Text($x, $y + $h * 39, num2sum($row['price']));
    } else {
        $pdf->Text($x, $y + $h * 39, num2som($row['price']));
    }
    $pdf->Line($x, $y + $h * 40, $x + 220, $y + $h * 40);

    $pdf->SetFont('arial', '', 6, '', true);
    $pdf->Text($x + 40, $y + $h * 40, "прописью");

    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->Text($x, $y + $h * 41, "М.П.");
    //$pdf->Text($x + 30, $y + $h * 41, date('d.m.Y') . " года");

    $pdf->Text($x, $y + $h * 43, "Главный бухгалтер или уполномоченное лицо");
    $pdf->Text($x + 90, $y + $h * 44, "Не предусмотрен");

    $pdf->SetFont('arial', '', 6, '', true);
    $pdf->Line($x + 25, $y + $h * 45, $x + 80, $y + $h * 45);
    $pdf->Text($x + 40, $y + $h * 45, "подпись");

    $pdf->Line($x + 90, $y + $h * 45, $x + 220, $y + $h * 45);
    $pdf->Text($x + 100, $y + $h * 45, "расшифровка подписи");

    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->Text($x, $y + $h * 46, "Кассир");
    $pdf->Text($x + 90, $y + $h * 46, "Не предусмотрен");
    $pdf->SetFont('arial', '', 6, '', true);
    $pdf->Line($x + 25, $y + $h * 47, $x + 80, $y + $h * 47);
    $pdf->Text($x + 40, $y + $h * 47, "подпись");

    $pdf->Line($x + 90, $y + $h * 47, $x + 220, $y + $h * 47);
    $pdf->Text($x + 100, $y + $h * 47, "расшифровка подписи");


    if (!empty($allPotos) && $row['country'] != 'kzg' && true) {
        $allPotos = array_unique($allPotos);
        foreach ($allPotos as $photoItem) {
            
            $pdf->AddPage();
            $pdf->Image(__DIR__ . "/../photos/product/$photoItem", '', '', $pdf->getPageWidth(), 0, '', '', 'M');
        }
    }
}


$pdf->Output(dirname(__FILE__) . '/../tmp/waybill-kbt.pdf', 'FI');
/////////////////////////////////////////////////////////////////////

