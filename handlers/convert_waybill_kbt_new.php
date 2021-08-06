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

$uuidArr = array();
foreach ($arr_b as $item) {
    $uuidArr[$item['id']] = $item['uuid'];
}
//print_r($uuidArr);die;
$payedCount = DB::queryAssData('uuid', 'count', 'SELECT uuid, IF (order_payed_2020_count >= 11, 12, order_payed_2020_count + 1) AS count FROM coffee.Clients WHERE uuid IN %ls', $uuidArr);
$payedTotal = DB::queryAssData('uuid', 'order_payed_2020_total', 'SELECT uuid, order_payed_2020_total FROM coffee.Clients WHERE uuid IN %ls', $uuidArr);

$biletCount = getKonkusBiletCount($arr_b, true);
//print_r($biletCount);
//die;
//foreach ($payedTotal as $key => &$pt2020) {
//    $pt2020 = ceil($pt2020 / 10000);
//}
//print_r($payedTotal);die;

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


    $heartCountArr = array();
    for ($pii = 1; $pii <= 12; $pii++) {
        if ($pii <= $payedCount[$row['uuid']]) {
            $heartCountArr[$pii] = '<td><img alt="" src="/images/KBTStore-heart-1.png" style="height:12px; width:12px" /></td>';
        } else {
            $heartCountArr[$pii] = "<td>&nbsp;</td>";
        }
    }

    $payedTotalValue = $payedTotal[$row['uuid']] + $row['price'];
    $payedTotalHeartCount = floor(($payedTotalValue) / 10000) > 12 ? 12 : floor(($payedTotalValue) / 10000);

    $heartTotalArr = array();
    for ($pii = 1; $pii <= 12; $pii++) {

        if ($pii <= $payedTotalHeartCount) {
            $heartTotalArr[$pii] = '<td><img alt="" src="/images/KBTStore-heart-1.png" style="height:12px; width:12px" /></td>';
        } else {
            $heartTotalArr[$pii] = "<td>&nbsp;</td>";
        }
    }

//    print_r($heartCountArr);
//    die;

    $heartCountStr = implode(' ', $heartCountArr);
    $heartTotalStr = implode(' ', $heartTotalArr);


    if ($row['country'] == 'kz') {
        $rozigrashStr = '<tr>
                            <td colspan="4"><span style="font-size:14px"><strong>Соверши 12 покупок, получи 12 сердец и участвуй весь 2021 год , 52 понедельника в розыгрышах 1 миллиона тенге!</strong></span></td>
                        </tr>
                        <tr>
                            <td colspan="4"><span style="font-size:14px"><strong>12 рет зат сатып алып, 12 жүрекше ал жане 2021 жыл бойы, 52 дүйсенбі 1 миллион тенге ұтыс ойынына қатыс.</strong></span></td>
                        </tr>';
    } else if ($row['country'] == 'kzg') {
        $rozigrashStr = '<tr>
                            <td colspan="4"><span style="font-size:14px"><strong>Соверши 12 покупок, получи 12 сердец и участвуй весь 2021 год , 52 понедельника в розыгрышах 100 000 сом!</strong></span></td>
                        </tr>
                        <tr>
                            <td colspan="4"><span style="font-size:14px"><strong>12 жолу заказ кылып 12 журокчого ээ болуп 2021 жылы , 52  жолу ар дуйшонбудо толугу менен 100 000 сом жана 25 бытовой техника ойнотулуучу  розыгрышка катышыныздар.</strong></span></td>
                        </tr>';
    } else {
        $rozigrashStr = '';
    }

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
                    <td style="background-color:#fff; text-align:center; vertical-align:middle; width:40%">&nbsp;</td>
                    <td rowspan="3" style="width:60%">
                        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                            <tbody>
                                <tr>
                                    <td colspan="4" style="text-align:right;"><img alt="" src="$barcode" style="height:30px; width:200px" /></td>
                                </tr>
                                <tr>
                                    <td colspan="1" style="text-align:center; width:30%">
                                        <br/><br/><br/><br/>
                                    </td>
                                    <td colspan="1" style="text-align:center; width:30%">&nbsp;</td>
                                    <td colspan="1" style="text-align:center; width:20%">&nbsp;</td>
                                    <td colspan="1" style="text-align:center; width:20%">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="4">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="4">Участвует после оплаты в розыгрыше
