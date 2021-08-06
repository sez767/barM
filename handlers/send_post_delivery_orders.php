<?php

/**
 * send_post_delivery_orders.php.
 * Date: 2017-12-11
 * Time: 17:43
 * @description Сбор данных о заказах с доставкой на почту и отправка на E-Mail
 * Заказы с доставкой тип почта
 * Бланк
 * Упаковочный лист
 * Инфа по отправлениям
 * @author      Andrii Khvorostianyi <a.khvorostianyi@gmail.com>
 */
//error_reporting( 0 );
//ini_set( "display_errors", 0 );
session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
require_once dirname(__FILE__) . '/../lib/xlsxwriter.class.php';
require_once 'excelwriter.inc.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';
require_once dirname(__FILE__) . '/../lib/fpdi/fpdi.php';
require_once dirname(__FILE__) . '/../ini/php_mailer_inited.php';

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
$orders = DB::query("SELECT * FROM staff_order WHERE id IN (" . implode(',', $ids) . ") ORDER BY offer,package");


switch ((int) $_REQUEST['sender']) {
    case 3:
//    $text1 = 'Мукалиев';
//    $text2 = '??? г. Астана , ул. Ауэзова 13, а/я 2623';
//    $text4 = '???  Служба заботы о клиентах:+77010352866';
//    $text3 = '??? Договор № 01-02-2016/2073';
//    $kodp = '??? 5061';
//    $postcode = '??? 010000';
        break;
    case 7:
        $otprav = 'ИП BRDmarket';
        $iik = 'KZ978210439812131672';
        $bank = 'АО «Bank RBK»';
        $biks = 'KINCKZKA';
        $addr = 'Главпочтамт а/я 87 ул. Ауэзова д. 13';
        $inn = '910214351216';
        $kodp = '4806';

        $text1 = 'ИП BRDmarket';
        $text2 = 'г. Астана , ул. Ауэзова 13, а/я 87';
        $text4 = 'Служба заботы о клиентах:+77059240373';
        $text3 = 'Договор № 01-02-17/1050 ОТ 24.03.2017';
        $postcode = '010000';
        break;
    case 10:
        $otprav = 'ИП ABC Group';
        $iik = 'KZ978210439812131672';
        $bank = 'АО «Bank RBK»';
        $biks = 'KINCKZKA';
        $addr = 'Главпочтамт а/я 87 ул. Ауэзова д. 13';
        $inn = '910214351216';
        $kodp = '5029';

        $text1 = 'ИП ABC Group';
        $text2 = 'г. Астана , ул. Ауэзова 13, а/я 87';
        $text4 = 'Служба заботы о клиентах:+77470940062';
        $text3 = 'Договор № 01-02-17/1049 ОТ 24.03.2017';
        $postcode = '010000';
        break;
    default:

        $otprav = "{$GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_firstname']} ({$GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_name']})";
        $iik = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_iik'];
        $bank = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_bank_name'];
        $biks = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_biks'];
        $addr = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_address'];
        $inn = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_inn'];
        $kodp = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_kodp'];

        $text1 = "{$GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_firstname']} ({$GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_name']})";
        $text2 = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_address'];
        $text4 = 'Служба заботы о клиентах:+77059240365';
        $text3 = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_contract'];
        $postcode = $GLOBAL_KETKZ_OTPRAVITEL[$_REQUEST['sender']]['sender_postcode'];

        break;
}


