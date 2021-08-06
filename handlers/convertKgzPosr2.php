<?php

/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 04.04.16
 * Time: 15:23
 */
ob_clean();

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
$pageCount = $pdf->setSourceFile('yarluk.pdf');

$text1 = 'ОСОО КБТ';
$text2 = 'Кыргызская Республика,';
$text4 = ' г. Бишкек, пр. Чуй, 227 р/с1280026031102944,';
$text3 = ' БИК 128002,ЗАО «КИКБ» ИНН 21712198201280 ';
$postcode = '001016';

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
    $pdf->Text(27 + $x, 20 + $y, $text1);
    $pdf->Text(25 + $x, 38 + $y, $text2);
    $pdf->Text(22 + $x, 48 + $y, $text4);
    $pdf->Text(22 + $x, 54 + $y, $text3);
    $pdf->SetFontSize(11);
    $pdf->setFontStretching(195);
    $pdf->Text(75 + $x, 68 + $y, $postcode);
    $pdf->setFontStretching(100);
    $pdf->SetFontSize(12);
    //$pdf->Text(70+$x, 52+$y, $row->price);
    //$pdf->Text(70+$x, 8+$y, num2str($row->price));
    $pdf->Text(180 + $x, 9 + $y, $row->price);
    $pdf->Text(180 + $x, 15 + $y, $row->price);
    //$pdf->Text(90+$x, 77+$y, $row->price);
    //$pdf->Text(10+$x, 60+$y, num2str($row->price));
    //$pdf->Image(dirname(__FILE__) . '/../images/Rihanna-signature.png', 49+$x, 68+$y, 15);
    $pdf->Text(13 + $x, 155 + $y, 'X');
    $pdf->Text(148 + $x, 95 + $y, $row->fio);
    //$row->addr = iconv('cp1251','utf-8',$row->addr);
    /* if(mb_strlen($row->addr)>63){
      $pdf->Text(145+$x, 106+$y, mb_substr($row->addr,0,63));
      $pdf->Text(145+$x, 112+$y, mb_substr($row->addr,64,mb_strlen($row->addr)));
      }
      else */
    $address = explode("-", $row->addr);
    $aid = 0;
    foreach ($address as $ad) {
        $pdf->Text(145 + $x, 106 + $y + $aid, $ad);
        $aid = $aid + 5;
    }
    //$pdf->Text(76+$x, 61+$y, $row->city . ',');
    //$pdf->Text(76+$x, 65+$y, $row->district.(strlen($row->region) > 0 ? ', '.$row->region : ''));
    $pdf->Text(220 + $x, 133 + $y, '+' . $row->phone);
    $pdf->Text(150 + $x, 162 + $y, $row->id);

    //$pdf->Text(85+$x, 35+$y, $row->index);
    if (strlen($row->kz_code) > 6) {
        $cs = curl_init(); // curl session
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
        $pdf->Image(__DIR__ . '/../tmp/barcodes/' . $row->id . '_barcode.png', 160 + $x, 23 + $y, 40);
        $pdf->Text(160 + $x, 37 + $y, $row->kz_code);
    }
    $pdf->SetFontSize(11);
    $pdf->setFontStretching(195);
    $pdf->Text(215, 156, str_replace(' ', '', $row->index));
    $pdf->setFontStretching(100);
    $pdf->SetFontSize(12);
}
$pdf->Output(dirname(__FILE__) . '/../tmp/form_address_export.pdf', 'FI');
