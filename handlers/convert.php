<?php

ob_end_clean();
session_start();

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
$redis = RedisManager::getInstance()->getRedis();

$t_ar = $redis->hGetAll('black_list');
if ((int) $_GET['otpr'] == 1) {
    $chep_dog = 'отправления ИП Тюлялиев  согл.дог. №16-15 от 23.03.2015 г';
    $chep = 'Тюлялиев';
    $chp_addr = '';
    //$chp_addr = 'ул. Манаса 101/1';
    $rss = 'р/с 1280016018427989';
    $bik = 'БИК128002';
} elseif ((int) $_GET['otpr'] == 3) {
    $otprav = 'ИП BRDmarket';
    $iik = 'KZ978210439812131672';
    $bank = 'АО «Bank RBK»';
    $biks = 'KINCKZKA';
    $addr = 'Главпочтамт а/я 87 ул. Ауэзова д. 13';
    $inn = '910214351216';
    $kodp = '4806';
} elseif ((int) $_GET['otpr'] == 4) {
    $otprav = 'ИП  «Нурлыханов» ';
    $iik = 'KZ898210439812129488';
    $bank = 'АО «Bank RBK»';
    $biks = 'KINCKZKA';
    $addr = 'Главпочтамт а/я 98 ул. Ауэзова д. 13';
    $inn = '880416301140';
    $kodp = '4807';
} elseif ((int) $_GET['otpr'] == 5) {
    $otprav = 'ИП «Садыков Д. Ш.»';
    $iik = 'KZ028210439812130181';
    $bank = 'АО «Bank RBK»';
    $biks = 'KINCKZKA';
    $addr = 'Главпочтамт а/я 97 ул. Ауэзова д. 13';
    $inn = '880507302173';
    $kodp = '4809';
} elseif ((int) $_GET['otpr'] == 6) {
    $otprav = 'ИП «Садыкова  Д. О.»';
    $iik = 'KZ728210439812137069';
    $bank = 'АО «Bank RBK»';
    $biks = 'KINCKZKA';
    $addr = 'Главпочтамт а/я 96 ул. Ауэзова д. 13';
    $inn = '871021402029';
    $kodp = '4808';
} else {
    /* $chep_dog = 'отправления ИП Пущина';
      $chep = 'Пущина';
      //$chp_addr = 'улица Разакова 32';
      $chp_addr = '';
      $rss = 'р/с 1280016019088397';
      $bik = 'БИК128001';
     */
    $chep_dog = ' ИП Метеров Р.А.';
    $chep = 'Метеров';
    $chp_addr = '001016, г. Бишкек, пр. Чуй, 227';
    //$chp_addr = '';
    $rss = 'р/с1280026031102944';
    $bik = 'БИК 128002';
}
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