EOF;

    $faqStr = '(1 сердечко = 10000)';
    if ($row['country'] == 'kz') {
        $html .= ' 1 МИЛЛИОНА ТЕНГЕ';
        $faqStr = '(1 сердечко = 10000 тенге)';
    }
    $html .= <<<EOF
                . Дополнительной регистрации НЕ ТРЕБУЕТСЯ</td>
                                </tr>
                                <tr>
                                    <td colspan="4">&nbsp;</td>
                                </tr>
EOF;
    if (true) {
        $html .= <<<EOF
                                <tr>
                                    <td colspan="4"><em>Количество покупок за 2020 год</em></td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <table border="1" width="100%" align="center"  style="padding:3px">
                                            <tr>
                                                <td style="font-size:10px;"><strong>1</strong></td>
                                                <td style="font-size:10px;"><strong>2</strong></td>
                                                <td style="font-size:10px;"><strong>3</strong></td>
                                                <td style="font-size:10px;"><strong>4</strong></td>
                                                <td style="font-size:10px;"><strong>5</strong></td>
                                                <td style="font-size:10px;"><strong>6</strong></td>
                                                <td style="font-size:10px;"><strong>7</strong></td>
                                                <td style="font-size:10px;"><strong>8</strong></td>
                                                <td style="font-size:10px;"><strong>9</strong></td>
                                                <td style="font-size:10px;"><strong>10</strong></td>
                                                <td style="font-size:10px;"><strong>11</strong></td>
                                                <td style="font-size:10px;"><strong>12</strong></td>
                                            </tr>
                                            <tr>
                                                {$heartCountStr}
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" colspan="4"><em>(1 сердечко = 1 покупка)</em></td>
                                </tr>
                                $rozigrashStr
EOF;
    }
    if (false) {
        $html .= <<<EOF
                                <tr>
                                    <td colspan="4"><em>Сумма покупок за 2020 год</em></td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <table border="1" width="100%" align="center"  style="padding:3px">
                                            <tr>
                                                <td style="font-size:10px;"><strong>1</strong></td>
                                                <td style="font-size:10px;"><strong>2</strong></td>
                                                <td style="font-size:10px;"><strong>3</strong></td>
                                                <td style="font-size:10px;"><strong>4</strong></td>
                                                <td style="font-size:10px;"><strong>5</strong></td>
                                                <td style="font-size:10px;"><strong>6</strong></td>
                                                <td style="font-size:10px;"><strong>7</strong></td>
                                                <td style="font-size:10px;"><strong>8</strong></td>
                                                <td style="font-size:10px;"><strong>9</strong></td>
                                                <td style="font-size:10px;"><strong>10</strong></td>
                                                <td style="font-size:10px;"><strong>11</strong></td>
                                                <td style="font-size:10px;"><strong>12</strong></td>
                                            </tr>
                                            <tr>
                                                {$heartTotalStr}
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" colspan="4"><em>{$faqStr}</em></td>
                                </tr>
EOF;
    }
    $html .= <<<EOF
                                <tr>
                                    <td colspan="2">
                                        <div style="border-bottom:2px solid #d6d6d6; text-align:center"><span style="font-size:12px;">$currDate</span></div>
                                    </td>
                                    <td colspan="2">
                                        <div style="border-bottom:2px solid #d6d6d6; text-align:center"><span style="font-size:12px">Накладная № <strong><span style="font-size: 16px;">{$row['id']}</strong></span></span></div>
                                        <div style="text-align:center"><span style="font-size:10px">данный номер участвует в розыгрыше</span></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div style="border-bottom:2px solid #d6d6d6"><span style="font-size:13px"><strong><span style="color:#387bb6">Покупатель</span></strong></span></div>
                                    </td>
                                    <td colspan="2">
                                        <div style="border-bottom:2px solid #d6d6d6"><span style="font-size:13px"><strong><span style="color:#387bb6">Поставщик</span></strong></span></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">{$row['fio']}</td>
                                    <td colspan="2"><span style="font-size:12px">$postavStr</span></td>
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


    if ($row['package'] > 0) {
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
    }

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

            $str = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] ? $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] : $dop_tovar['dop_tovar'][$ke];

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


