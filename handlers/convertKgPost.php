<?php

/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 04.04.16
 * Time: 15:23
 */
ob_clean();
session_start();

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
require_once dirname(__FILE__) . '/../lib/fpdi/fpdi.php';

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
$query = "SELECT * FROM staff_order
				WHERE country = 'kzg' AND id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ")
				ORDER BY offer,package";

$rs = mysql_query($query);
$pdf = new FPDI('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 0, 5);
$pdf->SetAutoPageBreak(TRUE, 0);
$pageCount = $pdf->setSourceFile('package.pdf');

$text1 = 'ОСОО КБТ';
$text2 = 'Кыргызская Республика Чуйская область, Кеминский район,';
$text3 = 'село Кичи-Кемин, ул. Омуралиева, д. 6';
$postcode = '001016';

$index = 1;
$pdf->AddPage('A');
$templateId = $pdf->importPage(1);
$style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
while ($row = mysql_fetch_object($rs)) {
    if ($index > 2) {
        $index = 1;
        $templateId = $pdf->importPage(1);
        $pdf->AddPage('A');
    }
    $row->district = str_replace(['область', 'Область'], 'обл', $row->district);
    $row->district .= (strpos($row->district, ' обл') !== false) ? '' : ' обл';
    if ($index == 1) {
        $x = 0;
        $y = 15;
        $pdf->useTemplate($templateId, 2 + $x, 1 + $y, 200);
        $pdf->SetFontSize(12);
        $pdf->Text(28 + $x, 114 + $y, $text1);
        $pdf->Text(28 + $x, 120 + $y, $text2);
        $pdf->Text(47 + $x, 125 + $y, $text3);
        $pdf->Text(28 + $x, 125 + $y, $postcode);
        $pdf->Text(10 + $x, 48 + $y, num2str($row->price));
        $pdf->Text(140 + $x, 39 + $y, $row->price);
        $pdf->Text(10 + $x, 60 + $y, num2str($row->price));
        $pdf->Text(3 + $x, 64.5 + $y, 'X');
        $pdf->Text(35 + $x, 95 + $y, $row->fio);
        $pdf->Text(25 + $x, 71 + $y, $row->index);
        $pdf->Text(25 + $x, 76 + $y, $row->addr . ',');
        $pdf->Text(40 + $x, 104 + $y, '+' . $row->phone);
        if (strlen($row->kz_code) > 6) {
            $cs = curl_init();
            curl_setopt($cs, CURLOPT_URL, 'http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=50&r=10&rot=0&text=' . $row->kz_code . '&f1=0&f2=10&a1=&a2=&a3=');
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
            $pdf->Image(__DIR__ . '/../tmp/barcodes/' . $row->id . '_barcode.png', 120 + $x, 10 + $y, 30);
            $pdf->Text(120 + $x, 16 + $y, $row->kz_code);
        }
    } elseif ($index == 2) {
        $x = 3;
        $y = 152;

        $pdf->SetFontSize(12);
        $pdf->Text(28 + $x, 110 + $y, $text1);
        $pdf->Text(28 + $x, 120 + $y, $text2);
        $pdf->Text(47 + $x, 126 + $y, $text3);
        $pdf->Text(28 + $x, 126 + $y, $postcode);
        $pdf->Text(10 + $x, 48 + $y, num2str($row->price));
        $pdf->Text(140 + $x, 39 + $y, $row->price);
        $pdf->Text(10 + $x, 60 + $y, num2str($row->price));
        $pdf->Text(3 + $x, 64.5 + $y, 'X');
        $pdf->Text(35 + $x, 95 + $y, $row->fio);
        $pdf->Text(25 + $x, 71 + $y, $row->index);
        $pdf->Text(25 + $x, 76 + $y, $row->addr . ',');
        $pdf->Text(40 + $x, 104 + $y, '+' . $row->phone);
        if (strlen($row->kz_code) > 6) {
            $cs = curl_init();
            curl_setopt($cs, CURLOPT_URL, 'http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=50&r=10&rot=0&text=' . $row->kz_code . '&f1=0&f2=10&a1=&a2=&a3=');
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
            $pdf->Image(__DIR__ . '/../tmp/barcodes/' . $row->id . '_barcode.png', 120 + $x, 10 + $y, 30);
            $pdf->Text(120 + $x, 16 + $y, $row->kz_code);
        }
    }

    $index++;
}
$pdf->Output(dirname(__FILE__) . '/../tmp/form_address_export.pdf', 'FI');