$query = "SELECT * FROM staff_order
				WHERE  id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ")
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
while ($obj = mysql_fetch_assoc($rs)) {
    $dop_str = '';
    // обработка атрибутов товара
    $other_data = json_decode($obj['other_data'], true);

    if (json_last_error() == JSON_ERROR_NONE) {
        krsort($other_data);
        $dop_str .= $GLOBAL_OFFER_DESC[$obj['offer']] . " " . implode(" ", $other_data);
    } else {
        $dop_str .= $GLOBAL_OFFER_DESC[$obj['offer']];
    }
    // обработка дополнительного товара
    $dop_tovar = json_decode($obj['dop_tovar'], true);
    $dop_tovar_all = array();

    if (
            json_last_error() == JSON_ERROR_NONE &&
            is_array($dop_tovar['dop_tovar'])
    ) {
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
            krsort($properties);
            //var_dump($dop_tovar); die;
            $dop_str .= '<br>' . $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . " " . implode(" ", $properties) . " - " . $dop_tovar['dop_tovar_count'][$ke];
        }
    }
    /*
      if (is_json($obj['dop_tovar'])) {
      $tmp_dop = (array) json_decode($obj['dop_tovar'], true);
      foreach($tmp_dop['dop_tovar'] as $ke=>$va){
      $dop_str .= '<br>'.$GLOBAL_OFFER_DESC[$va].' - '.$tmp_dop['dop_tovar_count'][$ke] .'('. $tmp_dop['dop_tovar_price'][$ke] .') '. (isset($tmp_dop['vendor'][$ke])?$tmp_dop['vendor'][$ke]:'') .' '. (isset($tmp_dop['color'][$ke])?$tmp_dop['color'][$ke]:'') .' '. (isset($tmp_dop['name'][$ke])?$tmp_dop['name'][$ke]:'')  .' '. (isset($tmp_dop['type'][$ke])?$tmp_dop['type'][$ke]:'')  .' '. (isset($tmp_dop['size'][$ke])?$tmp_dop['size'][$ke]:'');
      }
      }
     */
    //$obj['addr'] = mb_substr($obj['addr'],0,80);

    $offerName = $GLOBAL_OFFER_DESC[$obj['offer']];

    //
    // выбираем атрибут товара и формируем правильное название
    //
	$other_data = json_decode($obj['other_data'], true);

    if (json_last_error() != JSON_ERROR_NONE) {
        $other_data = array();
    }

    if (
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
    }

    if ($obj['country'] == 'kz' || $obj['country'] == 'KZ' || $obj['country'] == 'ru') {
        $pdf->AddPage('L');

        $in_html = '<table class="front_table" cellpadding="0" cellspacing="0">
		  <tr>
			<td>
			  <table border="1" class="left_table" cellpadding="0" cellspacing="0">
				<tr class="rows_firm">
				  <td class="left_column non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom center"><span>КВИТАНЦИЯ №</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><img src="kazax.jpg" alt=""/></td>
				  <td colspan="21" class="non_border_bottom center"><span>&nbsp;на оплату наличными</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span>&nbsp;Плательщик(ФИО) ' . $obj['fio'] . '</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span class="small_text">&nbsp;Код платежа</span></td>
				  <td colspan="21" class="non_border_bottom">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="small_text">(фамилия и инициалы)</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom text_right"><span class="small_text">' . $kodp . '&nbsp;</span></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;ИИНПлательщикаКОд</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span class="small_text">&nbsp;Номер посылки</span></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="3%" class="non_border center"></td>
				  <td width="3%" class="non_border center"></td>
				  <td width="23.5%" class="non_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span>&nbsp;&mdash;</span></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Адрес и телефон Плательщика :</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="bottom_border" style="font-size:10px;"><b>' . $obj['index'] . ' ' . $obj['addr'] . ' ' . (($obj['kz_delivery'] == 'Почта') ? $obj['phone'] : '') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Получатель платежа</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $otprav : 'ТОО «KAZECOTRANSIT»') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom">
					<span class="small_text">(КНП)</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">(организация)</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">БИН/ИНН</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">КБЕ</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">КНП</span>
				  </td>
				</tr>
				<tr>' . (($obj['kz_delivery'] == 'Почта') ? ' <td class="non_border_bottom"></td>
				  <td class="imp_border center"><span>' . $inn[0] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[1] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[2] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[3] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[4] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[5] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[6] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[7] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[8] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[9] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[10] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[11] . '</span></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>9</span></td>
				  <td class="imp_border center"></td>' : ' <td class="non_border_bottom"></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>4</span></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>4</span></td>
				  <td class="imp_border center"><span>0</span></td>
				  <td class="imp_border center"><span>0</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>3</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>4</span></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>7</span></td>
				  <td class="imp_border center"></td>') . '

				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;ИИК</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $iik : 'KZ23926150119P249000') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span class="small_text">&nbsp;Кассир:</span></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Банк</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $bank : 'АО «Казкоммерцбанк»') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;БИК</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $biks : 'KINCKZKA') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="20" class="imp_border center"><span class="small_text">Наименование платежа</span></td>
				  <td class="imp_border center"><span class="small_text">Сумма</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="20" class="imp_border"><span class="small_text">&nbsp;Номер посылки:</span></td>
				  <td class="imp_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="20" class="imp_border"></td>
				  <td class="imp_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="20" class="imp_border"></td>
				  <td class="imp_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="20" class="imp_border"><span class="small_text">&nbsp;ВСЕГО (сумма цифрами): </span></td>
				  <td class="imp_border center">' . (in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2)) . '</td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="21" class="imp_border"><span class="small_text">&nbsp;ВСЕГО (прописью): ' . (($obj['country'] == 'kz') ? num2str((in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2))) : num2rub((in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2)))) . '</span></td>
				</tr>
				<tr>
				  <td class="bottom_border">&nbsp;</td>
				  <td colspan="21" class="imp_border"><b class="small_text">&nbsp;Дата  «____» ________________ 20_____г.  Подпись Плательщика _______________  </b></td>
				</tr>
				<tr class="rows_firm">
				  <td class="left_column non_border_bottom"><img src="kazax.jpg" alt=""/></td>
				  <td colspan="21" class="non_border_bottom center"><span>КВИТАНЦИЯ №</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom center"><span>на оплату наличными</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span>&nbsp;Плательщик(ФИО) ' . $obj['fio'] . '</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span class="small_text">&nbsp;Код платежа</span></td>
				  <td colspan="21" class="non_border_bottom">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="small_text">(фамилия и инициалы)</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom text_right"><span class="small_text">' . $kodp . '&nbsp;</span></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;ИИНПлательщикаКОд</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span class="small_text">&nbsp;Номер посылки</span></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="3%" class="imp_border center"></td>
				  <td width="6%" class="non_border center"></td>
				  <td width="3%" class="non_border center"></td>
				  <td width="3%" class="non_border center"></td>
				  <td width="23.5%" class="non_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span>&nbsp;&mdash;</span></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Адрес и телефон Плательщика :</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="bottom_border" style="font-size:10px;"><b>' . $obj['index'] . ' ' . $obj['addr'] . ' ' . (($obj['kz_delivery'] == 'Почта') ? $obj['phone'] : '') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Получатель платежа</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $otprav : 'ТОО «KAZECOTRANSIT»') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom">
					<span class="small_text">(КНП)</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">(организация)</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">БИН/ИНН</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">КБЕ</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="small_text">КНП</span>
				  </td>
				</tr>
				<tr>' . (($obj['kz_delivery'] == 'Почта') ? ' <td class="non_border_bottom"></td>
				  <td class="imp_border center"><span>' . $inn[0] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[1] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[2] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[3] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[4] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[5] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[6] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[7] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[8] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[9] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[10] . '</span></td>
				  <td class="imp_border center"><span>' . $inn[11] . '</span></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>9</span></td>
				  <td class="imp_border center"></td>' : ' <td class="non_border_bottom"></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>4</span></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>4</span></td>
				  <td class="imp_border center"><span>0</span></td>
				  <td class="imp_border center"><span>0</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>3</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>2</span></td>
				  <td class="imp_border center"><span>4</span></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="imp_border center"></td>
				  <td class="non_border center"></td>
				  <td class="imp_border center"><span>1</span></td>
				  <td class="imp_border center"><span>7</span></td>
				  <td class="imp_border center"></td>') . '

				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;ИИК</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $iik : 'KZ23926150119P249000') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"><span class="small_text">&nbsp;Кассир:</span></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Банк</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $bank : 'АО «Казкоммерцбанк»') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;БИК</span> <b>' . (($obj['kz_delivery'] == 'Почта') ? $biks : 'KINCKZKA') . '</b></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="20" class="imp_border center"><span class="small_text">Наименование платежа</span></td>
				  <td class="imp_border center"><span class="small_text">Сумма</span></td>
				</tr>
				<tr>
				  <td class="non_border_bottom"></td>
				  <td colspan="20" class="imp_border"><span class="small_text">&nbsp;Номер посылки:</span></td>
				  <td class="imp_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="20" class="imp_border"></td>
				  <td class="imp_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="20" class="imp_border"></td>
				  <td class="imp_border center"></td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="20" class="imp_border"><span class="small_text">&nbsp;ВСЕГО (сумма цифрами): </span></td>
				  <td class="imp_border center">' . (in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2)) . '</td>
				</tr>
				<tr>
				  <td class="non_border_bottom">&nbsp;</td>
				  <td colspan="21" class="imp_border"><span class="small_text">&nbsp;ВСЕГО (прописью): ' . (($obj['country'] == 'kz') ? num2str((in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2))) : num2rub((in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2)))) . '</span></td>
				</tr>
			  </table>
			</td>
			<td>
			  <table class="right_table" cellpadding="0" cellspacing="0">
				<tr>
				  <td width="40%">&nbsp;</td>
				  <td width="60%">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">' . (($obj['kz_delivery'] == 'Почта') ? $otprav : 'ТОО «KAZECOTRANSIT»') . '</td>
				</tr>
				<tr>
				  <td colspan="2">' . (($obj['kz_delivery'] == 'Почта') ? $addr : 'Ул. Ауэзова д.13 А/Я №20') . '</td>
				</tr>
				<tr>
				  <td colspan="2"> г. Астана</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">Ценность: ' . (in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['price'], 2)) . ' ' . (($obj['country'] == 'kz') ? 'тенге' : 'руб.') . '</td>
				</tr>
				<tr>
				  <td colspan="2"></td>
				</tr>
				<tr>
				  <td colspan="2">Наложенный платеж: ' . (in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2)) . ' ' . (($obj['country'] == 'kz') ? 'тенге' : 'руб.') . '</td>
				</tr>
				<tr>
				  <td colspan="2"></td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">Имя: ' . $obj['fio'] . '</td>
				</tr>

				<tr>
				  <td colspan="2">Адрес: ' . $obj['addr'] . '</td>
				</tr>
				<tr>
				  <td colspan="2">Индекс: ' . $obj['index'] . '</td>
				</tr>
				<tr>
				  <td colspan="2">' . (($obj['kz_delivery'] == 'Почта') ? 'Телефон: ' . $obj['phone'] : 'BRD') . '</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">Телефон.тех. поддержки:' . (($obj['kz_delivery'] == 'Почта') ? '+7(705)924 03 73' : '+77770000363') . '</td>
				</tr>
				<tr>
				  <td colspan="2">Номер  Заказа: ' . $obj['id'] . '</td>
				</tr>
				<tr>
				  <td colspan="2">' . $offerName . ' - ' . $obj['package'] . ' ' . $dop_str . '</td>
				</tr>
				<tr>
				  <td colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="2">Запрещенных к пересылке вложений нет</td>
				</tr>
				<tr>
				  <td colspan="2">';
        if (strlen($obj['kz_code']))
            $in_html .= 'ЦОУ АСТАНА ПОЧТАМТ<br><img src="http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=' . $obj['kz_code'] . '&f1=0&f2=10&a1=&a2=&a3=" alt="Barcode Image" />
				  <br>' . $obj['kz_code'];
        $in_html .= '</td>
				</tr>
			  </table>
			</td>
		  </tr>
		</table>';

        if (strlen($obj['kz_code']))
            $in_html .= '<span style="font-size:7px;">ЦОУ АСТАНА ПОЧТАМТ</span><br><img style="font-size:20px;" src="http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=' . $obj['kz_code'] . '&f1=0&f2=10&a1=&a2=&a3=" alt="Barcode Image" /><br>' . $obj['kz_code'];
    } else {

        $pdf->AddPage('P');
        $in_html = '<table border="1" class="left_table" cellpadding="0" cellspacing="0" rules="groups">
       <tr>
		<td colspan="2" style="font-size:30px; text-align:center"> <b>Ценная бандероль</b>
		</td>
      </tr>
	  <tr>
		<td style="padding: 0 70px">
			<table width="210" border="1" cellpadding="4" cellspacing="0">
			<tr>
			 <td rowspan="2" style="font-size:40px; text-align:center"><b>Ц</b></td>
			 <td style="font-size:18px; text-align:center; color:red"><em>№ ' . $obj['kz_code'] . '</em></td>
			</tr>
			<tr>
			 <td style="font-size:25px; text-align:center">ЦМПОЛ</td>
			</tr>
		   </table>
		</td>
		<td>
			<table  border="0"  cellpadding="2" cellspacing="0">
			<tr>
				<td width="150" style="font-size:20px; text-align:left; padding: 10px 20px">На сумму</td>
				<td style="font-size:20px; text-align:center; color:red; padding: 10px 20px"><em>' . $obj['price'] . ' сом 0 тый</em></td>
			</tr>
			<tr >
				<td cellpadding="2" width="150" style="font-size:5px; text-align:left"></td>
				<td cellpadding="2" style="font-size:12px; text-align:center "><em>сумма цифрами и прописью</em></td>
			</tr>
			<tr>
				<td width="150" style="font-size:20px; text-align:left; padding: 10px 20px">Наложенный платеж</td>
				<td style="font-size:20px; text-align:center; color:red; padding: 10px 20px"><em>' . $obj['price'] . ' сом 0 тый</em></td>
			</tr>
		   </table>
		</td>
      </tr>
	  <tr>
		<td style="margin: 0 50px" colspan="2">
			<table  border="0"  cellpadding="2" cellspacing="0">
			<tr>
				<td width="170" style="font-size:20px; text-align:left; padding: 10px 20px">Куда</td>
				<td width="260" style="font-size:20px; text-align:left; padding: 10px 20px"><em>Кыргыстан<br><br><span style="color:red; font-size:20px;">' . $obj['addr'] . '</span></em></td>
			</tr>
			<tr>
				<td width="170" style="font-size:20px; text-align:left; padding: 10px 20px">Кому</td>
				<td style="font-size:20px; text-align:left; color:red; padding: 10px 20px"><em>' . $obj['fio'] . '</em></td>
				<td width="170" style="font-size:10px; text-align:left; padding: 10px 20px">(календарный штемпель места приема)</td>
			</tr>
		   </table>
		</td>
      </tr>
	  <tr>
		<td colspan="2">
			<table width="770"  border="1"  cellpadding="2" cellspacing="0"  rules="groups">
			<tr>
				<td width="340" style="font-size:20px; text-align:left; padding: 10px 70px">Откуда</td>
				<td style="font-size:20px; text-align:left; padding: 10px 0px"><em>Кыргыстан<br>001016  Чуйская область, ул. Омуралиева, д. 6</em></td>
			</tr>
			<tr>
				<td width="340" style="font-size:20px; text-align:left; padding: 10px 70px">Кому</td>
				<td style="font-size:20px; text-align:left; padding: 10px 0px"><em>Центр международного почтового обмена и логистики <br>(' . $chep_dog . ')</em></td>
			</tr>
		   </table>
		</td>
      </tr>
	  <tr>
		<td colspan="2">
			<table  border="0"  cellpadding="2" cellspacing="0">
			<tr>
				<td width="200" style="font-size:20px; text-align:left; padding: 10px 20px">Вес</td>
				<td width="250" style="font-size:20px; text-align:left; padding: 10px 20px"><div style="color:red; font-size:20px;">______гр.<br></div></td>
			</tr>
			<tr>
				<td width="250" style="font-size:20px; text-align:left; padding: 10px 20px">_____________<br><span style="font-size:12px;">Подпись оператора</span></td>
				<td style="font-size:20px; text-align:left; color:red; padding: 10px 20px"></td>
			</tr>
		   </table>
		</td>
      </tr>
    </table>';
        //if($i>0) $pdf->AddPage(); //$pdf->AddPage();
        $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');
        //if($i==0)
        $pdf->AddPage('L');

        $in_html = '<table border="0" class="left_table" cellpadding="0" cellspacing="0" >
       <tr>
			<td>
				<tr>
					<table  border="0"  cellpadding="2" cellspacing="0">
					<tr>
						<td width="270" style="font-size:12px; text-align:center; ">
								Министерство<br>
							Транспорта и коммуникаций<br>
							Кыргызской Республики<br>
							№ _________________<br>
							по реестру ф.11<br>
							№ _________________<br>
							по реестру ф.10
						</td>
						<td width="30" style="font-size:18px; text-align:left; padding: 5px 20px"><b>П<br>Р<br>И<br>Е<br>М</b></td>
						<td width="160" style="font-size:25px; text-align:center;"></td>
						<td width="40" style="font-size:12px; text-align:center; padding: 10px 20px">Ф.113</td>
					</tr>
				   </table>
			   </tr>
			   <tr>
					<table  border="1"  cellpadding="2" cellspacing="0">
						<tr>
							<td width="20" rowspan="7" style="font-size:8px; text-align:center; ">И<br>С<br>П<br>Р<br>А<br>В<br>Л<br>Е<br>Н<br>И<br>Я<br><br>Н<br>Е<br><br>Д<br>О<br>П<br>У<br>С<br>К<br>А<br>Ю<br>Т<br>С<br>Я</td>
							<td width="190" style="font-size:15px; text-align:center; padding: 10px 20px">Наименование филиала,<br>к – гербовая печать</td>
							<td width="120" style="font-size:15px; text-align:center;">(календ. шт. места подачи)</td>
							<td width="50" style="font-size:12px; text-align:center; padding: 10px 20px">№ по Ф. 5</td>
							<td width="100" style="font-size:12px; text-align:center; padding: 10px 20px">Сумма, вид услуги подпись оператора</td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:16px; text-align:center; padding: 10px 20px">
								ПОЧТОВЫЙ ПЕРЕВОД НАЛОЖЕННОГО ПЛАТЕЖА <br><span style="color:red;"><u>' . $obj['price'] . ' сом 00 тыйин</u></span></td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:16px; text-align:left; padding: 10px 20px">
									Куда  <span style="color:blue;"> <u>индекс 001016,  ' . $chp_addr . '</u></span></td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:16px; text-align:center; padding: 10px 20px">
								<span style="color:blue;">ИП «' . $chep . '»</span></td>
						</tr>
						<tr>
							<td colspan="4" style="font-size:16px; text-align:center; padding: 10px 20px">
							 <span style="color:blue;">ЗАО КИК-Банк,  ' . $bik . ',  ' . $rss . '</span></td>
						</tr>
						<tr>
							<td colspan="4" style="font-size:16px; text-align:left; padding: 10px 20px">
							<span style="color:red;">От кого &nbsp;&nbsp;&nbsp;
										' . $obj['fio'] . '</span></td>
						</tr>
						<tr>
							<td colspan="2" style="font-size:16px; text-align:left; padding: 10px 20px">
								<span style="color:red;">Адрес &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								' . $obj['addr'] . '</span>
							</td>
							<td colspan="2">
								<table width="150" border="0" cellpadding="4" cellspacing="0">
									<tr>
										<td style="font-size:15px; text-align:center;"><em>_________________</em></td>
									</tr>
									<tr>
										<td style="font-size:12px; text-align:center;">_____________________ Шифр и подпись</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
			    </tr>
				<tr>
					<table border="0" cellpadding="4" cellspacing="0">

						<td style="font-size:12px;  text-align:center;">
							________________________________________________________________________
							<br> Л И Н И Я &nbsp;&nbsp;&nbsp; О Т Р Е З А
						</td>
					</table>
				</tr>
				<tr>
					<table border="0" cellpadding="4" cellspacing="0">
						<tr>
							<td colspan="2" width="240" style="font-size:8px;  text-align:left;">
								При получении денег заполните извещение и предъявите паспорт или документ, удостоверяющий личность<br>
								<span style="font-size:3px; float:left;">___________________________________________________________________________________________________________________________________</span>
								<br><span style="font-size:12px;"><u>Предьявлен</u>_______________________</span>
								<br><span style="font-size:12px;"><u>Серия</u>__________<u>№</u>_______________</span>
								<br><span style="font-size:12px;"><u>Выданный «___»____________ _____г</u></span>
								<br><br><br><span style="font-size:12px;">_<u>Кем ____________________________</u></span>
								<br><span style="font-size:12px;"><u>Паспорт прописан*) ________________</u></span>
								<br><span style="font-size:12px;"><u>Получатель _______________________</u></span>
							</td>
							<td rowspan="2" width="40" style="font-size:12px;  text-align:center;">

								<br> Л<br>И<br>Н<br>И<br>Я<br><br>О<br>Т<br>Р<br>Е<br>З<br>А
							</td>
							<td rowspan="2" width="210" style="font-size:10px;  text-align:center;">Министерство транспорта и коммуникаций
								<br>Кыргызской Республики
								<br>№ ______________
								<br><span style="font-size:6px;">(по реестру ф.11)</span>
								<br><span style="font-size:12px;"><b>И З В Е Щ Е Н И Е</b></span>
								<br><span style="font-size:8px;">о почтовом переводе нал.платежа №_______</span>
								<br><span style="font-size:12px;">На <span style="color:red;">' . $obj['price'] . '</span> сом 0 тыйын</span>
								<br><span style="font-size:12px;text-align:left;"><u>Куда &nbsp;&nbsp;Инд.001016, ' . $chp_addr . '</u></span>

								<br><span style="font-size:12px;text-align:left;"><u>Кому ИП «' . $chep . '»</u></span>
								<br><br><br><span style="font-size:12px;text-align:center;"><u>ЗАО КИК-Банк</u></span>
								<br><span style="font-size:12px;text-align:center;"><u>' . $bik . '</u></span>
								<br><span style="font-size:12px;text-align:center;">____<u>' . $rss . '</u>____</span>
								<br><br><span style="font-size:12px;text-align:left;"><u>______________от _______до ______</u></span>
								<br><span style="font-size:6px;text-align:left;">Куда явиться за получением и время</span>
							</td>
						</tr>
						<tr>
							<td style="font-size:12px;  text-align:center;">

							</td>
							<td style="font-size:12px;  text-align:center;">
								<table width="110" border="1" cellpadding="4" cellspacing="0">
									<tr>
										<td style="font-size:7px; text-align:left;">*сведения о прописке паспорта заполняются только при получении переводов, адресованных «до востребования»</td>
									</tr>
									<tr>
										<td style="font-size:12px; text-align:left;">Оплатил</td>
									</tr>
									<tr>
										<td style="font-size:10px; text-align:center;">__________________</td>
									</tr>
									<tr>
										<td style="font-size:10px; text-align:center;">__________________</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</tr>
				<tr>
					<td colspan="4" style="font-size:10px; text-align:left;">ОБЩИЕ (' . $obj['id'] . ') ' . $offerName . ' Кол-во: ' . $obj['package'] . '' . $dop_str . '</td>
				</tr>
			</td>
			<td>
				<tr>
					<td>
						<table  border="0"  cellpadding="2" cellspacing="0">
						<tr>
							<td width="200" style="font-size:12px; text-align:center; ">
									<span style="font-size:10px;text-align:left;">Вторичное извещение</span>
									<br><span style="font-size:10px;text-align:left;"><u>выписано ________________</u></span>
									<br><span style="font-size:6px;text-align:left;">(дата)</span>
									<br><span style="font-size:10px;text-align:left;">Плата за доставку</span>
									<br><span style="font-size:10px;text-align:left;">__________сом___________тый.</span>
									<br><span style="font-size:10px;text-align:left;">Подлежит оплате</span>
									<br><span style="font-size:10px;text-align:left;">______________________</span>
									<br><span style="font-size:6px;text-align:left;">(подпись)</span>
								</td>
							<td width="30" style="font-size:15px; text-align:left; padding: 5px 20px"><b>О<br>П<br>Л<br>А<br>Т<br>А</b></td>
							<td width="160" style="font-size:25px; text-align:center;"></td>
							<td width="40" style="font-size:12px; text-align:center; padding: 10px 20px"></td>
						</tr>
					   </table>
					</td>
			    </tr>
				<tr>
					<td>
						<table  border="0"  cellpadding="2" cellspacing="0" style="padding: 0px 20px 0 150px">
							<tr>
							<td width="220">
							</td>
							<td>
							<table  border="1"  cellpadding="2" cellspacing="0" style="padding: 0px 20px 0 150px">
								<tr>
									<td width="100" style="font-size:12px; text-align:center; padding: 10px 20px">Наименование филиала</td>
									<td width="70" style="font-size:12px; text-align:center;">Дата</td>
									<td width="50" style="font-size:12px; text-align:center; padding: 10px 20px">Номер</td>
									<td width="60" style="font-size:12px; text-align:center; padding: 10px 20px">Сумма</td>
								</tr>
							</table>
							</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<span style="font-size:16px;text-align:center;"><b>Расписка получателя</b></span>
						<br><span style="font-size:14px;text-align:left;">Сумма ____________________________________________________</span>
						<br><span style="font-size:6px;text-align:center;">(сомы прописью, тыйыны цифрами)</span>
						<br><span style="font-size:14px;text-align:left;">Получил &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;«____» __________ 2016 г. &nbsp;&nbsp;&nbsp;_________________</span>
						<br><span style="font-size:6px;text-align:center;">(дата)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(подпись)</span>
						<br><span style="font-size:14px;text-align:left;">Оплатил ____________________</span>
						<br><span style="font-size:6px;text-align:left; padding: 0 0 0 50px">(подпись оператора)</span>
						<br><span style="font-size:14px;text-align:left;">______________________________________________________________</span>
						<br><span style="font-size:7px;text-align:center;">Отметки (о досылке, возвращении и причинах не оплаты)</span>
						<br><span style="font-size:14px;text-align:left;">______________________________________________________________</span>
						<br><span style="font-size:14px;text-align:left;">______________________________________________________________</span>
						<br><span style="font-size:14px;text-align:left;">______________________________________________________________</span>
						<br><span style="font-size:14px;text-align:left;">______________________________________________________________</span>
						<br><span style="font-size:14px;text-align:left;">______________________________________________________________</span>
					<br></td>
				</tr>
				<tr> <td>
					<table border="0" cellpadding="4" cellspacing="0">

						<td style="font-size:12px;  text-align:center;">
							________________________________________________________________________
							<br> Л И Н И Я &nbsp;&nbsp;&nbsp; О Т Р Е З А
						</td>
					</table>
					</td>
				</tr>
				<tr> <td>
					<table border="0" cellpadding="4" cellspacing="0">
						<tr>
							<td colspan="2" width="240" style="font-size:8px;  text-align:left;">
								<span style="font-size:10px; text-align:center;">Министерство транспорта и коммуникаций Кыргызской Республики</span>
								<br><span style="font-size:14px; text-align:center;">ТАЛОН</span>
								<br><br><span style="font-size:10px;">к почтовому переводу нал.платежа № ________</span>
								<br><span style="font-size:14px; text-align:center;">На <span style="color:red">' . $obj['price'] . '</span> сом 0 тыйын</span>
								<br><br><span style="font-size:10px;"><u>От кого _' . $obj['fio'] . '</u></span>
								<br><span style="font-size:12px;"><u>_________________________________</u></span>
								<br><span style="font-size:12px;"><u>Тел. ______' . $obj['phone'] . '____________</u></span>
								<br><span style="font-size:12px;"><u>_________________________________</u></span>
								<br><span style="font-size:12px;"><u>_________________________________</u></span>
								<br><span style="font-size:12px;"><u>Адрес____________________________</u></span>
							    <br><span style="font-size:6px; text-align:center;">(Почтовый индекс и подробный адрес)</span>
								<br><br><span style="font-size:12px;"><u>_____' . $obj['index'] . '__' . $obj['addr'] . '____</u></span>
								<br><span style="font-size:12px;"><u>_________________________________</u></span>
							</td>
							<td width="40" style="font-size:12px;  text-align:center;">

								<br> Л<br>И<br>Н<br>И<br>Я<br><br>О<br>Т<br>Р<br>Е<br>З<br>А
							</td>
							<td width="210" style="font-size:12px;  text-align:left;">
								<br><u>Для письменного сообщения_____</u>
								<br><br><span style="font-size:16px;">Ценное(ая):</span>
								<br><span style="font-size:14px;"><u>_________письмо________</u></span>
								<br><span style="font-size:14px;"><u>_____&#121;___бандероль_____</u></span>
								<br><span style="font-size:14px;"><u>_________посылка_______</u></span>
								<br><span style="font-size:6px; text-align:center;">(Нужное отметить)</span>
								<br><span style="font-size:14px;"><u>________________________</u></span>
								<br><span style="font-size:14px;"><u>________________________</u></span>
								<br><span style="font-size:14px;"><u>__№ ' . $obj['kz_code'] . '_______________</u></span>
								<br><span style="font-size:14px;"><u>__от «___» ________2016г.</u></span>
								<br><span style="font-size:14px;"><u>________________________</u></span>
								<br><span style="font-size:14px;"><u>________________________</u></span>
								<br><span style="font-size:14px;"><u>________________________</u></span>
							</td>
						</tr>
					</table>
				</td>
				</tr>
			</td>
      </tr>
    </table>';
    }
    //if($i>0) $pdf->AddPage();
    $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');
    //echo $in_html; //die;
    $i++;
}
$in_html .= '</body>
</html>';
//echo $in_html; die;
//$pdf->writeHTML($in_html, true, false, false, false, '');

$pdf->Output('doc.pdf', 'I');

function num2rub($num) {
    $nul = 'ноль';
    $ten = array(
        array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
    );
    $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
    $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
    $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
    $unit = array(// Units
        array('копейка', 'копейки', 'копеек', 1),
        array('рубль', 'рубля', 'рублей', 0),
        array('тысяча', 'тысячи', 'тысяч', 1),
        array('миллион', 'миллиона', 'миллионов', 0),
        array('миллиард', 'милиарда', 'миллиардов', 0),
    );
    //
    list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub) > 0) {
        foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
            if (!intval($v))
                continue;
            $uk = sizeof($unit) - $uk - 1; // unit key
            $gender = $unit[$uk][3];
            list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2 > 1)
                $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];# 20-99
            else
                $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];# 10-19 | 1-9
            // units without rub & kop
            if ($uk > 1)
                $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
        } //foreach
    } else
        $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
    $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
}