//            print_r($dop_tovar);
//            echo $str;
//            die;


            if ($dop_tovar['dop_tovar_count'][$ke] > 0) {
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
    }

    $commonTotal = number_format($globalTotal, 2, '.', ' ');
    $commonSkidka = number_format($globalTotal - $row['price'], 2, '.', ' ');
    $commonFinal = number_format($row['price'], 2, '.', ' ');
    $supportPhoneStr = str_replace('заботы', 'заботы<br/>', getSupportPhoneStr($row));
    $supportPhoneStr = str_replace('тел', '<br/>тел', $supportPhoneStr);


    $html .= <<<EOF
                                <tr>
                                    <td colspan="2" style="text-align:right"><span style="font-size:12px"><strong>Итого к оплате</strong></span></td>
                                    <td colspan="2" style="border-top:1px solid #000; border-bottom:1px solid #000; background-color:#cfe2f3; text-align:center"><span style="font-size:12px"><strong>$commonFinal&nbsp;&#8376;</strong></span></td>
                                </tr>
                                <tr>
                                    <td colspan="4">&nbsp;</td>
                                </tr>
EOF;
    if ($row['country'] != 'kzg' && false) {
        $html .= <<<EOF
                                <tr>
                                    <td colspan="4"><p><strong>KBT group!!!</strong><br/>
Рекомендуйте продукт своим друзьям, коллегам, родственникам и получайте от их оплаченного заказа ДОПОЛНИТЕЛЬНО 3 билета в барабан на ежемесячный розыгрыш Автомобиля</p>
<p>За дополнительной информацией обращайтесь по короткому мобильному номеру 2442</p>
<p>Ваш промо-код: <strong><span style="font-size: 16px;background-color:#00BFFF;;border: 1px solid black;">{$row['uuid']}</span></strong></p>
                                    </td>
                                </tr>
EOF;
    }
    $html .= <<<EOF
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="1" style="background-color:#fff; text-align:center; vertical-align:top;">

                        <p><span style="font-size:12px">$postavStr<br />
                                $ourAddressStr</span>
                        </p>
                        <p>
                            <span style="font-size:10px"><a href="https://kbt-store.com/" target="_blank">https://kbt-store.com/</a></span><br/>
                            Instagram: <img alt="" src="/images/instagram_64x64.png" style="height:14px; width:14px" /> kbt.group
                        </p>


                        <p>
                            <span style="font-size:12px">$supportPhoneStr</span>
                        </p>
                        <p>
                            <span style="font-size:12px">Курьер: {$GLOBAL_KZ_COURIERS_ASS[$row['kz_curier']]['id']}</span>
                        </p>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>

                        <p style="font-size:13px; text-align:center;">
                            <strong>
                                Колличество билетов<br/>
                                на ближайшие два<br/>
                                розыгрыша по данному<br/>
                                заказу = {$biletCount[$row['id']]}
                            </strong>
                        </p>

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

    if ($row['country'] == 'kz') {
        $pdf->Rotate(90, -20, 440);
        $pdf->SetFont('dejavusans', '', 8, '', true);
//        $pdf->MultiCell(250, 0, "KBT group!!!", 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
//        MultiCell($w,  $h, $txt                                , $border=0, $align='J', $fill=false, $ln=1, $x='' , $y='' , $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false);
//        $pdf->MultiCell(250, 0, "Өнімді достарыңызға, әріптестеріңізге, туыстарыңызға ұсыныңыз және олардың төленген тапсырысынан, барабанға ай сайынғы автокөлік ұтысына қосымша 3 билет алыңыз.", 0, 'L', 0);
//        $pdf->MultiCell(250, 0, "Қосымша ақпарат алу үшін 2442 қысқа нөміріне хабарласыңыз", 0, 'L', 0);
//        $pdf->MultiCell(250, 0, "Қосымша ақпарат алу үшін 2442 қысқа нөміріне хабарласыңыз", 0, 'L', 0);
//        $pdf->MultiCell(250, 0, "Промо-код: {$row['uuid']}", 0, 'L', 0);
    } else {
        $pdf->Rotate(90, 0, 500);
        $pdf->SetFont('arial', '', 12, '', true);
//        $pdf->MultiCell(230, 0, "Промо-код: {$row['uuid']}", 0, 'L', 0);
    }

    $pdf->StopTransform();