if (in_array((int) $_REQUEST['sender'], [3])) {
    $redis = RedisManager::getInstance()->getRedis();
    $t_ar = $redis->hGetAll('black_list');
    $pdf = new \TCPDF('L', 'px', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(10, 5, - 60);
    $pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setFontSubsetting(true);
    $pdf->SetFont('dejavusans', '', 12, '', true);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
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
    foreach ($orders as $obj) {
        echo "\n+++++++++++++++++\n";

        $sub_staff = explode("@", $obj['staff_id']);
        $dop_str = '';
        $tovar = '';
        echo "\n+1:{$obj['offer']}\n";

        if (preg_match('/([a-z_1-9])\w+\s-\s((\d)+?)/', $obj['offer'])) {

            $offers = explode(',', $obj['offer']);
            echo "\n+2\n";
            print_r($offers);
            foreach ($offers as $ke => $ve) {
                $offe = explode(' - ', $ve);
                echo "\n+3:$ve\n";
                print_r($offe);

                $obj['package'] = trim($offe[1]);
                if (isset($_SESSION['offer_' . trim($offe[0])])) {
                    $offe[0] = $_SESSION['offer_' . trim($offe[0])];
                }
                $tovar .= trim($offe[0]) . ' - ' . $obj['package'] . 'шт.<br>';
                echo "\n+1:{$obj['offer']}\n";
            }
        } else {
            echo "\n+5:{$obj['offer']}\n";
            $tovar = preg_replace('/=\s+[\d.]+\s+руб./', '', $obj['offer']);
            echo "\n+6:$tovar\n";
        }
        if (isJson($obj['dop_tovar'])) {
            $tmp_dop = (array) json_decode($obj['dop_tovar']);
            foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
                $dop_str .= '<br>' . $va . ' - ' . $tmp_dop['dop_tovar_count'][$ke] . '(' . $tmp_dop['dop_tovar_price'][$ke] . ')';
            }
        }
        if ($obj['country'] == 'kz' or $obj['country'] == 'KZ') {
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
              <td class="non_border_bottom"></td>
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
              <td colspan="21" class="bottom_border" style="font-size:10px;"><b>' . $obj['index'] . ' ' . $obj['addr'] . ' ' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $obj['phone'] : '' ) . '</b></td>
            </tr>
            <tr>
              <td class="non_border_bottom"></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Получатель платежа</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $otprav : 'ТОО «KAZECOTRANSIT» ' ) . '</b></td>
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
            <tr>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? ' <td class="non_border_bottom"></td>
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
              <td class="imp_border center"></td>' ) . '

            </tr>
            <tr>
              <td class="non_border_bottom"></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;ИИК</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $iik : 'KZ23926150119P249000' ) . '</b></td>
            </tr>
            <tr>
              <td class="non_border_bottom"><span class="small_text">&nbsp;Кассир:</span></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Банк</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $bank : 'АО «Казкоммерцбанк»' ) . '</b></td>
            </tr>
            <tr>
              <td class="non_border_bottom"></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;БИК</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $biks : 'KZKOKZKX' ) . '</b></td>
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
              <td class="imp_border center">' . ( in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2) ) . '</td>
            </tr>
            <tr>
              <td class="non_border_bottom">&nbsp;</td>
              <td colspan="21" class="imp_border"><span class="small_text">&nbsp;ВСЕГО (прописью): ' . num2str(( in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2))) . '</span></td>
            </tr>
            <tr>
              <td class="bottom_border">&nbsp;</td>
              <td colspan="21" class="imp_border"><b class="small_text">&nbsp;Дата  «____» ________________ 20_____г.  Подпись Плательщика _______________  </b></td>
            </tr>
            <tr class="rows_firm">
              <td class="left_column non_border_bottom"></td>
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
              <td colspan="21" class="bottom_border" style="font-size:10px;"><b>' . $obj['index'] . ' ' . $obj['addr'] . ' ' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $obj['phone'] : '' ) . '</b></td>
            </tr>
            <tr>
              <td class="non_border_bottom"></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Получатель платежа</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $otprav : 'ТОО «KAZECOTRANSIT»' ) . '</b></td>
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
            <tr>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? ' <td class="non_border_bottom"></td>
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
              <td class="imp_border center"></td>' ) . '
            </tr>
            <tr>
              <td class="non_border_bottom"></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;ИИК</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $iik : 'KZ23926150119P249000' ) . '</b></td>
            </tr>
            <tr>
              <td class="non_border_bottom"><span class="small_text">&nbsp;Кассир:</span></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;Банк</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $bank : 'АО «Казкоммерцбанк»' ) . '</b></td>
            </tr>
            <tr>
              <td class="non_border_bottom"></td>
              <td colspan="21" class="non_border_bottom"><span class="small_text">&nbsp;БИК</span> <b>' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $biks : 'KZKOKZKX' ) . '</b></td>
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
              <td class="imp_border center">' . ( in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2) ) . '</td>
            </tr>
            <tr>
              <td class="non_border_bottom">&nbsp;</td>
              <td colspan="21" class="imp_border"><span class="small_text">&nbsp;ВСЕГО (прописью): ' . num2str(( in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2))) . '</span></td>
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
              <td colspan="2">' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $otprav : 'ТОО «KAZECOTRANSIT»' ) . '</td>
            </tr>
            <tr>
              <td colspan="2">' . ( ( $obj['kz_delivery'] == 'Почта' ) ? $addr : 'Ул. Ауэзова д.13 А/Я №20' ) . '</td>
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
              <td colspan="2">Ценность: ' . ( in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2) ) . ' тенге</td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td colspan="2">Наложенный платеж: ' . ( in_array($obj['id'], $t_ar) ? '0' : round((float) $obj['total_price'], 2) ) . ' тенге</td>
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
              <td colspan="2"><span  style="font-size:14px;"><strong>' . $sub_staff[0] . '</strong></span></td>
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
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2">Телефон.тех. поддержки:' . ( ( $obj['kz_delivery'] == 'Почта' ) ? '+77057342701' : '+77057342701' ) . '</td>
            </tr>
            <tr>
              <td colspan="2">Номер  Заказа: ' . $obj['ext_id'] . '</td>
            </tr>
            <tr>
              <td colspan="2">' . $tovar . ' ' . ( ( $obj['package'] > 0 ) ? $obj['package'] : '' ) . ' ' . $dop_str . '</td>
            </tr>
             <tr>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td colspan="2">';
            if (strlen($obj['kz_code'])) {
                $in_html .= 'ЦОУ АСТАНА ПОЧТАМТ<br><img src="http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=' . $obj['kz_code'] . '&f1=0&f2=10&a1=&a2=&a3=" alt="Barcode Image" />
			  <br>' . $obj['kz_code'];
            }
            $in_html .= '</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>';
            if (strlen($obj['kz_code'])) {
                $in_html .= '<span style="font-size:7px;">ЦОУ АСТАНА ПОЧТАМТ</span><br>
	<img style="font-size:20px;" src="http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=' . $obj['kz_code'] . '&f1=0&f2=10&a1=&a2=&a3=" alt="Barcode Image" />';
            }
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
				<td style="font-size:20px; text-align:center; color:red; padding: 10px 20px"><em>' . $obj['total_price'] . ' сом 0 тый</em></td>
			</tr>
			<tr >
				<td cellpadding="2" width="150" style="font-size:5px; text-align:left"></td>
				<td cellpadding="2" style="font-size:12px; text-align:center "><em>сумма цифрами и прописью</em></td>
			</tr>
			<tr>
				<td width="150" style="font-size:20px; text-align:left; padding: 10px 20px">Наложенный платеж</td>
				<td style="font-size:20px; text-align:center; color:red; padding: 10px 20px"><em>' . $obj['total_price'] . ' сом 0 тый</em></td>
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
				<td style="font-size:20px; text-align:left; padding: 10px 0px"><em>Кыргыстан<br>000888 г. Бишкек, ЦМПОЛ</em></td>
			</tr>
			<tr>
				<td width="340" style="font-size:20px; text-align:left; padding: 10px 70px">Кому</td>
				<td style="font-size:20px; text-align:left; padding: 10px 0px"><em>Центр межд-ного почтового обмена  и логистики <br>(' . $chep_dog . ')</em></td>
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
            $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');
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
								ПОЧТОВЫЙ ПЕРЕВОД НАЛОЖЕННОГО ПЛАТЕЖА <br><span style="color:red;"><u>' . $obj['total_price'] . ' сом 00 тыйин</u></span></td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:16px; text-align:left; padding: 10px 20px">
									Куда  <span style="color:blue;"> <u>индекс 000888, город Бишкек, ' . $chp_addr . '</u></span></td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:16px; text-align:center; padding: 10px 20px">
								<span style="color:blue;">ЧП «' . $chep . '»</span></td>
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
								<br><span style="font-size:12px;">На <span style="color:red;">' . $obj['total_price'] . '</span> сом 0 тыйын</span>
								<br><span style="font-size:12px;text-align:left;"><u>Куда &nbsp;&nbsp;Инд.000888,г. Бишкек,</u></span>
								<br><span style="font-size:12px;text-align:right;"><u>' . $chp_addr . '</u></span>
								<br><span style="font-size:12px;text-align:left;"><u>Кому ЧП «' . $chep . '»</u></span>
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
					<td colspan="4" style="font-size:10px; text-align:left;">ОБЩИЕ (' . $obj['ext_id'] . ') ' . $obj['offer'] . ' Кол-во: ' . $obj['package'] . '' . $dop_str . '</td>
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
						<br><span style="font-size:14px;text-align:left;">Получил &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;«____» __________ 2015 г. &nbsp;&nbsp;&nbsp;_________________</span>
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
								<br><span style="font-size:14px; text-align:center;">На <span style="color:red">' . $obj['total_price'] . '</span> сом 0 тыйын</span>
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
								<br><span style="font-size:14px;"><u>__от «___» ________2015г.</u></span>
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
        $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');
        $i ++;
    }
    $in_html .= '</body>
