<?php

ob_end_clean();
session_start();

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
$redis = RedisManager::getInstance()->getRedis();

$t_ar = $redis->hGetAll('black_list');

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

$query = "SELECT * FROM staff_order
				WHERE  id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ") AND country = 'ru'
				ORDER BY offer,package";
//echo $query; die;
$rs = mysql_query($query);
$pdf = new \TCPDF('L', 'px', 'A4', true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(10, 5, -60);
$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}
//$pdf->setFontSubsetting(true);
//$pdf->SetFont('dejavusans', '', 12, '', true);
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
        width: 70%;
        font-size: 12px;
      }
      table.front_table {
        border: none;
        width: 1200px;
        /*font-size: 12px;*/
      }

      tr.left_table, td.left_table {
        border: 1px solid black;
        align:left;
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
$i = 0;
$in_html = '<table class="front_table" cellpadding="0" cellspacing="0">';
$co = mysql_num_rows($rs);
while ($obj = mysql_fetch_assoc($rs)) {
    $dop_str = '';

    if (isJson($obj['dop_tovar'])) {
        $tmp_dop = (array) json_decode($obj['dop_tovar']);
        foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
            $dop_str .= '<br>' . $GLOBAL_OFFER_DESC[$va] . ' ' . (isset($tmp_dop['vendor'][$ke]) ? $tmp_dop['vendor'][$ke] : '') . ' ' . (isset($tmp_dop['color'][$ke]) ? $tmp_dop['color'][$ke] : '') . ' ' . (isset($tmp_dop['name'][$ke]) ? $tmp_dop['name'][$ke] : '') . ' ' . (isset($tmp_dop['type'][$ke]) ? $tmp_dop['type'][$ke] : '') . ' ' . (isset($tmp_dop['size'][$ke]) ? $tmp_dop['size'][$ke] : '') . ' - ' . $tmp_dop['dop_tovar_count'][$ke] . 'шт.';
        }
    }

    //$obj['addr'] = mb_substr($obj['addr'],0,80);

    $offerName = $GLOBAL_OFFER_DESC[$obj['offer']];

    //
    // выбираем атрибут товара и формируем правильное название
    //
	$other_data = json_decode($obj['other_data'], true);

    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }
    if (json_last_error() == JSON_ERROR_NONE) {
        krsort($other_data);
        $offerName = $GLOBAL_OFFER_DESC[$obj['offer']] . " " . implode(" ", $other_data);
    }
    /* if (
      isset($other_data['attribute']) &&
      !empty($other_data['attribute'])
      ) {
      $offerName = $offerName . " (" . $other_data['attribute'] . ")";
      }

      if (
      isset($other_data['vendor']) &&
      !empty($other_data['vendor'])
      ) {
      $offerName = $offerName . " (" . $other_data['vendor'] . ")";
      }

      if (
      isset($other_data['color']) &&
      !empty($other_data['color'])
      ) {
      $offerName = $offerName . " (" . $other_data['color'] . ")";
      } */
    if ($i == 0 or ( $i % 4 == 0))
        $pdf->AddPage('L');

    $in_html = '
        <tr>
              <td> ' . ($i + 1) . ' из ' . $co . ' ' . $obj['fio'] . '<br>
                      <img src="http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=' . urlencode('874+' . $obj['id']) . '&f1=0&f2=10&a1=&a2=&a3=" alt="Barcode Image" />
                      <br>874+' . $obj['id'] . '<br>' . $offerName . ' - ' . $obj['package'] . ' ' . $dop_str . '
                      <br>-------------------------------------------------------------------------
              </td>
        </tr>';


    //if($i==0 or ($i % 6 == 0))
    $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');

    $i++;
}
$in_html .= '</table></body>
</html>';
//echo $in_html; die;
//$pdf->writeHTML($in_html, true, false, false, false, '');

$pdf->Output('doc.pdf', 'I');