/////////////////////
    $l = 20;
    $x = 35;
    $y = 50;

    if ($row['country'] == 'kz' || $row ['country'] == 'KZ') {
        $h = 10;

//        $pdf->Line($x - 30, $y + 330 + $h * $l, $x + 200, $y + 330 + $h * $l);

        $pdf->StartTransform();
        $pdf->Rotate(90, 240, 600);
        // Левая часть расписки
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->SetXY($x, $y + $h * 32);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->MultiCell(220, 0, "Приложение 1\nк приказу Министра финансов республики Казахстан\nот 20 декабря 2012 года № 562\n\nформа КО-1", 0, 'R', 0);
        } elseif ($row['country'] == 'am') {
            $pdf->MultiCell(220, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Республики Армения\n форма КО-1", 0, 'R', 0);
        } else {
            $pdf->MultiCell(220, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Кыргызской Республики\n форма КО-1", 0, 'R', 0);
        } $pdf->SetXY($x, $y + $h * 37);
        $pdf->MultiCell(220, 0, "Организация", 0, 'L', 0);

        $pdf->SetFont('arial', 'U', 8, '', true);
        $pdf->SetXY($x, $y + $h * 38);

        $pdf->MultiCell(220, 0, $postavStr, 0, 'C', 0);

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->SetXY($x, $y + $h * 39);
        $pdf->MultiCell(220, 0, " КВИТАНЦИЯ к приходному касовому ордеру №  {$row ['id']}", 0, 'C', 0);
        $pdf->Line($x + 5, $y + $h * 39 + 10, $x + 220, $y + $h * 39 + 10);

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x, $y + $h * 40, "Принято от");
        $pdf->Text($x, $y + $h * 41, $row['fio']);
        $pdf->Line($x, $y + $h * 41 + 10, $x + 220, $y + $h * 41 + 10);

        $pdf->Text($x, $y + $h * 42, "Сумма");
        $pdf->SetFont('arial', '', 7, '', true);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x, $y + $h * 43, num2str($row['price']));
        } elseif ($row['country'] == 'am') {
            $pdf->Text($x, $y + $h * 43, num2dram($row['price']));
        } elseif ($row['country'] == 'uz') {
            $pdf->Text($x, $y + $h * 43, num2sum($row ['price']));
        } else {
            $pdf->Text($x, $y + $h * 43, num2som($row ['price']));
        }
        $pdf->Line($x, $y + $h * 43 + 10, $x + 220, $y + $h * 43 + 10);

        $pdf->SetFont('arial', '', 5, '', true);
        $pdf->Text($x + 40, $y + $h * 44, 'прописью');

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x, $y + $h * 46, 'М.П.');

        $pdf->Text($x, $y + $h * 48, 'Главный бухгалтер или уполномоченное лицо');
        $pdf->Text($x + 140, $y + $h * 50, 'Не предусмотрен');

        $pdf->SetFont('arial', '', 5, '', true);
        $pdf->Line($x + 25, $y + $h * 51, $x + 80, $y + $h * 51);
        $pdf->Text($x + 40, $y + $h * 51, 'подпись');

        $pdf->Line($x + 110, $y + $h * 51, $x + 220, $y + $h * 51);
        $pdf->Text($x + 130, $y + $h * 51, 'расшифровка подписи');

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->Text($x, $y + $h * 53, 'Кассир');
        $pdf->Text($x + 140, $y + $h * 53, 'Не предусмотрен');
        $pdf->SetFont('arial', '', 5, '', true);
        $pdf->Line($x + 25, $y + $h * 54, $x + 80, $y + $h * 54);
        $pdf->Text($x + 40, $y + $h * 54, 'подпись');

        $pdf->Line($x + 110, $y + $h * 54, $x + 220, $y + $h * 54);
        $pdf->Text($x + 130, $y + $h * 54, 'расшифровка подписи');
    } else {
        $h = 13;

        $pdf->Line($x, $y + 270 + $h * $l, $x + 520, $y + 270 + $h * $l);

        $pdf->StartTransform();
        $pdf->Rotate(90, 300, 540);
        // Левая часть расписки
        $pdf->SetFont('arial', '', 9, '', true);
        $pdf->SetXY($x, $y + $h * 32);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->MultiCell(220, 0, "Приложение 1\nк приказу Министра финансов республики Казахстан\nот 20 декабря 2012 года № 562\n\nформа КО-1", 0, 'R', 0);
        } elseif ($row['country'] == 'am') {
            $pdf->MultiCell(220, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Республики Армения\n форма КО-1", 0, 'R', 0);
        } else {
            $pdf->MultiCell(220, 0, "Приложение 1\n Министерство Транспорта и коммуникаций Кыргызской Республики\n форма КО-1", 0, 'R', 0);
        }
        $pdf->SetXY($x, $y + $h * 37);
        $pdf->MultiCell(220, 0, "Организация", 0, 'L', 0);

        $pdf->SetFont('arial', 'U', 8, '', true);
        $pdf->SetXY($x, $y + $h * 38);

        $pdf->MultiCell(220, 0, $postavStr, 0, 'C', 0);

        $pdf->SetFont('arial', '', 9, '', true);
        $pdf->SetXY($x, $y + $h * 39);
        $pdf->MultiCell(220, 0, "КВИТАНЦИЯ к приходному касовому ордеру № {$row['id']}", 0, 'C', 0);
        $pdf->Line($x + 5, $y + $h * 39 + 10, $x + 220, $y + $h * 39 + 10);

        $pdf->SetFont('arial', '', 9, '', true);
        $pdf->Text($x, $y + $h * 40, "Принято от");
        $pdf->Text($x, $y + $h * 41, $row['fio']);
        $pdf->Line($x, $y + $h * 41 + 10, $x + 220, $y + $h * 41 + 10);

        $pdf->Text($x, $y + $h * 42, "Сумма");
        $pdf->SetFont('arial', '', 8, '', true);
        if ($row['country'] == 'kz' || $row['country'] == 'KZ') {
            $pdf->Text($x, $y + $h * 43, num2str($row['price']));
        } elseif ($row['country'] == 'am') {
            $pdf->Text($x, $y + $h * 43, num2dram($row['price']));
        } elseif ($row['country'] == 'uz') {
            $pdf->Text($x, $y + $h * 43, num2sum($row['price']));
        } else {
            $pdf->Text($x, $y + $h * 43, num2som($row['price']));
        }
        $pdf->Line($x, $y + $h * 43 + 10, $x + 220, $y + $h * 43 + 10);

        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Text($x + 40, $y + $h * 44, 'прописью');

        $pdf->SetFont('arial', '', 9, '', true);
        $pdf->Text($x, $y + $h * 46, 'М.П.');

        $pdf->Text($x, $y + $h * 48, 'Главный бухгалтер или уполномоченное лицо');
        $pdf->Text($x + 140, $y + $h * 50, 'Не предусмотрен');

        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Line($x + 25, $y + $h * 51, $x + 80, $y + $h * 51);
        $pdf->Text($x + 40, $y + $h * 51, 'подпись');

        $pdf->Line($x + 110, $y + $h * 51, $x + 220, $y + $h * 51);
        $pdf->Text($x + 130, $y + $h * 51, 'расшифровка подписи');

        $pdf->SetFont('arial', '', 9, '', true);
        $pdf->Text($x, $y + $h * 53, 'Кассир');
        $pdf->Text($x + 140, $y + $h * 53, 'Не предусмотрен');
        $pdf->SetFont('arial', '', 6, '', true);
        $pdf->Line($x + 25, $y + $h * 54, $x + 80, $y + $h * 54);
        $pdf->Text($x + 40, $y + $h * 54, 'подпись');

        $pdf->Line($x + 110, $y + $h * 54, $x + 220, $y + $h * 54);
        $pdf->Text($x + 130, $y + $h * 54, 'расшифровка подписи');
    }
}


$pdf->Output(dirname(__FILE__) . '/../tmp/waybill-kbt.pdf', 'FI');
/////////////////////////////////////////////////////////////////////

