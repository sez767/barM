<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$is_sip = array();
$rez = array();
$VUQuery = "SELECT Sip AS value, Sip AS id FROM Staff WHERE 1 ";
$VUResult = mysql_query($VUQuery) or $Result = false;
while ($VUResultRow = mysql_fetch_array($VUResult, MYSQL_ASSOC)) {
    $is_sip[] = $VUResultRow["id"];
}
$all_arr = range("2001", "8999");
$rez_ar = array_diff($all_arr, $is_sip);
foreach ($rez_ar as $v) {
    $rez[] = array($v, $v);
}
echo json_encode($rez);
