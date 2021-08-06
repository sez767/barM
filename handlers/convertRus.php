<?php

ob_end_clean();

require_once dirname(__FILE__) . '/../lib/db.php';

$redis = RedisManager::getInstance()->getRedis();
$t_ar = $redis->hGetAll('black_list');
//if (!isset($_SESSION['Logged_StaffId'])) { header("location: /login.html"); die();	}

require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

$query = "SELECT * FROM staff_order
				WHERE  id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ") AND country='ru'
				ORDER BY offer,package";
//echo $query; die;
$rs = mysql_query($query);
$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(3, 3, 2, true);
$pdf->SetAutoPageBreak(FALSE, 2);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}
$pdf->setFontSubsetting(false);
$pdf->SetFont('dejavusans', '', 7, '', true);
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$headhtml = '
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <style>

      table.left_table {
        border: 1px solid black;
        width: 90%;
        font-size: 12px;
      }
      table.right_table {
        border: none;
        width: 80%;
        font-size: 12px;
      }
      table.front_table {
        border: none;
		color:blue;
        width: 1200px;
        /*font-size: 12px;*/
      }

      tr.left_table, td.left_table {
        border: 1px solid black;
        align:left;
		width: 800px;
		padding: 5px;
      }

      .center {
        text-align: center;
      }
      .left_column {
        width: 15%;
      }

      .non_border_bottom {
        border-bottom: none;
      }
      .small_text {
        font-size: 8px;
      }
      .text_right {
        text-align: right;
      }
      .non_border {
        border: none;
      }
      .imp_border {
        border: 2px solid black;
      }
      .bottom_border {
        border-bottom: 2px solid black;
      }
      .indent1 {
        display: block;
        width: 50px;
      }

    </style>
  </head>
  <body>';
$i = 1;
$pdf->AddPage('P');
$in_html = '';
$text_ar = array();
//$in_html = '<table border="1" width="700" class="front_table" >';
while ($obj = mysql_fetch_assoc($rs)) {
    $t = 1;
    $dop_str = '';
    if (isJson($obj['dop_tovar'])) {
        $tmp_dop = (array) json_decode($obj['dop_tovar']);
        foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
            $dop_str .= '<br>' . $GLOBAL_OFFER_DESC[$va] . ' - ' . $tmp_dop['dop_tovar_count'][$ke] . '(' . $tmp_dop['dop_tovar_price'][$ke] . ')';
        }
    }
    $offerName = $GLOBAL_OFFER_DESC[$obj['offer']];
    //$obj['addr'] = mb_substr($obj['addr'],0,80);
    if ($i % 2 == 0 && $i != 1) {
        $kof = 138;
        $pdf->AddPage('P');
    } else
        $kof = 0;
    $in_html = '<table width="700" class="front_table" >';

    $in_html .= '
		  <tr><td><img height="480" width="600" src="f116.jpg" alt=""/></td></tr>
		  <tr><td><img height="480" width="600" src="f116.jpg" alt=""/></td></tr>
		';
//    $text_ar[] = array('x' => 10, 'y' => 52, 'text' => $obj['price'] . 'руб. (' . num2str($obj['price']) . ') 00 коп.', 'size' => 9);
    $text_ar[] = array('x' => 10, 'y' => 52, 'text' => $obj['price'] . 'руб. 00 коп.', 'size' => 9);

    //$text_ar[]=array('x'=>10,'y'=>61,'text'=>$obj['price'].'руб. ('.num2str($obj['price']).') 00 коп.','size'=>9);
    //$text_ar[]=array('x'=>40,'y'=>24,'text'=>num2str($obj['price']),'size'=>10);
    //$text_ar[]=array('x'=>20,'y'=>47,'text'=>' ');
    $text_ar[] = array('x' => 25, 'y' => 68, 'text' => 'ООО "НАИЛ" ', 'size' => 9);
    $text_ar[] = array('x' => 25, 'y' => 73, 'text' => 'ул. Горшина, дом 6, корпус 2, помещение 38', 'size' => 9);
    $text_ar[] = array('x' => 25, 'y' => 79, 'text' => ' Московская обл., г. Химки', 'size' => 9);
    //$text_ar[]=array('x'=>20,'y'=>73,'text'=>' +'.$obj['phone'],'size'=>9);
    $text_ar[] = array('x' => 129, 'y' => 106, 'text' => $obj['index'][0] . '  ' . $obj['index'][1] . '  ' . $obj['index'][2] . '  ' . $obj['index'][3] . '  ' . $obj['index'][4] . '  ' . $obj['index'][5], 'size' => 12);
    $text_ar[] = array('x' => 30, 'y' => 94, 'text' => $obj['fio'], 'size' => 9);
    $text_ar[] = array('x' => 45, 'y' => 35, 'text' => $obj['id'], 'size' => 9);
    $text_ar[] = array('x' => 25, 'y' => 41, 'text' => 'Телефон тех. поддержки +74996771609', 'size' => 9);

    $text_ar[] = array('x' => 30, 'y' => 101, 'text' => 'г. ' . $obj['city'] . ' ' . $obj['street'] . ' ' . $obj['building'] . '/' . $obj['flat'], 'size' => 9);
    $text_ar[] = array('x' => 85, 'y' => 85, 'text' => '1  4  1  4  0  7', 'size' => 13);

    //if($i%3 == 0) {
    $t = 0;
    $in_html .= '</table>';
    $pdf->writeHTML($in_html, true, false, true, false, '');

    foreach ($text_ar as $tk => $tv) {
        if (isset($tv['size']))
            $pdf->SetFontSize(($tv['size']));
        else
            $pdf->SetFontSize(7);
        $pdf->SetTextColor(18, 7, 166);
        $pdf->Text($tv['x'], $tv['y'] + $kof, $tv['text']);
    }
    $text_ar = array();
    $pdf->SetTextColor(18, 7, 166);
    $pdf->Text(40, 121 + $kof, $offerName . ' - ' . $obj['package'] . ' ' . $dop_str);
    $i++;
}

$in_html .= '</body>
</html>';

$pdf->Output('doc.pdf', 'I');
