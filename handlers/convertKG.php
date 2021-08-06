<?php
ob_end_clean();
$t_ar = array('4737', '4349', '3735', '2250', '12195', '8700', '4349', '20032', '19452', '7999', '12195', '16008', '11312', '12195', '1416', '41081', '7733', '20032');
session_start();

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

$query = "SELECT * FROM staff_order
				WHERE country = 'kzg' AND id IN (" . substr($_GET['id'], 0, strlen($_GET['id']) - 1) . ")
				ORDER BY offer,package";
//echo $query; die;
$rs = mysql_query($query);
$pdf = new \TCPDF('L', 'px', 'A4', true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(10, 5, -70);
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
$pdf->AddPage();
$i = 0;
while ($obj = mysql_fetch_assoc($rs)) {
    $dop_str = '';
    if (isJson($obj['dop_tovar'])) {
        $tmp_dop = (array) json_decode($obj['dop_tovar']);
        foreach ($tmp_dop['dop_tovar'] as $ke => $va) {
            $dop_str .= '<br>' . $GLOBAL_OFFER_DESC[$va] . ' - ' . $tmp_dop['dop_tovar_count'][$ke] . '(' . $tmp_dop['dop_tovar_price'][$ke] . ')';
        }
    }
    $in_html = '<table border="1" class="left_table" cellpadding="0" cellspacing="0" rules="groups">
       <tr>
		<td colspan="2" style="font-size:40px; text-align:center"> <b>Ценная бандероль</b>
		</td>
      </tr>
	  <tr>
		<td style="padding: 0 70px">
			<table width="350" border="1" cellpadding="4" cellspacing="0">
			<tr>
			 <td rowspan="2" style="font-size:50px; text-align:center"><b>Ц</b></td>
			 <td style="font-size:20px; text-align:center; color:red"><em>№ ирис</em></td>
			</tr>
			<tr>
			 <td style="font-size:30px; text-align:center">ЦМПОЛ</td>
			</tr>
		   </table>
		</td>
		<td>
			<table  border="0"  cellpadding="2" cellspacing="0">
			<tr>
				<td width="200" style="font-size:25px; text-align:left; padding: 10px 20px">На сумму</td>
				<td style="font-size:25px; text-align:center; color:red; padding: 10px 20px"><em>' . $obj['price'] . ' сом 0 тый</em></td>
			</tr>
			<tr >
				<td cellpadding="2" width="200" style="font-size:5px; text-align:left"></td>
				<td cellpadding="2" style="font-size:16px; text-align:center "><em>сумма цифрами и прописью</em></td>
			</tr>
			<tr>
				<td width="200" style="font-size:25px; text-align:left; padding: 10px 20px">Наложенный платеж</td>
				<td style="font-size:25px; text-align:center; color:red; padding: 10px 20px"><em>' . $obj['price'] . ' сом 0 тый</em></td>
			</tr>
		   </table>
		</td>
      </tr>
	  <tr>
		<td style="margin: 0 50px" colspan="2">
			<table  border="0"  cellpadding="2" cellspacing="0">
			<tr>
				<td width="300" style="font-size:25px; text-align:left; padding: 10px 20px">Куда</td>
				<td width="400" style="font-size:25px; text-align:left; padding: 10px 20px"><em>Кыргыстан<br><br><span style="color:red; font-size:20px;">' . $obj['addr'] . '</span></em></td>
			</tr>
			<tr>
				<td width="300" style="font-size:25px; text-align:left; padding: 10px 20px">Кому</td>
				<td style="font-size:20px; text-align:left; color:red; padding: 10px 20px"><em>' . $obj['fio'] . '</em></td>
				<td width="300" style="font-size:10px; text-align:left; padding: 10px 20px">(календарный штемпель места приема)</td>
			</tr>
		   </table>
		</td>
      </tr>
	  <tr>
		<td colspan="2">
			<table width="1310"  border="1"  cellpadding="2" cellspacing="0"  rules="groups">
			<tr>
				<td width="270" style="font-size:25px; text-align:left; padding: 10px 70px">Откуда</td>
				<td style="font-size:20px; text-align:left; padding: 10px 0px"><em>Кыргыстан<br>720000 г. Бишкек, ЦМПОЛ</em></td>
			</tr>
			<tr>
				<td width="270" style="font-size:25px; text-align:left; padding: 10px 70px">Кому</td>
				<td style="font-size:20px; text-align:left; padding: 10px 0px"><em>Центр межд-ного почтового обмена  и логистики <br>(отправления ЧП Кадыркулова  согл.дог. №100-14 от 13.03.2015 г)</em></td>
			</tr>
		   </table>
		</td>
      </tr>
	  <tr>
		<td colspan="2">
			<table  border="0"  cellpadding="2" cellspacing="0">
			<tr>
				<td width="300" style="font-size:25px; text-align:left; padding: 10px 20px">Вес</td>
				<td width="400" style="font-size:20px; text-align:left; padding: 10px 20px"><div style="color:red; font-size:20px;">______гр.<br></div></td>
			</tr>
			<tr>
				<td width="400" style="font-size:25px; text-align:left; padding: 10px 20px">_____________<br><span style="font-size:12px;">Подпись оператора</span></td>
				<td style="font-size:20px; text-align:left; color:red; padding: 10px 20px"></td>
			</tr>
		   </table>
		</td>
      </tr>
    </table>';
    $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');
    $pdf->AddPage();

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
							<td width="20" rowspan="7" style="font-size:9px; text-align:center; ">И<br>С<br>П<br>Р<br>А<br>В<br>Л<br>Е<br>Н<br>И<br>Я<br><br>Н<br>Е<br><br>Д<br>О<br>П<br>У<br>С<br>К<br>А<br>Ю<br>Т<br>С<br>Я</td>
							<td width="190" style="font-size:15px; text-align:center; padding: 10px 20px">Наименование филиала,<br>к – гербовая печать</td>
							<td width="120" style="font-size:15px; text-align:center;">(календ. шт. места подачи)</td>
							<td width="50" style="font-size:12px; text-align:center; padding: 10px 20px">№ по Ф. 5</td>
							<td width="100" style="font-size:12px; text-align:center; padding: 10px 20px">Сумма, вид услуги подпись оператора</td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:18px; text-align:center; padding: 10px 20px">
								ПОЧТОВЫЙ ПЕРЕВОД НАЛОЖЕННОГО ПЛАТЕЖА  <span style="color:red;"><u>' . num2str($obj['price']) . '</u></span></td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:18px; text-align:left; padding: 10px 20px">
									Куда  <span style="color:blue;"> <u>индекс 720093, город Бишкек, ул. Манаса 101/1</u></span></td>
						</tr>
						<tr>
							<td  colspan="4" style="font-size:18px; text-align:center; padding: 10px 20px">
								<span style="color:blue;">ЧП «Кадыркулова»</span></td>
						</tr>
						<tr>
							<td colspan="4" style="font-size:18px; text-align:center; padding: 10px 20px">
							 <span style="color:blue;">ЗАО КИК-Банк,  БИК128002,  р/с 1280026017833647</span></td>
						</tr>
						<tr>
							<td colspan="4" style="font-size:18px; text-align:left; padding: 10px 20px">
							<span style="color:red;">От кого &nbsp;&nbsp;&nbsp;
										' . $obj['fio'] . '</span></td>
						</tr>
						<tr>
							<td colspan="2" style="font-size:18px; text-align:left; padding: 10px 20px">
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
								<br><span style="font-size:12px;text-align:left;"><u>Куда &nbsp;&nbsp;Инд.720093,г. Бишкек,</u></span>
								<br><span style="font-size:12px;text-align:right;"><u>ул. Манаса 101/1</u></span>
								<br><span style="font-size:12px;text-align:left;"><u>Кому ЧП «Кадыркулова»</u></span>
								<br><br><br><span style="font-size:12px;text-align:center;"><u>ЗАО КИК-Банк</u></span>
								<br><span style="font-size:12px;text-align:center;"><u>БИК128002</u></span>
								<br><span style="font-size:12px;text-align:center;">____<u>р/с1280026017833647</u>____</span>
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
								<br><span style="font-size:14px;"><u>__№ ирис________________</u></span>
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
    if ($i > 0)
        $pdf->AddPage();
    $pdf->writeHTML($headhtml . $in_html, true, false, true, false, '');
    $i++;
}
$in_html .= '</body>
</html>';
//echo $headhtml.$in_html; die;
//$pdf->writeHTML($in_html, true, false, false, false, '');

$pdf->Output('doc.pdf', 'I');
