<?php

session_start();
$ukr_ar = array('37239410', '30224289', '30356360', '76488859', '79319411', '47671128', '72480483');
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$qs = "  SELECT object_id, max(`date`) AS `date`
            FROM ActionHistoryNew
            WHERE object_name = 'StaffObj' AND `type` = 'login' AND date > CURDATE()
            GROUP BY object_id
            ORDER BY id DESC";
$data = DB::query($qs);

$html_str = '<table>';
foreach ($data as $value) {
    $html_str .= "<tr><td><b>{$GLOBAL_STAFF_FIO[$value['object_id']]}</b></td><td>{$value['date']}</td></tr>";
}
$html_str .= '</table>';

echo '<div style="text-align:left;">' . $html_str . '</div>';