</html>';
    $file[] = dirname(__FILE__) . '/../tmp/post_report/blank_' . date('d.m.Y', strtotime('+1 day')) . '_' . (count($orders)) . '.pdf';
    $pdf->Output(dirname(__FILE__) . '/../tmp/post_report/blank_' . date('Y-m-d', strtotime('+1 day')) . '_' . (count($orders)) . '.pdf', 'F');
} else {
    $pdf = new FPDI('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 0, 5);
    $pdf->SetAutoPageBreak(true, 0);
    $pdf->setFontSubsetting(true);
    $pdf->SetFont('dejavusans', '', 12, '', true);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pageCount = $pdf->setSourceFile('EVRAZ.pdf');
    $style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

    foreach ($orders as $row) {
        if ($row['staff_id'] == '35574800') {
            $text4 = ' Служба заботы о клиентах:+77172472862';
        }
        $templateId = $pdf->importPage(1);
        $pdf->AddPage('L');
        $row['kz_code'] = str_replace(' ', '', $row['kz_code']);
        $row['district'] = str_replace(['область', 'Область'], 'обл', $row['district']);
        $row['district'] .= ( strpos($row['district'], ' обл') !== false ) ? '' : ' обл';

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

        // main goods
        $otherData = json_decode($row['other_data'], true);

        $offerArr = array(
            "{$GLOBAL_OFFER_DESC[$row['offer']]}" . (empty($otherData) ? '' : ' ' . implode(' ', $otherData)) . " - {$row['package']}шт."
        );

        // additional goods
        $dopTovar = json_decode($row['dop_tovar'], true);
        if (!empty($dopTovar['dop_tovar']) && is_array($dopTovar['dop_tovar'])) {
            foreach ($dopTovar['dop_tovar'] AS $ke => $va) {
                $tmpProp = array();
                foreach (array_keys($dopTovar) AS $propKey) {
                    if (!in_array($propKey, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) && !empty($dopTovar[$propKey][$ke])) {
                        $tmpProp[] = $dopTovar[$propKey][$ke];
                    }
                }
                $offerArr[] = "$va " . implode(' ', $tmpProp) . " - {$dopTovar['dop_tovar_count'][$ke]}шт.";
//                $offerArr[] = "{$GLOBAL_OFFER_DESC[$va]} " . implode(' ', $tmpProp) . " - {$dopTovar['dop_tovar_count'][$ke]}шт.";
            }
        }

        $workOfferArr = explode('|', wordwrap(implode('; ', recursiveClearArr($offerArr)), 55, '|'));
        foreach ($workOfferArr as $ke => $offerItem) {
            $pdf->Text(140 + $x, 96 + $y + $ke * 3, $offerItem);
        }

        $pdf->Text(136 + $x, 142 + $y, $row['ext_id']);
        $pdf->Text(165 + $x, 32 + $y, $row['total_price'] . ' (' . num2str($row['total_price']) . ')');
        $pdf->Text(165 + $x, 41 + $y, $row['total_price'] . ' (' . num2str($row['total_price']) . ')');
        $pdf->SetFontSize(16);
        $pdf->Text(92 + $x, 24 + $y, 'КОД ПЛАТЕЖА                  ' . $kodp);
        $pdf->SetFontSize(12);

        $pdf->Text(158 + $x, 115 + $y, $row['fio']);

        $address = explode(" ", $row['addr']);
        $aid = 0;
        $adar = array();
        $pdf->SetFontSize(10);

        foreach ($address as $ad) {
            if ($aid < 3) {
                $adar[0] = (isset($adar[0]) ? $adar[0] . ' ' : '') . $ad;
            } else {
                $adar[1] = (isset($adar[1]) ? $adar[1] . ' ' : '') . $ad;
            }
            $aid ++;
        }
        if (isset($adar[0])) {
            $pdf->Text(155 + $x, 130 + $y, $adar[0]);
        }
        if (isset($adar[1])) {
            $pdf->Text(155 + $x, 135 + $y, $adar[1]);
        }

        $pdf->SetFontSize(12);
        $pdf->Text(160 + $x, 149 + $y, '+' . $row['phone']);

        if (strlen($row['kz_code']) > 6) {
            $cs = curl_init();
            $url = 'http://baribarda.com/lib/barcode/html/image.php?code=code128&o=1&dpi=72&t=20&r=10&rot=0&text=' . $row['kz_code'] . '&f1=0&f2=10&a1=&a2=&a3=';
            curl_setopt($cs, CURLOPT_URL, $url);
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
            $pdf->Image(__DIR__ . '/../tmp/barcodes/' . $row['id'] . '_barcode.png', 160 + $x, 83 + $y, 80);
            $pdf->Text(160 + $x, 52 + $y, $row['kz_code']);
        }
        $pdf->SetFontSize(11);
        $pdf->setFontStretching(105);
        $pdf->Text(205, 175, str_replace(' ', '', $row['index']));
        $pdf->setFontStretching(100);
        $pdf->SetFontSize(12);
    }
    $file[] = dirname(__FILE__) . '/../tmp/post_report/blank_' . date('d.m.Y', strtotime('+1 day')) . '_' . (count($orders)) . '.pdf';
    print_r($file);
    $pdf->Output(dirname(__FILE__) . '/../tmp/post_report/blank_' . date('d.m.Y', strtotime('+1 day')) . '_' . (count($orders)) . '.pdf', 'F');
}

$file[] = dirname(__FILE__) . '/../tmp/post_report/' . date('d.m.Y', strtotime('+1 day')) . '_UPL_' . (count($orders)) . '.xls';

$excel = new ExcelWriter(dirname(__FILE__) . '/../tmp/post_report/' . date('d.m.Y', strtotime('+1 day')) . '_UPL_' . (count($orders)) . '.xls');
$excel->writeLine(array(''));
$excel->writeLine(array('', '', '', '<b>Упаковочный лист</b>', '', '', ''));
//$excel->writeLine(array('<b>Почта</b>'));


$all_ar = array();
foreach ($orders as $obj) {
//    print_r($obj);
    // main goods
    $otherData = json_decode($obj['other_data'], true);

    // товар в массив
    $offer = preg_replace('/\s+/', ' ', trim("{$GLOBAL_OFFER_DESC[$obj['offer']]}" . (empty($otherData) ? '' : ' ' . implode(' ', $otherData))));
    $all_ar[$obj['kz_delivery']][$offer][] = array(
        'offer' => $offer,
        'total_price' => $obj['total_price'],
        'package' => $obj['package'],
    );

    // additional goods
    $dopTovar = json_decode($obj['dop_tovar'], true);
    if (!empty($dopTovar['dop_tovar']) && is_array($dopTovar['dop_tovar'])) {
        foreach ($dopTovar['dop_tovar'] AS $ke => $va) {
            $tmpProp = array();
            foreach (array_keys($dopTovar) AS $propKey) {
                if (!in_array($propKey, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) && !empty($dopTovar[$propKey][$ke])) {
                    $tmpProp[] = $dopTovar[$propKey][$ke];
                }
            }
            $offer = preg_replace('/\s+/', ' ', trim("{$GLOBAL_OFFER_DESC[$va]} " . implode(' ', $tmpProp)));
//            print_r($dopTovar);
            $all_ar[$obj['kz_delivery']][$offer][] = array(
                'offer' => $offer,
                'total_price' => $dopTovar['dop_tovar_price'][$ke],
                'package' => $dopTovar['dop_tovar_count'][$ke],
            );
        }
    }
}


foreach ($all_ar as $oks => $all_arr) {
    ksort($all_arr);
    $all_c = 0;
    $excel->writeLine(array('<b>' . $oks . '</b>'));
    foreach ($all_arr as $ok => $ov) {
//        print_r($ov);

        $sh = 0;
        foreach ($ov as $kv => $obj) {
            $sh += $obj['package'];
        }
        $excel->writeLine(array($ok, '-', $sh));
        $all_c += $sh;
    }
    $excel->writeLine(array('<b>Итого:</b>', '', '<b>' . $all_c . '</b>'));
    $excel->writeLine(array(''));
}
$excel->close();

$add = ' AND id IN (' . (implode(',', $ids)) . ') ';

$fields = " * ";

switch ((int) $_REQUEST['sender']) {
    case 3:
        $otprav = 'ИП «BRDmarket»';
        break;
    case 4:
        $otprav = 'ИП «Нурлыханов» ';
        break;
    case 5:
        $otprav = 'ИП «Садыков Д. Ш.»';
        break;
    case 6:
        $otprav = 'ИП «Садыкова  Д. О.»';
        break;
    case 7:
        $otprav = 'ИП «BRDmarket»';
        break;
    case 8:
        $otprav = 'ИП UPSALE';
        break;
    case 9:
        $otprav = 'ИП ABC Group';
        break;
    case 10:
        $otprav = 'ИП ABC Group';
        break;
    default:
        $otprav = $otprav;
        break;
}

$fields = " fio,  `index`, addr, offer, kz_code, '' as w, total_price, total_price as k,package, CONCAT('+',phone) as phone";

$query = "SELECT $fields FROM staff_order WHERE kz_delivery='Почта' " . $add . " ORDER BY offer,package";
$rs = mysql_query($query);
$dost_arr = array();
if (mysql_num_rows($rs)) {
    while ($obj = mysql_fetch_assoc($rs)) {
        $dost_arr[$obj['offer']][$obj['package']][$obj['total_price']][] = $obj;
    }
}
$all_doc = array();
$count_doc = 0;

foreach ($dost_arr as $offer => $off_ar) {
    foreach ($off_ar as $package => $sum_pr) {
        foreach ($sum_pr as $total_price => $sum_a) {
            $all_doc[] = $sum_a;
            $count_doc += count($sum_a);
            if (count($sum_a) < 6) {
                continue;
            }
        }
    }
}
$file[] = dirname(__FILE__) . '/../tmp/post_report/' . $otprav . '_' . (date('d.m.Y', strtotime('+1 day'))) . '_' . (count($orders)) . '_ALL.xls';
$excel = new ExcelWriter(dirname(__FILE__) . '/../tmp/post_report/' . $otprav . '_' . (date('d.m.Y', strtotime('+1 day'))) . '_' . (count($orders)) . '_ALL.xls');
$excel->writeLine(array('Направление', '1'));
$excel->writeLine(array('Вид РПО', '3'));
$excel->writeLine(array('Категория РПО', '4'));
$excel->writeLine(array('Отправитель', ' ' . $otprav));
$excel->writeLine(array('Регион назначения', '1'));
$excel->writeLine(array('Индекс ОПС места приема', '010000'));
$excel->writeLine(array('Всего РПО', (string) $count_doc));
$excel->writeLine(array('№ п.п', 'ФИО', 'Индекс', 'Адрес', 'ШПИ', 'Вес', 'Сумма объявленной ценности', 'Сумма нал. Платежа', 'Телефон'));
$i = 1;

foreach ($all_doc as $sum_a) {
    foreach ($sum_a as $obj) {
        unset($obj['offer']);
        array_unshift($obj, $i);
        $excel->writeLine($obj);
        $i++;
    }
}

$excel->close();

try {
    $newSubject = 'Почта за ' . date('d.m.Y', strtotime('+1 day'));
    $newBody = 'Отчет';
    foreach ($file as $item) {
        $newAttachmentArr[] = $item;
    }

    $newAddressArr[] = '87477807355@mail.ru';
    $newAddressArr[] = 'orig_ba_xx@mail.ru';
    $newAddressArr[] = 'ainur92kan@mail.ru';
//    $newAddressArr[] = 'phpsergey@gmail.com';
    $newAddressArr[] = 'zhuldiz.ip@mail.ru';
    ///////////////////////////////////////////
//    $newAddressArr[] = array();
//    $newAddressArr[] = 'sdobrovol@gmail.com';

    if (DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName)) {
        $result['data'] = "Письмо отправлено на E-Mail 1";
    } else {
        $result['data'] = "Ошибка отправки письма на E-Mail 0";
    }
} catch (PHPMailer\PHPMailer\Exception $e) {
    $result['data'] = $e->errorMessage();
} catch (PHPMailer\PHPMailer\Exception $e) {
    $result['data'] = $e->getMessage();
}
echo $result['data'];
