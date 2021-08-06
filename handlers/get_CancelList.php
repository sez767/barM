<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
include_once ("excel.inc.php");
$fields = " * ";
if (strlen($_GET['id_str'])) {
    $add = ' AND id IN (' . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ') ';
}
$fields = "id, description";
$query = "SELECT $fields FROM staff_order
				WHERE status = 'Отменён' $add
                ORDER BY id";
//echo $query; die;
$rs = mysql_query($query);
$dost_arr = array();
if (mysql_num_rows($rs)) {
    while ($obj = mysql_fetch_assoc($rs)) {
        $dost_arr[] = $obj;
    }
}
$excel = new ExcelWriter("cancel_" . $_REQUEST['from'] . '_' . $_REQUEST['to'] . '.xls');
//var_dump($dost_arr); die;
$i = 1;
foreach ($dost_arr as $obj) {
    array_unshift($obj, $i);
    $excel->writeLine($obj);
    $i++;
}

$excel->close();
?>
