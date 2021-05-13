<?php

include('config.php');
require('function.php');
require_once dirname(__FILE__) . '/../../../vendor/autoload.php';

if (@file_exists(dirname(__FILE__) . '/../../lang/eng.php')) {
    require_once(dirname(__FILE__) . '/../../lang/eng.php');
    $pdf->setLanguageArray($l);
}
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sergey');
$pdf->SetTitle('Declaration');
$pdf->SetSubject('Kazpost');
$pdf->SetKeywords('Kazpost');


$codes = substr($_GET['code'], 0, strlen($_GET['code']) - 1);
$code_ar = explode(",", $codes);

foreach ($code_ar as $k => $code1) {
    $pre_code = explode("|", $code1);
    $code = $pre_code[0];
    $html = '<b>' . $pre_code[1] . '</b><br>';
    $html .= '<img src="http://89.218.86.178:8081/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=' . $code . '&f1=0&f2=10&a1=&a2=&a3=" alt="Barcode Image" />
	<div align="center" style="font-size:10px;"><b>';
    $html .= $code . '</b></div>';

// set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//$pdf->setFooterFont(Array('dejavusans', '', PDF_FONT_SIZE_DATA));
//set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// ---------------------------------------------------------
// set default font subsetting mode
    $pdf->setFontSubsetting(true);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(3, 3, 3, false);
    $pdf->SetAutoPageBreak(true, 5);
// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
    $pdf->SetFont('dejavusans', '', 6, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
    $page_format = array(
        'MediaBox' => array('l1x' => 0, 'l1y' => 0, 'urx' => 50, 'ury' => 25),
        'Rotate' => 0,
    );

// Check the example n. 29 for viewer preferences
// add first page ---
    $pdf->AddPage('L', $page_format, false, false);

//$pdf->Cell(0, 10, $i.' страница ', 0, 1, 'L');
// Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');

// ---------------------------------------------------------
}

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('barcode_001.pdf', 'I');
