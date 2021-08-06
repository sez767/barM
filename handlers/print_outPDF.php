<?php
require_once dirname(__FILE__) . '/../lib/db.php';
$table_name = 'asterisk.cdr';
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$prefix = $_GET['project'];
$rus_pref = array('8*' => 0.17, '15*' => 0.07, '7*' => 0.07);
$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>
<MMString:LoadString id="insertbar/linebreak" />
</title>
</head>
<body><h1 aligh="center">Статистика по звонкам в разрезе часов за ' . $start_date . ' - ' . $end_date . '</h1>';
mysql_close();
$ext_db_old = asterisk_base();
if ($prefix == '8*') {
    $add_remedio = " ( dst LIKE '" . $prefix . "%' OR ( dst LIKE '%TorgKZ_P%' AND lastapp = 'Queue') ) ";
    $queue = " queuename = 'TorgKZ_P';";
} elseif ($prefix == '9*') {
    $add_remedio = " ( dst LIKE '" . $prefix . "%' OR ( dst LIKE '%LogistKZ_P%' AND lastapp = 'Queue') ) ";
    $queue = " queuename = 'LogistKZ_P';";
} elseif ($prefix == '5*') {
    $add_remedio = " ( dst LIKE '" . $prefix . "%' OR ( dst LIKE '%PostKZ_P%' AND lastapp = 'Queue') ) ";
    $queue = " queuename = 'PostKZ_P';";
} else
    $add_remedio = " dst LIKE '" . $prefix . "%' ";
$que_select = "SELECT COUNT(*) as count,
			SUM(billsec) as sum_time,
		(SELECT SUM(duration-billsec)
				FROM " . $table_name . " WHERE " . $add_remedio . "
			AND calldate > '" . $start_date . " 00:00:00' AND calldate < '" . $end_date . " 23:59:59'
			AND disposition in ('ANSWERED','BUSY','NO ANSWER','FAILED')) as full_hold,
		(SELECT COUNT(*)
				FROM " . $table_name . " WHERE " . $add_remedio . "
			 AND calldate > '" . $start_date . " 00:00:00' AND calldate < '" . $end_date . " 23:59:59'
			 AND disposition in ('ANSWERED','BUSY','NO ANSWER','FAILED')) as count_all,
			 MAX(billsec) as max_time, MIN(billsec) as min_time,
			 SUM(duration-billsec) as sum_hold_time,
			 MAX(duration-billsec) as max_hold_time, MIN(duration-billsec) as min_hold_time
				FROM " . $table_name . " WHERE " . $add_remedio . "
			 AND calldate > '" . $start_date . " 00:00:00' AND calldate < '" . $end_date . " 23:59:59'
			 AND disposition in ('ANSWERED')";
//echo $que_select;
$Queue_stat = mysql_query($que_select);
$rez_que = mysql_fetch_array($Queue_stat);
$html .= '<h3>Состоявшиеся звонки </h3>
			<table border="1" width="400">
			 <tr>
			<td width="320">Средняя продолжительность звонка:</td>
			<td width="80">' . pr_time(round(($rez_que['sum_time']) / ($rez_que['count']))) . '</td>
			 </tr>
			 <tr>
			<td>Мин. продолжительность звонка:</td>
			<td>' . pr_time($rez_que['min_time']) . '</td>
			 </tr>
			 <tr>
			<td>Макс. продолжительность звонка:</td>
			<td>' . pr_time($rez_que['max_time']) . '</td>
			 </tr>
			 <tr>
			<td>Общая продолжительность звонков:</td>
			<td>' . pr_time($rez_que['sum_time']) . '' . ((in_array($prefix, array_keys($rus_pref))) ? ' - ' . round($rus_pref[$prefix] * ($rez_que['sum_time']) / 60) . '$' : '') . '</td>
			 </tr>
			 <tr>
			<td>Среднее время ожидания:</td>
			<td>' . pr_time(round(($rez_que['sum_hold_time']) / ($rez_que['count']))) . '</td>
			 </tr>
			 <tr>
			<td>Мин. время ожидания:</td>
			<td>' . pr_time($rez_que['min_hold_time']) . '</td>
			 </tr>
			 <tr>
			<td>Макс. время ожидания:</td>
			<td>' . pr_time($rez_que['max_hold_time']) . '</td>
			 </tr>
			 <tr>
			<td>Общее время ожидания:</td>
			<td>' . pr_time($rez_que['sum_hold_time']) . '</td>
			 </tr>
			 <tr>
			<td>Общее время ожидания(включая звонки которые не были в разговоре):</td>
			<td>' . pr_time($rez_que['full_hold']) . '</td>
			 </tr>
			 <tr>
			<td>Количество звонков(отвеченные абонентом):</td>
			<td>' . $rez_que['count'] . '</td>
			 </tr>
			 <tr>
			<td>Количество звонков(всего):</td>
			<td>' . $rez_que['count_all'] . '</td>
			 </tr>
		   </table><br>';

/* -- Статистика в разрезе агентов -- */
$query = "SELECT
			SUBSTRING(dstchannel, 1, 8) as src,
			COUNT(*) as ccall,
			SUM(if(disposition in ('ANSWERED'),1,0)) as otvet,
			SUM(if(disposition NOT in ('ANSWERED'),1,0)) as notvet,
			SUM(duration) as tcall,
			SUM(billsec) as rcall
		FROM " . $table_name . "
		WHERE " . $add_remedio . " AND calldate > '" . $start_date . " 00:00:00' AND calldate < '" . $end_date . " 23:59:59'

   GROUP BY SUBSTRING(dstchannel, 1, 8)";
//echo $query;
$Que_stat = mysql_query($query);
$html .= '<h3>Статистика в разрезе агентов</h3>
<br>
<table border="1" width="800">
   <tr>
   <th>Агент</th>
   <th>Всего звонков</th>
   <th>отвеченные звонки</th>
   <th>неотвеченные звонки</th>
   <th>Общее время звонков</th>
   <th>Время в разговоре</th>
   </tr>';
while ($rez_statT = mysql_fetch_array($Que_stat)) {
    $html .= '<tr>
   <td>' . $rez_statT['src'] . '</td>
   <td>' . $rez_statT['ccall'] . '</td>
   <td>' . $rez_statT['otvet'] . '</td>
   <td>' . $rez_statT['notvet'] . '</td>
   <td>' . pr_time($rez_statT['tcall']) . '</td>
   <td>' . pr_time($rez_statT['rcall']) . '</td>
   </tr>';
}
$html .= '</table>';
$html .= '</body></html>';
echo $html;
die;
