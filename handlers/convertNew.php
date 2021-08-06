<?php

ob_clean();

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
require_once dirname(__FILE__) . '/../lib/fpdi/fpdi.php';

header('Content-Type: application/json; charset=utf-8', true);

$ids = [];
if (isset($_GET['id'])) {
    $ids = explode(',', $_GET['id']);
}
foreach ($ids as $key => $id) {
    if (strlen($id) == 0 || $id <= 0) {
        unset($ids[$key]);
    }
}
if (count($ids) == 0) {
    die('Нет заказов для печати');
}
$query = "SELECT * FROM staff_order WHERE id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ") ORDER BY offer,package";

$rs = mysql_query($query);
$pdf = new FPDI('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 0, 5);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setFontSubsetting(true);
$pdf->SetFont('dejavusans', '', 12, '', true);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pageCount = $pdf->setSourceFile('EVRAZ.pdf');

switch ((int) $_REQUEST['otpr']) {
    case 7:
        $text1 = 'ИП BRDmarket';
        $text2 = 'г. Астана , ул. Ауэзова 13, а/я 87';
        $text4 = 'Служба заботы о клиентах:+77059240373';
        $text3 = 'Договор № 01-02-17/1050 ОТ 24.03.2017';
        $postcode = '010000';
        break;
    case 10:
        $text1 = 'ИП ABC Group';
        $text2 = 'г. Астана , ул. Ауэзова 13, а/я 87';
        $text4 = 'Служба заботы о клиентах:+77470940062';
        $text3 = 'Договор № 01-02-17/1049 ОТ 24.03.2017';
        $postcode = '010000';
        break;
    default:
        $text1 = $text2 = $text4 = $text3 = $postcode = '!!ERROR!!';
        break;
}


$style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
while ($row = mysql_fetch_object($rs)) {
    $templateId = $pdf->importPage(1);
    $pdf->AddPage('L');
    $row->kz_code = str_replace(' ', '', $row->kz_code);
    $row->district = str_replace(['область', 'Область'], 'обл', $row->district);
    $row->district .= (strpos($row->district, ' обл') !== false) ? '' : ' обл';

    $x = 0;
    $y = 15;
    $pdf->useTemplate($templateId, 2 + $x, 1 + $y, 270);
    $pdf->SetFontSize(12);
    $pdf->Text(33 + $x, 36 + $y, $text1);
    $pdf->Text(33 + $x, 48 + $y, $text2);
    $pdf->Text(33 + $x, 53 + $y, $text4);

    $pdf->SetFontSize(11);
    $pdf->Text(22 + $x, 105 + $y, $text3);
    $pdf->setFontStretching(105);
    $pdf->Text(68 + $x, 59 + $y, $postcode);
    $pdf->setFontStretching(100);
    $pdf->SetFontSize(9);
    $ait = 0;

    $other_data = json_decode($row->other_data, true);

    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }

    $offer_property = NULL;

    if (!empty($other_data)) {
        $offer_property = implode(", ", (array) $other_data);
    }
    $tovar = array(
        $GLOBAL_OFFER_DESC[$row->offer] . ($offer_property ? ' (' . $offer_property . ')' : '') . " - {$row->package}шт."
    );

    if (isJson($row->dop_tovar)) {
        $tmp_dop = json_decode($row->dop_tovar, true);
        foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
            $tovar[] = $va . ' ' . (isset($tmp_dop['vendor'][$ke]) ? $tmp_dop['vendor'][$ke] : '') . ' ' . (isset($tmp_dop['color'][$ke]) ? $tmp_dop['color'][$ke] : '') . ' ' . (isset($tmp_dop['name'][$ke]) ? $tmp_dop['name'][$ke] : '') . ' ' . (isset($tmp_dop['type'][$ke]) ? $tmp_dop['type'][$ke] : '') . ' ' . (isset($tmp_dop['size'][$ke]) ? $tmp_dop['size'][$ke] : '') . ' - ' . $tmp_dop['dop_tovar_count'][$ke] . 'шт.';
        }
    }

    foreach ($tovar as $at) {
        $pdf->Text(140 + $x, 96 + $y + $ait, $at);
        $ait = $ait + 3;
    }
    $pdf->Text(136 + $x, 142 + $y, $row->id);
    $pdf->Text(165 + $x, 32 + $y, $row->total_price . ' (' . num2str($row->total_price) . ')');
    $pdf->Text(165 + $x, 41 + $y, $row->total_price . ' (' . num2str($row->total_price) . ')');
    $pdf->SetFontSize(16);
    $pdf->Text(92 + $x, 24 + $y, 'КОД ПЛАТЕЖА                  4806');
    $pdf->SetFontSize(12);
    $pdf->Text(158 + $x, 115 + $y, $row->fio);
    $address = explode(' ', $row->addr);
    $aid = 0;
    $adar = array();
    $pdf->SetFontSize(10);
    foreach ($address as $ad) {
        if ($aid < 3) {
            $adar[0] = $adar[0] . ' ' . $ad;
        } else {
            $adar[1] = $adar[1] . ' ' . $ad;
        }
        $aid++;
    }
    if (isset($adar[0])) {
        $pdf->Text(155 + $x, 130 + $y, $adar[0]);
    }
    if (isset($adar[1])) {
        $pdf->Text(155 + $x, 135 + $y, $adar[1]);
    }

    $pdf->SetFontSize(12);

    if ($row->kz_delivery == 'Почта') {
        $pdf->Text(160 + $x, 149 + $y, '+' . $row->phone);
    }

    if (strlen($row->kz_code) > 6) {
        $cs = curl_init();
        curl_setopt($cs, CURLOPT_URL, 'http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=10&rot=0&text=' . $row->kz_code . '&f1=0&f2=10&a1=&a2=&a3=');
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
        file_put_contents(__DIR__ . '/../tmp/barcodes/' . $row->id . '_barcode.png', $ret);
        $pdf->Image(__DIR__ . '/../tmp/barcodes/' . $row->id . '_barcode.png', 160 + $x, 83 + $y, 80);
        $pdf->Text(160 + $x, 52 + $y, $row->kz_code);
    }
    $pdf->SetFontSize(11);
    $pdf->setFontStretching(105);
    $pdf->Text(205, 175, str_replace(' ', '', $row->index));
    $pdf->setFontStretching(100);
    $pdf->SetFontSize(12);
}
$pdf->Output(dirname(__FILE__) . '/../tmp/form_address_export.pdf', 'FI');

